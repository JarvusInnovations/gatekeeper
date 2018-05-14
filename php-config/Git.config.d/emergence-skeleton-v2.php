<?php

if (Site::getConfig('handle') == 'skeleton-v2') {
    Git::$repositories['emergence-skeleton-v2'] = [
        'remote' => 'https://github.com/JarvusInnovations/emergence-skeleton-v2.git',
        'originBranch' => 'master',
        'workingBranch' => 'master',
        'trees' => [
            'html-templates',
            'php-classes',
            'php-config',
            'php-migrations',
            'sencha-workspace',
            'site-root'
        ]
    ];
}
