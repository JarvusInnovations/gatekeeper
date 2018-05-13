<?php


if (empty($_SERVER['SITE_ROOT'])) {
    error_log('SITE_ROOT must be set in environment');
    exit(1);
}

$siteRoot = $_SERVER['SITE_ROOT'];



// load bootstrap PHP code
require("${siteRoot}/php-bootstrap/bootstrap.inc.php");


// load core
Site::initialize($siteRoot, $_SERVER['HTTP_HOST'], [
    {{~#eachAlive bind.database.members as |member|~}}
        {{~#if @first}}
    'database' => [
        'host' => '{{member.sys.ip}}',
        'port' => '{{member.cfg.port}}',
        'username' => '{{member.cfg.username}}',
        'password' => '{{member.cfg.password}}',
        'database' => '{{../cfg.database.name}}'
    ]
        {{~/if~}}
    {{~/eachAlive}}
]);
