<?php

Git::$repositories['GateKeeper'] = [
    'remote' => 'git@github.com:JarvusInnovations/Gatekeeper.git',
    'originBranch' => 'master',
    'workingBranch' => 'master',
    'localOnly' => true,
    'trees' => [
        'event-handlers',
        'html-templates',
        'php-classes',
        'php-config',
        'php-migrations',
        'phpunit-tests',
        'site-root',
        'sencha-workspace',
        'dwoo-plugins'
    ]
];