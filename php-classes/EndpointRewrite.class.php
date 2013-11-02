<?php

class EndpointRewrite extends ActiveRecord
{
	// ActiveRecord configuration
	static public $tableName = 'endpoint_rewrites';
	static public $singularNoun = 'endpoint rewrite';
	static public $pluralNoun = 'endpoint rewrites';

	static public $fields = array(
		'EndpointID' => 'uint'
		,'Pattern'
		,'Template'
		,'Priority' => array(
			'type' => 'uint'
			,'default' => 100
		)
	);
	
	static public $relationships = array(
		'Endpoint' => array(
			'type' => 'one-one'
			,'class' => 'Endpoint'
		)
	);
}
