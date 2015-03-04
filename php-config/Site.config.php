<?php

Site::$debug = true; // set to true for extended query logging
Site::$production = true; // set to true for heavy file caching
#Site::$autoPull = false;

Site::$permittedOrigins = '*';

Site::$skipSessionPaths[] = 'api.php';
Site::$skipSessionPaths[] = 'test-api/cachable.php';
Site::$skipSessionPaths[] = 'test-api/status.php';

if (
    Gatekeeper\Gatekeeper::$apiHostname &&
    !empty($_SERVER['HTTP_HOST']) &&
    (
        (
            is_string(Gatekeeper\Gatekeeper::$apiHostname) &&
            $_SERVER['HTTP_HOST'] == Gatekeeper\Gatekeeper::$apiHostname
        ) ||
        (
            is_array(Gatekeeper\Gatekeeper::$apiHostname) &&
            in_array($_SERVER['HTTP_HOST'], Gatekeeper\Gatekeeper::$apiHostname)
        )
    )
) {
    Site::$onInitialized = function() {
        if (empty(Site::$pathStack[0]) && Gatekeeper\Gatekeeper::$portalHostname) {
            Site::redirect('http://' . Gatekeeper\Gatekeeper::$portalHostname);
        }

        array_unshift(Site::$pathStack, 'api');
        array_unshift(Site::$requestPath, 'api');
    };
}