<?php

namespace Gatekeeper;

use Site;
use JSON;

class Gatekeeper
{
    public static $apiHostname;
    public static $portalHostname;
    public static $authRealm = 'Gatekeeper';

    public static function getApiBaseUrl()
    {
        $url = Site::getConfig('ssl') ? 'https://' : 'http://';

        if (static::$apiHostname) {
            $url .= is_array(static::$apiHostname) ? static::$apiHostname[0] : static::$apiHostname;
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $url .= $_SERVER['HTTP_HOST'];
            $url .= '/api';
        } else {
            $url .= Site::getConfig('primary_hostname') ?: 'localhost';
            $url .= '/api';
        }

        return $url;
    }

    public static function authorizeTestApiAccess()
    {
        if (
            $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] &&
            $_SERVER['REMOTE_ADDR'] != gethostbyname($_SERVER['HTTP_HOST'])
        ) {
            JSON::error('Access to test API denied', 403);
            exit();
        }
    }
}