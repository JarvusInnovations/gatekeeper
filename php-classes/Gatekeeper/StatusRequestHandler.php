<?php

namespace Gatekeeper;

use Cache;

class StatusRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
        ,'text/csv' => 'csv'
    ];

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        switch (static::shiftPath())
        {
            case 'cache':
                return static::handleCacheRequest();
            default:
                return static::respond('status');
        }
    }

    public static function handleCacheRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        $memInfo = apcu_sma_info(true);

        return static::respond('cacheStatus', [
            'free' => $memInfo['avail_mem'],
            'responses' => Cache::getIterator('/^response:/')->getTotalSize(),
            'total' => $memInfo['seg_size']
        ]);
    }
}