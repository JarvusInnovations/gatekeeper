<?php

namespace Gatekeeper;

use Gatekeeper\Endpoints\Endpoint;


// detect endpoint if it has not already been set
if (!$_EVENT['request']->getEndpoint()) {

    if (!$Endpoint = Endpoint::getFromPath($_EVENT['request']->getPathStack())) {
        return ApiRequestHandler::throwNotFoundError('No endpoint was found that can handle this path');
    }


    // trim path stack
    $_EVENT['request']->setPathStack(
        array_slice(
            $_EVENT['request']->getPathStack(),
            substr_count($Endpoint->Path, '/') + 1
        )
    );


    // save determined endpoint to request object
    $_EVENT['request']->setEndpoint($Endpoint);
}