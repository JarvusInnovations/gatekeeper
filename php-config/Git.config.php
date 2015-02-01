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

Git::$repositories['swagger-ui'] = [
    'remote' => 'https://github.com/swagger-api/swagger-ui.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'master'
    ,'trees' => [
        'site-root/lib/swagger-ui' => 'dist'
    ]
];

Git::$repositories['symfony-yaml'] = [
    'remote' => 'https://github.com/symfony/Yaml.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'master'
    ,'trees' => [
        'php-classes/Symfony/Component/Yaml' => [
            'path' => '.'
            ,'exclude' => [
                '#\\.gitignore$#'
                ,'#^/Tests#'
                ,'#\\.md$#'
                ,'#composer\\.json#'
                ,'#phpunit\\.xml\\.dist#'
            ]
        ]
    ]
];