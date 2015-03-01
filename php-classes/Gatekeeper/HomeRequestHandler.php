<?php

namespace Gatekeeper;

use Gatekeeper\Endpoints\Endpoint;

class HomeRequestHandler extends \RequestHandler
{
    public static function handleRequest()
    {
        $publicEndpoints = Endpoint::getAllByWhere('Public');
        
        static::respond('home', [
            'data' => $publicEndpoints
        ]);
    }
}