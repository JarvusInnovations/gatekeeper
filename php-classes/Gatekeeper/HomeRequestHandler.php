<?php

namespace Gatekeeper;

use DB;
use Cache;
use TableNotFoundException;
use Gatekeeper\Metrics\MetricSample;
use Gatekeeper\Endpoints\Endpoint;

class HomeRequestHandler extends \RequestHandler
{
    public static $popularityTTL = 3600;
    public static $publicTTL = 60;

    public static $userResponseModes = [
        'application/json' => 'json',
        'text/csv' => 'csv'
    ];

    public static function handleRequest()
    {
        // get request totals for trailing week
        if (false === ($weeklyRequestsByEndpoint = Cache::fetch('endpoints-requests-week'))) {
            $weeklyRequestsByEndpoint = array_map('intval', DB::valuesTable(
                'EndpointID',
                'requests',
                'SELECT'
                .'  SUBSTRING_INDEX(@context := SUBSTRING_INDEX(`Key`, "/", 2), "/", -1) AS EndpointID,'
                .'  SUM(Value) AS requests'
                .' FROM `%s`'
                .' WHERE'
                .'  `Timestamp` BETWEEN DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK) AND CURRENT_TIMESTAMP AND '
                .'  `Key` LIKE "endpoints/%%" AND'
                .'  `Key` REGEXP "^endpoints/[[:digit:]]+/requests$"'
                .' GROUP BY EndpointID',
                [
                    MetricSample::$tableName
                ]
            ));

            Cache::store('endpoints-requests-week', $weeklyRequestsByEndpoint, static::$popularityTTL);
        }


        // get public endpoints
        if (false === ($publicEndpointIds = Cache::fetch('endpoints-public'))) {
            try {
                $publicEndpointIds = array_map('intval', DB::allValues(
                    'ID',
                    'SELECT ID FROM `%s` WHERE Public',
                    Endpoint::$tableName
                ));
            } catch (TableNotFoundException $e) {
                $publicEndpointIds = [];
            }

            Cache::store('endpoints-public', $publicEndpointIds, static::$publicTTL);
        }


        // fetch endpoint instances
        $publicEndpoints = array_map(function($endpointId) {
            return Endpoint::getByID($endpointId);
        }, $publicEndpointIds);


        // determine requested order
        if (!empty($_GET['order']) && $_GET['order'] == 'alpha') {
            $order = 'alpha';
            $sortFn = function($a, $b) {
                return strcasecmp($a->Path, $b->Path);
            };
        } elseif (!empty($_GET['order']) && $_GET['order'] == 'newest') {
            $order = 'newest';
            $sortFn = function($a, $b) {
                if ($a->ID == $b->ID) {
                    return 0;
                }

                return ($a->ID < $b->ID) ? 1 : -1;
            };
        } else {
            $order = 'popularity';
            $sortFn = function($a, $b) use ($weeklyRequestsByEndpoint) {
                $a = isset($weeklyRequestsByEndpoint[$a->ID]) ? $weeklyRequestsByEndpoint[$a->ID] : 0;
                $b = isset($weeklyRequestsByEndpoint[$b->ID]) ? $weeklyRequestsByEndpoint[$b->ID] : 0;

                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? 1 : -1;
            };
        }


        // apply order
        usort($publicEndpoints, $sortFn);


        static::respond('home', [
            'data' => $publicEndpoints,
            'order' => $order
        ]);
    }
}