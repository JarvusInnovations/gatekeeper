<?php

namespace Gatekeeper;

use Cache;
use Gatekeeper\Metrics\Metrics;


$Endpoint = $_EVENT['request']->getEndpoint();
$url = $_EVENT['request']->getUrl();


// attempt to load response from cache if enabled for this endpoint
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $Endpoint->CachingEnabled) {
    $cacheKey = "response:$Endpoint->ID:$url";

    if ($cachedResponse = Cache::fetch($cacheKey)) {
        if ($cachedResponse['expires'] < $_EVENT['request']->getStartTime()) {
            Cache::delete($cacheKey);
            $cachedResponse = false;
        } else {
            foreach ($cachedResponse['headers'] AS $header) {
                header($header);
            }
            print($cachedResponse['body']);
            
            $_EVENT['metrics']['endpointResponsesCached'] = Metrics::appendCounter("endpoints/$Endpoint->ID/responsesCached");
            $_EVENT['metrics']['endpointBytesCached'] = Metrics::appendCounter("endpoints/$Endpoint->ID/bytesCached", strlen($cachedResponse['body']));
            \Site::finishRequest();
        }
    }
}