<?php

namespace Gatekeeper;

use Site;
use JSON;
use Emergence\Site\Client;

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
        $clientIp = Client::getAddress();

        if (
            $clientIp != $_SERVER['SERVER_ADDR'] &&
            $clientIp != gethostbyname($_SERVER['HTTP_HOST'])
        ) {
            JSON::error('Access to test API denied', 403);
            exit();
        }
    }
}