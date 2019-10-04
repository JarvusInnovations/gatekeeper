<?php

namespace Gatekeeper;

use Cache;
use Gatekeeper\Alerts\BandwidthLimitApproached;
use Gatekeeper\Alerts\BandwidthLimitExceeded;


$Endpoint = $_EVENT['request']->getEndpoint();


// check endpoint bandwidth bucket (unlike requests, it can't be dripped until after the request)
if ($Endpoint->GlobalBandwidthPeriod && $Endpoint->GlobalBandwidthCount) {
    $flagKey = "alerts/endpoints/$Endpoint->ID/bandwidth-flagged";
    $bucket = HitBuckets::fetch("endpoints/$Endpoint->ID/bandwidth");

    if ($bucket && $bucket['hits'] < 0) {
        BandwidthLimitExceeded::open($Endpoint, [
            'request' => [
                'uri' => $_EVENT['request']->getUrl()
            ],
            'endpointId' => $Endpoint->ID,
            'bucket' => $bucket
        ]);

        Cache::store($flagKey, true);

        return ApiRequestHandler::throwRateError($bucket['seconds'], 'The global bandwidth limit for this endpoint has been exceeded');
    }

    if ($bucket && $Endpoint->AlertNearMaxRequests && $bucket['hits'] < (1 - $Endpoint->AlertNearMaxRequests) * $Endpoint->GlobalBandwidthCount) {
        BandwidthLimitApproached::open($Endpoint, [
            'request' => [
                'uri' => $_EVENT['request']->getUrl()
            ],
            'endpointId' => $Endpoint->ID,
            'bucket' => $bucket
        ]);

        Cache::store($flagKey, true);

    } elseif (Cache::fetch($flagKey)) {
        Cache::delete($flagKey);

        // automatically close any open alerts if there is a flag in the cache
        // TODO: maybe do this in a cron job instead?
        foreach ([BandwidthLimitExceeded::class, BandwidthLimitApproached::class] AS $alertClass) {
            $OpenAlert = $alertClass::getByWhere([
                'Class' => $alertClass,
                'EndpointID' => $Endpoint->ID,
                'Status' => 'open'
            ]);

            if ($OpenAlert) {
                $OpenAlert->Status = 'closed';
                $OpenAlert->save();
            }
        }
    }
}