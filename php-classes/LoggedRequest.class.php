<?php

class LoggedRequest extends ActiveRecord
{
	// ActiveRecord configuration
	static public $tableName = 'requests_log';
	static public $singularNoun = 'logged request';
	static public $pluralNoun = 'logged requests';

	static public $fields = array(
		'CreatorID' => null
		,'EndpointID' => array(
			'type' => 'uint'
			,'index' => true
		)
		,'KeyID' => array(
			'type' => 'uint'
			,'notnull' => false
			,'index' => true
		)
		,'ClientIP' => 'uint'
		,'Method'
		,'Path'
		,'Query'
		,'ResponseTime' => array(
			'type' => 'mediumint'
			,'unsigned' => true
		)
		,'ResponseCode' => array(
			'type' => 'smallint'
			,'unsigned' => true
		)
		,'ResponseBytes' => array(
			'type' => 'mediumint'
			,'unsigned' => true
		)
	);
	
	static public $relationships = array(
		'Endpoint' => array(
			'type' => 'one-one'
			,'class' => 'Endpoint'
		)
		,'Key' => array(
			'type' => 'one-one'
			,'class' => 'Key'
		)
	);
}
