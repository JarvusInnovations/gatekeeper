<?php

namespace Gatekeeper\Metrics;

use DB;
use Gatekeeper\Endpoints\Endpoint;
use Gatekeeper\Keys\Key;

class MetricsRequestHandler extends \RequestHandler
{
    public static $userResponseModes = array(
        'application/json' => 'json'
        ,'text/csv' => 'csv'
    );

    public static function handleRequest($action = null)
    {
        switch ($action = $action ?: static::shiftPath()) {
            case 'endpoints-current':
                return static::handleEndpointsCurrentRequest();
            case 'endpoints-historic':
                return static::handleEndpointsHistoricRequest();
            case 'keys-current':
                return static::handleKeysCurrentRequest();
            default:
                return static::throwInvalidRequestError('Global metrics are not yet implemented');
        }
    }

    public static function handleEndpointsCurrentRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        $results = [];

        foreach (Endpoint::getAll() AS $Endpoint) {
            $results[] = [
                'EndpointID' => $Endpoint->ID,

                // TODO: move this list to Metric config var and implement Endpoint->getAllMetrics
                'requests' => $Endpoint->getCounterMetric('requests'),
                'responseTime' => $Endpoint->getAverageMetric('responseTime', 'requests'),
                'responsesExecuted' => $Endpoint->getCounterMetric('responsesExecuted'),
                'responsesCached' => $Endpoint->getCounterMetric('responsesCached'),
                'bytesExecuted' => $Endpoint->getCounterMetric('bytesExecuted'),
                'bytesCached' => $Endpoint->getCounterMetric('bytesCached')
            ];
        }

        return static::respond('currentEndpointsMetrics', [
           'data' => $results
        ]);
    }

    public static function handleEndpointsHistoricRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        if (empty($_GET['time-max']) || !($timeMax = strtotime($_GET['time-max']))) {
            $timeMax = time();
        }

        if (empty($_GET['time-min']) || !($timeMin = strtotime($_GET['time-min']))) {
            $timeMin = $timeMax - 3600 * 24 * 7; // 1 week
        }

        if (!empty($_GET['metrics']) && preg_match('/^[a-zA-Z|]+$/', $_GET['metrics'])) {
            $metricPattern = '(' . $_GET['metrics'] . ')';
        } else {
            $metricPattern = '[^/]+';
        }

        return static::respond('historicEndpointMetrics', [
            'data' => array_map(
                function($row) {
                    $row['Timestamp'] = intval($row['Timestamp']);
                    $row['EndpointID'] = intval($row['EndpointID']);
                    $row['Value'] = intval($row['Value']);
                    return $row;
                },
                DB::allRecords(
                    'SELECT'
                    .'  UNIX_TIMESTAMP(Timestamp) AS Timestamp,'
                    .'  SUBSTRING_INDEX(@context := SUBSTRING_INDEX(`Key`, "/", 2), "/", -1) AS EndpointID,'
                    .'  SUBSTRING(`Key`, LENGTH(@context) + 2) AS Metric,'
                    .'  Value'
                    .' FROM `%s`'
                    .' WHERE'
                    .'  `Timestamp` BETWEEN "%s" AND "%s" AND '
                    .'  `Key` LIKE "endpoints/%%" AND'
                    .'  `Key` REGEXP "^endpoints/[[:digit:]]+/%s$"'
                    .' ORDER BY ID DESC',
                    [
                        MetricSample::$tableName,
                        date('Y-m-d H:i:s', $timeMin),
                        date('Y-m-d H:i:s', $timeMax),
                        $metricPattern
                    ]
                )
            )
        ]);
    }

    public static function handleKeysCurrentRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        $results = [];

        foreach (Key::getAll() AS $Key) {
            $results[] = [
                'KeyID' => $Key->ID,

                // TODO: move this list to Metric config var and implement Endpoint->getAllMetrics
                'requests' => $Key->getCounterMetric('requests'),
                'responseTime' => $Key->getAverageMetric('responseTime', 'requests'),
                'responsesExecuted' => $Key->getCounterMetric('responsesExecuted'),
                'responsesCached' => $Key->getCounterMetric('responsesCached'),
                'bytesExecuted' => $Key->getCounterMetric('bytesExecuted'),
                'bytesCached' => $Key->getCounterMetric('bytesCached')
            ];
        }

        return static::respond('currentKeysMetrics', [
           'data' => $results
        ]);
    }
}