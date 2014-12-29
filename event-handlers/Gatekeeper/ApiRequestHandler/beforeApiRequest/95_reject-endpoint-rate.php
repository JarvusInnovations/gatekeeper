<?php

namespace Gatekeeper;

use Cache;
use Gatekeeper\Alerts\RateLimitApproached;
use Gatekeeper\Alerts\RateLimitExceeded;


$Endpoint = $_EVENT['request']->getEndpoint();


// drip into endpoint bucket
if ($Endpoint->GlobalRatePeriod && $Endpoint->GlobalRateCount) {
    $flagKey = "alerts/endpoints/$Endpoint->ID/rate-flagged";

    $bucket = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
        return [
            'seconds' => $Endpoint->GlobalRatePeriod,
            'count' => $Endpoint->GlobalRateCount
        ];
    });

    if ($bucket['hits'] < 0) {
        RateLimitExceeded::open($Endpoint, [
            'request' => [
                'uri' => $_EVENT['request']->getUrl()
            ],
            'endpointId' => $Endpoint->ID,
            'bucket' => $bucket
        ]);

        Cache::store($flagKey, true);

        return ApiRequestHandler::throwRateError($bucket['seconds'], 'The global rate limit for this endpoint has been exceeded');
    }

    if ($Endpoint->AlertNearMaxRequests && $bucket['hits'] < (1 - $Endpoint->AlertNearMaxRequests) * $Endpoint->GlobalRateCount) {
        RateLimitApproached::open($Endpoint, [
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
        foreach ([RateLimitExceeded::class, RateLimitApproached::class] AS $alertClass) {
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