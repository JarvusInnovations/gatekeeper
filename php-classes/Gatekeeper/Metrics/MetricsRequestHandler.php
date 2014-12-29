<?php

namespace Gatekeeper\Metrics;

use DB;
use Gatekeeper\Endpoints\Endpoint;

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
            default:
                return static::throwInvalidRequestError('Global metrics are not yet implemented');
        }
    }

    public static function handleEndpointsCurrentRequest()
    {
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

        return static::respond('currentEndpointMetrics', [
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

        return static::respond('historicEndpointMetrics', [
           'data' => DB::allRecords(
                'SELECT'
                .'  Timestamp,'
                .'  SUBSTRING_INDEX(@context := SUBSTRING_INDEX(`Key`, "/", 2), "/", -1) AS EndpointID,'
                .'  SUBSTRING(`Key`, LENGTH(@context) + 2) AS Metric,'
                .'  Value'
                .' FROM `%s`'
                .' WHERE'
                .'  `Timestamp` BETWEEN "%s" AND "%s" AND '
                .'  `Key` LIKE "endpoints/%%" AND'
                .'  `Key` REGEXP "^endpoints/[[:digit:]]+/[^/]+$"'
                .' ORDER BY ID'
                .' LIMIT %u',
                [
                    MetricSample::$tableName,
                    date('Y-m-d H:i:s', $timeMin),
                    date('Y-m-d H:i:s', $timeMax),
                    !empty($_GET['limit']) && ctype_digit($_GET['limit']) ? $_GET['limit'] : 20
                ]
            )
        ]);
    }
}