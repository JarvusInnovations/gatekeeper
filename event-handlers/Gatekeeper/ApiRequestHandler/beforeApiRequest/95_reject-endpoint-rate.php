<?php

namespace Gatekeeper;


$Endpoint = $_EVENT['request']->getEndpoint();


// drip into endpoint bucket
if ($Endpoint->GlobalRatePeriod && $Endpoint->GlobalRateCount) {
    $bucket = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
        return [
            'seconds' => $Endpoint->GlobalRatePeriod,
            'count' => $Endpoint->GlobalRateCount
        ];
    });

    if ($bucket['hits'] < 0) {
        Alerts\RateLimitExceeded::open($Endpoint, [
            'request' => [
                'uri' => $_EVENT['request']->getUrl()
            ],
            'endpointId' => $Endpoint->ID,
            'bucket' => $bucket
        ]);

        return ApiRequestHandler::throwRateError($bucket['seconds'], 'The global rate limit for this endpoint has been exceeded');
    }

    if ($bucket['hits'] < (1 - $Endpoint->AlertNearMaxRequests) * $Endpoint->GlobalRateCount) {
        Alerts\RateLimitApproached::open($Endpoint, [
            'request' => [
                'uri' => $_EVENT['request']->getUrl()
            ],
            'endpointId' => $Endpoint->ID,
            'bucket' => $bucket
        ]);
    }
}