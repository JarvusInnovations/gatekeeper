<?php

$Endpoint = $_EVENT['request']->getEndpoint();


// abort if response shouldn't be cached
if (!$Endpoint->CachingEnabled) {
    return false;
}

if (empty($_EVENT['responseHeaders']['Expires'])) {
    return false;
}

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    return false;
}


// retrieve and evaluate expiration time
$startTime = $_EVENT['request']->getStartTime();
$expires = strtotime($_EVENT['responseHeaders']['Expires']);

if ($expires <= $startTime) {
    return false;
}


// compile cachable headers
$cachableHeaders = array();

foreach ($_EVENT['responseHeaders'] AS $headerKey => $headerValue) {
    $header = "$headerKey: $headerValue";
    foreach (ApiRequestHandler::$passthruHeaders AS $pattern) {
        if (preg_match($pattern, $header)) {
            $cachableHeaders[] = $header;
            break;
        }
    }
}


// save response to cache
$url = $_EVENT['request']->getUrl();
$LoggedRequest = $_EVENT['LoggedRequest'];

Cache::store("response:$Endpoint->ID:$url", array(
    'path' => $LoggedRequest->Path
    ,'query' => $LoggedRequest->Query
    ,'expires' => $expires
    ,'headers' => $cachableHeaders
    ,'body' => $_EVENT['responseBody']
), $expires - $startTime);