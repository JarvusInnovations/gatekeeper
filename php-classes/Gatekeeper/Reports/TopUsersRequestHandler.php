<?php

namespace Gatekeeper\Reports;

use DB;
use Gatekeeper\Endpoints\Endpoint;
use Gatekeeper\Metrics\MetricSample;

class TopUsersRequestHandler extends AbstractReportRequestHandler
{
    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        if (empty($_GET['time-max']) || !($timeMax = strtotime($_GET['time-max']))) {
            $timeMax = time();
        }

        if (empty($_GET['time-min']) || !($timeMin = strtotime($_GET['time-min']))) {
            $timeMin = $timeMax - 3600 * 24 * 7; // 1 week
        }
        
        if (!empty($_GET['endpoint'])) {
            if (!$Endpoint = Endpoint::getByHandle($_GET['endpoint'])) {
                return static::throwNotFoundError('endpoint not found');
            }
        }

        $topUsers = DB::allRecords(
            'SELECT'
            .'  @user := SUBSTRING_INDEX(SUBSTRING_INDEX(`Key`, "/", -2), "/", 1) AS User,'
            .'  SUBSTRING_INDEX(@user, ":", 1) AS UserType,'
            .'  SUBSTRING_INDEX(@user, ":", -1) AS UserIdentifier,'
            .'  SUM(Value) AS TotalRequests,'
            .'  MIN(Timestamp) AS EarliestRequest,'
            .'  MAX(Timestamp) AS LatestRequest'
            .' FROM `%s`'
            .' WHERE'
            .'  `Timestamp` BETWEEN "%s" AND "%s" AND '
            .'  `Key` LIKE "endpoints/%s/users/%%/requests"'
            .' GROUP BY User'
            .' ORDER BY TotalRequests DESC'
            .' LIMIT %u',
            [
                MetricSample::$tableName,
                date('Y-m-d H:i:s', $timeMin),
                date('Y-m-d H:i:s', $timeMax),
                $Endpoint ? $Endpoint->ID : '%',
                !empty($_GET['limit']) && ctype_digit($_GET['limit']) ? $_GET['limit'] : 20
            ]
        );

        return static::respond('topUsers', [
           'data' => $topUsers,
           'Endpoint' => $Endpoint
        ]);
    }
}