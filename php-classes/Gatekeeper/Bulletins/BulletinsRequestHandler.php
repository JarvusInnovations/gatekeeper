<?php

namespace Gatekeeper\Bulletins;

use ActiveRecord;

use Gatekeeper\Endpoints\Endpoint;

class BulletinsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Bulletin::class;
    public static $browseOrder = ['ID' => 'DESC'];

    public static $accountLevelRead = false;
    public static $accountLevelComment = 'User';
    public static $accountLevelBrowse = false;
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = false;

    public static function handleBrowseRequest($options = [], $conditions = [], $responseID = null, $responseData = [])
    {
        // apply status filter
        if (!empty($_GET['status'])) {
            if ($_GET['status'] == 'any') {
                $status = null;
            } elseif (in_array($_GET['status'], Bulletin::getFieldOptions('Status', 'values'))) {
                $status = $_GET['status'];
            }

            $responseData['status'] = $conditions['Status'] = $status;
        }


        // apply endpoint filter
        if (!empty($_GET['endpoint'])) {
            if ($_GET['endpoint'] == 'none') {
                $conditions[] = 'EndpointID IS NULL';
            } elseif (!$Endpoint = Endpoint::getByHandle($_GET['endpoint'])) {
                return static::throwNotFoundError('Endpoint not found');
            }
        }

        if (isset($Endpoint)) {
            $conditions['EndpointID'] = $Endpoint->ID;
            $responseData['Endpoint'] = $Endpoint;
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }
}