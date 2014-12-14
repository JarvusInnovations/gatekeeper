<?php

namespace Gatekeeper;

use ActiveRecord;

class EndpointsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Endpoint::class;
    public static $browseOrder = ['ID' => 'DESC'];

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    public static function getRecordByHandle($endpointHandle)
    {
        // get version tag from next URL component
        if (!($endpointVersion = static::shiftPath()) || !preg_match('/^v.+$/', $endpointVersion)) {
            return static::throwInvalidRequestError('Endpoint version required');
        }

        $endpointVersion = substr($endpointVersion, 1);

        return Endpoint::getByHandleAndVersion($endpointHandle, $endpointVersion);
    }

    protected static function applyRecordDelta(ActiveRecord $Endpoint, $data)
    {
        if (is_numeric($data['AlertNearMaxRequests'])) {
            $data['AlertNearMaxRequests'] = $data['AlertNearMaxRequests'] / 100;
        }

        return parent::applyRecordDelta($Endpoint, $data);
    }

    public static function handleRecordRequest(ActiveRecord $Endpoint, $action = false)
    {
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'rewrites':
                return static::handleRewritesRequest($Endpoint);
            default:
                return parent::handleRecordRequest($Endpoint, $action);
        }
    }

    public static function handleRewritesRequest(Endpoint $Endpoint)
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return static::respond('endpointRewrites', [
                    'data' => $Endpoint->Rewrites
                ]);
            case 'POST':
                if (!is_array($_POST['rewrites'])) {
                    return static::throwInvalidRequestError('POST method expects "rewrites" array');
                }

                $saved = [];
                $deleted = [];
                $invalid = [];

                foreach ($_POST['rewrites'] AS $key => $data) {
                    $nonEmptyData = array_filter($data);

                    if ($key == 'new') {
                        if (!count($nonEmptyData)) {
                            continue;
                        }

                        $Rewrite = EndpointRewrite::create([
                            'Endpoint' => $Endpoint
                        ]);
                    } else {
                        $Rewrite = EndpointRewrite::getByID($key);

                        if ($Rewrite->EndpointID != $Endpoint->ID) {
                            return static::throwInvalidRequestError('Supplied rewrite ID does not belong to this endpoint');
                        }

                        if (!count($nonEmptyData)) {
                            $Rewrite->destroy();
                            $deleted[] = $Rewrite;
                            continue;
                        }
                    }

                    if (empty($data['Priority'])) {
                        $data['Priority'] = EndpointRewrite::getFieldOptions('Priority', 'default');
                    }

                    $Rewrite->setFields($data);

                    if ($Rewrite->isDirty) {
                        if ($Rewrite->validate()) {
                            $Rewrite->save();
                            $saved[] = $Rewrite;
                        } else {
                            $invalid[] = $Rewrite;
                        }
                    }
                }

                return static::respond('endpointRewritesSaved', [
                    'success' => count($saved) > 0,
                    'saved' => $saved,
                    'invalid' => $invalid,
                    'deleted' => $deleted,
                    'Endpoint' => $Endpoint
                ]);
            default:
                return static::throwInvalidRequestError('Only GET/POST methods are supported');
        }
    }
}