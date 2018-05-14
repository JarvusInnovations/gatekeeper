<?php

if (Site::getConfig('parent_hostname') == null) {
    Git::$repositories['emergence-skeleton-v1'] = [
        'remote' => 'https://github.com/JarvusInnovations/emergence-skeleton.git',
        'originBranch' => 'master',
        'workingBranch' => 'master',
        'trees' => [
            'api-docs',
            'dwoo-plugins',
            'event-handlers',
            'ext-library',
            'html-templates',
            'js-library',
            'php-classes',
            'php-config',
            'php-migrations',
            'phpunit-tests',
            'sencha-build',
            'sencha-workspace',
            'site-root',
            'site-tasks'
        ]
    ];

    Git::$repositories['symfony-yaml'] = [
        'remote' => 'https://github.com/symfony/Yaml.git'
        ,'originBranch' => '3.4'
        ,'workingBranch' => '3.4'
        ,'trees' => [
            'php-classes/Symfony/Component/Yaml' => [
                'path' => '.'
                ,'exclude' => [
                    '#\\.gitignore$#'
                    ,'#^/Tests#'
                    ,'#^/Command#'
                    ,'#\\.md$#'
                    ,'#composer\\.json#'
                    ,'#phpunit\\.xml\\.dist#'
                ]
            ]
        ]
    ];

    Git::$repositories['utf8'] = [
        'remote' => 'https://github.com/tchwork/utf8.git'
        ,'originBranch' => 'master'
        ,'workingBranch' => 'master'
        ,'trees' => [
            'php-classes/Patchwork/Utf8.php' => 'src/Patchwork/Utf8.php'
        ]
    ];

    Git::$repositories['psr-http-message'] = [
        'remote' => 'https://github.com/php-fig/http-message.git'
        ,'originBranch' => 'master'
        ,'workingBranch' => 'master'
        ,'trees' => [
            'php-classes/Psr/Http/Message' => 'src'
        ]
    ];
}
