<?php


// determine root paths
$coreRoot = '{{#if cfg.core.root}}{{ cfg.core.root }}{{else}}{{ pkg.path }}/core{{/if}}';
$siteRoot = '{{#if cfg.site.root}}{{ cfg.site.root }}{{else}}{{ pkg.path }}/site{{/if}}';


// determine hostname
$hostname = empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST'];


// load bootstrap PHP code
require("${coreRoot}/vendor/autoload.php");


// load core
Site::initialize($siteRoot, $hostname, [
    {{~#eachAlive bind.database.members as |member|~}}
        {{~#if @first}}
    'database' => [
        'host' => '{{ member.sys.ip }}',
        'port' => '{{ member.cfg.port }}',
        'username' => '{{ member.cfg.username }}',
        'password' => '{{ member.cfg.password }}',
        'database' => '{{ ../cfg.database.name }}'
    ],
        {{~/if~}}
    {{~/eachAlive}}

    'primary_hostname' => {{toJson cfg.site.primary_hostname}},
    'hostnames' => {{toJson cfg.site.hostnames}},

    'logger' => [
        'dump' => {{toJson cfg.logger.dump}},
        'root' => '{{ pkg.svc_var_path }}/logs'
    ]
]);
