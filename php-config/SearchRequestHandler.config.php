<?php

SearchRequestHandler::$searchClasses = array(
	'Endpoint' => array(
		'fields' => array(
			'Title'
			,array(
				'field' => 'Handle'
				,'method' => 'like'
			)
		)
		,'conditions' => array('Class' => 'Endpoint')
	)
	,'Key' => array(
		'fields' => array(
			'OwnerName'
		)
		,'conditions' => array('Class' => 'Key')
	)
);