<?php

namespace Gatekeeper;

use Gatekeeper\Endpoints\Endpoint;


// read endpoint handle from path
if (!$endpointHandle = $_EVENT['request']->shiftPathStack()) {
    return ApiRequestHandler::throwInvalidRequestError('Endpoint handle required');
}


// read endpoint version from path and get endpoint
if (
    ($endpointVersion = $_EVENT['request']->peekPathStack()) &&
    $endpointVersion[0] == 'v' &&
    ($endpointVersion = substr($endpointVersion, 1)) &&
    preg_match(Endpoint::$validators['Version']['pattern'], $endpointVersion) &&
    ($Endpoint = Endpoint::getByHandleAndVersion($endpointHandle, $endpointVersion))
) {
    $_EVENT['request']->shiftPathStack();
} else {
    $Endpoint = Endpoint::getByHandleAndVersion($endpointHandle);
}


// ensure endpoint was found
if (!$Endpoint) {
    return ApiRequestHandler::throwNotFoundError('Requested endpoint+version not found, be sure to specify a version if this endpoint requires it');
}


// save determined endpoint to request object
$_EVENT['request']->setEndpoint($Endpoint);