<?php

namespace Gatekeeper;

class TransactionsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Transaction::class;

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    public static $browseLimitDefault = 20;
    public static $browseOrder = ['ID' => 'DESC'];
    public static $browseCalcFoundRows = false;


    public static function handleBrowseRequest($options = [], $conditions = [], $responseID = null, $responseData = [])
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

    public static function respondCsv($responseID, $responseData = [])
    {
        if ($responseID == 'loggedRequests') {
            foreach ($responseData['data'] AS &$result) {
                $result = [
                    'timestamp' => date('Y-m-d H:i:s', $result->Created),
                    'endpoint' => $result->Endpoint->Handle,
                    'key' => $result->Key ? $result->Key->Handle : '',
                    'client_IP' => long2ip($result->ClientIP),
                    'method' => $result->Method,
                    'path' => $result->Path,
                    'query' => $result->Query,
                    'response_code' => $result->ResponseCode,
                    'response_time' => $result->ResponseTime,
                    'response_bytes' => $result->ResponseBytes
                ];
            }
        }

        return parent::respondCsv($responseID, $responseData);
    }
}