<?php

namespace Gatekeeper;

$Endpoint = $_EVENT['request']->getEndpoint();


// drip into endpoint bucket
if ($Endpoint->GlobalRatePeriod && $Endpoint->GlobalRateCount) {
    $bucket = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
        return array('seconds' => $Endpoint->GlobalRatePeriod, 'count' => $Endpoint->GlobalRateCount);
    });

    if ($bucket['hits'] < (1 - $Endpoint->AlertNearMaxRequests) * $Endpoint->GlobalRateCount) {
        ApiRequestHandler::sendAdminNotification($Endpoint, 'endpointRateLimitNear', array(
            'bucket' => $bucket
        ), "endpoints/$Endpoint->ID/rate-warning-sent");
    }

    if ($bucket['hits'] < 0) {
        ApiRequestHandler::sendAdminNotification($Endpoint, 'endpointRateLimitReached', array(
            'bucket' => $bucket
        ), "endpoints/$Endpoint->ID/rate-notification-sent");

        return ApiRequestHandler::throwRateError($bucket['seconds'], 'The global rate limit for this endpoint has been exceeded');
    }
}