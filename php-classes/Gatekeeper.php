<?php

class Gatekeeper
{
    public static $apiHostname;

    public static function authorizeTestApiAccess()
    {
        if (
            $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] &&
            $_SERVER['REMOTE_ADDR'] != gethostbyname($_SERVER['HTTP_HOST'])
        ) {
            header('HTTP/1.1 403 Forbidden');
            JSON::error('Access to test API denied');
            exit();
        }
    }
}