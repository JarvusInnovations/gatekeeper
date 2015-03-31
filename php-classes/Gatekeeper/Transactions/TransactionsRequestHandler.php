<?php

namespace Gatekeeper\Transactions;

use DB;
use Gatekeeper\Keys\Key;
use Gatekeeper\Endpoints\Endpoint;

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
        if (!empty($_REQUEST['endpoint'])) {
            if (!$Endpoint = Endpoint::getByHandle($_REQUEST['endpoint'])) {
                return static::throwNotFoundError('Endpoint not found');
            }

            $conditions['EndpointID'] = $Endpoint->ID;
            $responseData['Endpoint'] = $Endpoint;
        }

        // apply method filter
        if (!empty($_REQUEST['method'])) {
            $conditions['Method'] = $_REQUEST['method'];
        }

        // apply path filter
        if (!empty($_REQUEST['path-substring'])) {
            $conditions[] = 'Path LIKE "%' . DB::escape($_REQUEST['path-substring']) . '%"';
        }

        // apply path filter
        if (!empty($_REQUEST['query-substring'])) {
            $conditions[] = 'Query LIKE "%' . DB::escape($_REQUEST['query-substring']) . '%"';
        }

        // apply IP filter
        if (!empty($_REQUEST['ip'])) {
            if (!filter_var($_REQUEST['ip'], FILTER_VALIDATE_IP)) {
                return static::throwError('IP is invalid');
            }

            $conditions['ClientIP'] = ip2long($_REQUEST['ip']);
        }

        // apply key filter
        if (!empty($_REQUEST['key'])) {
            if (!$Key = Key::getByKey($_REQUEST['key'])) {
                return static::throwError('key is invalid');
            }

            $conditions['KeyID'] = $Key->ID;
        }

        // apply time filter
        if (!empty($_REQUEST['time-max']) && ($timeMax = strtotime($_REQUEST['time-max']))) {
            $conditions[] = 'Created <= "' . date('Y-m-d H:i:s', $timeMax) . '"';
        }

        if (!empty($_REQUEST['time-min']) && ($timeMin = strtotime($_REQUEST['time-min']))) {
            $conditions[] = 'Created >= "' . date('Y-m-d H:i:s', $timeMin) . '"';
        }

        // apply type filter
        if (!empty($_REQUEST['type'])) {
            if ($_REQUEST['type'] == 'ping') {
                $conditions['Class'] = PingTransaction::class;
            } elseif ($_REQUEST['type'] == 'consumer') {
                $conditions['Class'] = Transaction::class;
            }
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