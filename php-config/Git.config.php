<?php

Git::$repositories['GateKeeper'] = array(
	'remote' => 'git@github.com:JarvusInnovations/GateKeeper.git'
	,'originBranch' => 'master'
	,'workingBranch' => 'master'
	,'localOnly' => true
	,'trees' => array(
		'html-templates'
		,'php-classes'
		,'php-config'
		,'site-root'
	)
);