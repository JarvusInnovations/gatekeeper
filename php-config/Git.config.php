<?php

Git::$repositories['GateKeeper'] = array(
    'remote' => 'git@github.com:JarvusInnovations/GateKeeper.git'
    ,'originBranch' => 'master'
    ,'workingBranch' => 'master'
    ,'localOnly' => true
    ,'trees' => array(
        'event-handlers'
        ,'html-templates'
        ,'php-classes'
        ,'php-config'
        ,'phpunit-tests'
        ,'site-root'
        ,'sencha-workspace'
        ,'dwoo-plugins'
    )
);