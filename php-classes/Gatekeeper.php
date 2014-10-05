<?php

class Gatekeeper
{
    public static $apiHostname;
    public static $authRealm = 'Gatekeeper';

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