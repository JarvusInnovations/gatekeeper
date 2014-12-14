<?php

namespace Gatekeeper;

use Endpoint;

class CachedResponsesRequestHandler extends \RequestHandler
{
    public static $defaultLimit = 20;
    public static $userResponseModes = [
        'application/json' => 'json'
        ,'text/csv' => 'csv'
    ];

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');
        
        if (empty($_REQUEST['endpoint']) || empty($_REQUEST['endpointVersion'])) {
            return static::throwInvalidRequestError('endpoint and endpointVersion required');
        } elseif (!$Endpoint = Endpoint::getByHandleAndVersion($_REQUEST['endpoint'], $_REQUEST['endpointVersion'])) {
            return static::throwNotFoundError('Endpoint not found');
        }

        $cachedResponses = $Endpoint->getCachedResponses();
    	$limit = isset($_GET['limit']) && ctype_digit($_GET['limit']) ? (integer)$_GET['limit'] : static::$defaultLimit;
		$offset = isset($_GET['offset']) && ctype_digit($_GET['offset']) ? (integer)$_GET['offset'] : 0;

        return static::respond('cachedResponses', [
            'success' => true
            ,'data' => $limit ? array_slice($cachedResponses, $offset, $limit) : $cachedResponses
            ,'total' => count($cachedResponses)
            ,'limit' => $limit
            ,'offset' => $offset
            ,'Endpoint' => $Endpoint
        ]);
    }
}