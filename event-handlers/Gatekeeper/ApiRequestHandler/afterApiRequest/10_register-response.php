<?php

namespace Gatekeeper;

use Gatekeeper\Metrics\Metrics;


$Endpoint = $_EVENT['request']->getEndpoint();


// append metrics
$_EVENT['metrics']['endpointResponsesExecuted'] = Metrics::appendCounter("endpoints/$Endpoint->ID/responsesExecuted");
$_EVENT['metrics']['endpointBytesExecuted'] = Metrics::appendCounter("endpoints/$Endpoint->ID/bytesExecuted", $_EVENT['Transaction']->ResponseBytes);
$_EVENT['metrics']['endpointResponseTime'] = Metrics::appendAverage("endpoints/$Endpoint->ID/responseTime", $_EVENT['Transaction']->ResponseTime, $_EVENT['metrics']['endpointResponsesExecuted']);


// drip bandwidth bucket
if ($Endpoint->GlobalBandwidthPeriod && $Endpoint->GlobalBandwidthCount) {
    HitBuckets::drip("endpoints/$Endpoint->ID/bandwidth", function() use ($Endpoint) {
        return [
            'seconds' => $Endpoint->GlobalBandwidthPeriod,
            'count' => $Endpoint->GlobalBandwidthCount
        ];
    }, $_EVENT['Transaction']->ResponseBytes);
}