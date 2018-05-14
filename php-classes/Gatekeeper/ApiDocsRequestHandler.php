<?php

namespace Gatekeeper;

use Site;
use Gatekeeper\Gatekeeper;

class ApiDocsRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
    ];

    public static function handleRequest()
    {
        if (empty(Site::$pathStack) || empty(Site::$pathStack[0])) {
            return static::throwInvalidRequestError();
        }


        // get endpoint
        if (!$Endpoint = Endpoints\Endpoint::getFromPath(Site::$pathStack)) {
            return static::throwNotFoundError('Endpoint not found');
        }


        // check access
        if (!$Endpoint->Public) {
            $GLOBALS['Session']->requireAccountLevel('Staff');
        }

        return static::respond('docs', $Endpoint->getSwaggerData());
    }
}