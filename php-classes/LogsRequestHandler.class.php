<?php

class LogsRequestHandler extends RecordsRequestHandler
{
    public static $recordClass = 'LoggedRequest';

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    public static $browseLimitDefault = 20;
    public static $browseOrder = array('ID' => 'DESC');
    public static $browseCalcFoundRows = false;


    public static function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
    {
        // apply endpoint filter
        if (!empty($_REQUEST['endpoint']) && !empty($_REQUEST['endpointVersion'])) {
            if (!$Endpoint = Endpoint::getByHandleAndVersion($_REQUEST['endpoint'], $_REQUEST['endpointVersion'])) {
                return static::throwNotFoundError('Endpoint not found');
            }

            $conditions['EndpointID'] = $Endpoint->ID;
            $responseData['Endpoint'] = $Endpoint;
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }

    public static function respondCsv($responseID, $responseData = array())
    {
        if ($responseID == 'loggedRequests') {
            foreach ($responseData['data'] AS &$result) {
                $result = array(
                    'timestamp' => date('Y-m-d H:i:s', $result->Created)
                    ,'endpoint' => $result->Endpoint->Handle
                    ,'key' => $result->Key ? $result->Key->Handle : ''
                    ,'client_IP' => long2ip($result->ClientIP)
                    ,'method' => $result->Method
                    ,'path' => $result->Path
                    ,'query' => $result->Query
                    ,'response_code' => $result->ResponseCode
                    ,'response_time' => $result->ResponseTime
                    ,'response_bytes' => $result->ResponseBytes
                );
            }
        }

        return parent::respondCsv($responseID, $responseData);
    }
}