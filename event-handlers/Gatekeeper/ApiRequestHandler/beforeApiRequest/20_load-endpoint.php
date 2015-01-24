<?php

namespace Gatekeeper;

use Gatekeeper\Endpoints\Endpoint;


// detect endpoint if it has not already been set
if (!$_EVENT['request']->getEndpoint()) {

    if (!$Endpoint = Endpoint::getFromPath($_EVENT['request']->getPathStack())) {
        return ApiRequestHandler::throwNotFoundError('No endpoint was found that can handle this path');
    }

    // save determined endpoint to request object
    $_EVENT['request']->setEndpoint($Endpoint);
}