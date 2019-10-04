<?php

namespace Gatekeeper\Keys;

use ActiveRecord;
use Emergence\People\User;
use Gatekeeper\Endpoints\Endpoint;

class KeysRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Key::class;

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    public static $browseConditions = [
        'Status' => 'active'
    ];

    public static function checkReadAccess(ActiveRecord $Record = null, $suppressLogin = false)
    {
        if (parent::checkReadAccess($Record, $suppressLogin)) {
            return true;
        }

        if (!$GLOBALS['Session']->Person) {
            return false;
        }

        return (boolean)KeyUser::getByWhere([
            'PersonID' => $GLOBALS['Session']->PersonID,
            'KeyID' => $Record->ID
        ]);
    }

    static public function handleRecordsRequest($action = false)
	{
		switch ($action ?: $action = static::shiftPath()) {
            case 'request':
                return static::handleRequestRequest();
            default:
                return parent::handleRecordsRequest($action);
        }
    }

    public static function handleRecordRequest(ActiveRecord $Key, $action = false)
    {
        switch ($action ?: $action = static::shiftPath()) {
            case 'endpoints':
                return static::handleEndpointsRequest($Key);
            case 'share':
                return static::handleShareRequest($Key);
            case 'revoke':
                return static::handleRevokeRequest($Key);
            default:
                return parent::handleRecordRequest($Key, $action);
        }
    }

    public static function handleRequestRequest()
    {
        $GLOBALS['Session']->requireAuthentication();

        // get key
        if (empty($_REQUEST['endpoint'])) {
            return static::throwInvalidRequestError('endpoint required');
        }

        if (!$Endpoint = Endpoint::getByHandle($_REQUEST['endpoint'])) {
            return static::throwNotFoundError('Endpoint not found');
        }

        if (!$Endpoint->KeySelfRegistration) {
            return static::throwUnauthorizedError('key registration not available');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['OwnerName'])) {
                return static::throwInvalidRequestError('OwnerName required');
            }

            // create Key
            $Key = Key::create([
                'OwnerName' => $_POST['OwnerName'],
                'ContactName' => $GLOBALS['Session']->Person->FullName,
                'ContactEmail' => $GLOBALS['Session']->Person->Email
            ], true);

            $KeyEndpoint = KeyEndpoint::create([
                'Key' => $Key,
                'Endpoint' => $Endpoint
            ], true);

            $KeyUser = KeyUser::create([
                'Key' => $Key,
                'Person' => $GLOBALS['Session']->Person,
                'Role' => 'owner'
            ], true);

            return static::respond('keyIssued', [
                'data' => $Key
            ]);
        }

        return static::respond('request', [
            'Endpoint' => $Endpoint
        ]);
    }

    public static function handleEndpointsRequest(Key $Key)
    {
        if ($endpointId = static::shiftPath()) {
            $Endpoint = Endpoint::getByID($endpointId);
            if (!$Endpoint || !in_array($Endpoint, $Key->Endpoints)) {
                return static::throwNotFoundError('Requested endpoint not added to this key');
            }

            return static::handleEndpointRequest($Key, $Endpoint);
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return static::respond('keyEndpoints', [
                    'data' => KeyEndpoint::getAllByWhere(['KeyID' => $Key->ID])
                ]);
            case 'POST':
                $GLOBALS['Session']->requireAccountLevel('Staff');

                if (empty($_POST['EndpointID']) || !($Endpoint = Endpoint::getByID($_POST['EndpointID']))) {
                    return static::throwInvalidRequestError('Valid EndpointID must be provided');
                }

                if (KeyEndpoint::getByWhere(['KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID])) {
                    return static::throwInvalidRequestError('Provided endpoint already added to this key');
                }

                $KeyEndpoint = KeyEndpoint::create(['KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID], true);

                return static::respond('keyEndpointAdded', [
                    'success' => true,
                    'data' => $KeyEndpoint
                ]);

                break;
            default:
                return static::throwInvalidRequestError('Method not supported');
        }
    }

    public static function handleEndpointRequest(Key $Key, Endpoint $Endpoint)
    {
        if (static::peekPath() == 'remove') {
            return static::handleEndpointRemoveRequest($Key, $Endpoint);
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return static::respond('keyEndpoint', [
                    'data' => KeyEndpoint::getByWhere(['KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID])
                ]);
            default:
                return static::throwInvalidRequestError('Method not supported');
        }
    }

    public static function handleEndpointRemoveRequest(Key $Key, Endpoint $Endpoint)
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        $KeyEndpoint = KeyEndpoint::getByWhere(['KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID]);

        if (!$KeyEndpoint) {
            return static::throwNotFoundError('Requested endpoint not added to this key');
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::respond('confirm', [
                'question' => 'Are you sure you want to remove endpoint <strong>'.htmlspecialchars($Endpoint->Title).'</strong> from key '.$Key->Key.'?',
                'data' => KeyEndpoint::getByWhere(['KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID])
            ]);
        }

        $KeyEndpoint->destroy();

        return static::respond('keyEndpointRemoved', [
            'success' => true,
            'data' => $KeyEndpoint
        ]);
    }

    public static function handleRevokeRequest(Key $Key)
    {
        $GLOBALS['Session']->requireAuthentication();

        if (
            !$GLOBALS['Session']->hasAccountLevel('Staff') &&
            !KeyUser::getByWhere([
                'PersonID' => $GLOBALS['Session']->PersonID,
                'KeyID' => $Key->ID,
                'Role' => 'owner'
            ])
        ) {
            return static::throwUnauthorizedError('Only staff or the key owner may revoke this key');
        }

        $Key->Status = 'revoked';
        $Key->save();

        return static::respond('revoked', [
            'success' => true,
            'data' => $Key
        ]);
    }

    public static function handleShareRequest(Key $Key)
    {
        $GLOBALS['Session']->requireAuthentication();

        if (
            !$GLOBALS['Session']->hasAccountLevel('Staff') &&
            !KeyUser::getByWhere([
                'PersonID' => $GLOBALS['Session']->PersonID,
                'KeyID' => $Key->ID,
                'Role' => 'owner'
            ])
        ) {
            return static::throwUnauthorizedError('Only staff or the key owner may share this key');
        }

        $responseData = [
            'data' => $Key
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['Email'])) {
                $responseData['success'] = false;
                $responseData['message'] = 'Email address for registered user required';
            } elseif (!$User = User::getByUsername($_POST['Email'])) {
                $responseData['success'] = false;
                $responseData['message'] = 'No registered user found for provided email address';
            } else {
                try {
                    KeyUser::create([
                        'Key' => $Key,
                        'Person' => $User
                    ], true);

                    return static::respond('shared', [
                        'success' => true,
                        'data' => $Key
                    ]);
                } catch (\DuplicateKeyException $e) {
                    $responseData['success'] = false;
                    $responseData['message'] = 'Requested user already has access to this key';
                }
            }
        }

        return static::respond('share', $responseData);
    }
}