<?php

$Endpoint = $_EVENT['request']->getEndpoint();
$userIdentifier = $_EVENT['request']->getUserIdentifier();


// drip into endpoint+user bucket first so that abusive users can't pollute the global bucket
if ($Endpoint->UserRatePeriod && $Endpoint->UserRateCount) {
    $bucket = HitBuckets::drip("endpoints/$Endpoint->ID/$userIdentifier", function() use ($Endpoint) {
        return array('seconds' => $Endpoint->UserRatePeriod, 'count' => $Endpoint->UserRateCount);
    });

    if ($bucket['hits'] < 0) {
        return ApiRequestHandler::throwRateError($bucket['seconds'], 'Your rate limit for this endpoint has been exceeded');
    }
}


// TODO: Remove hitbucket and just use $_EVENT['metrics']['endpoint-user-requests'] ? This would lose custom rate intervals