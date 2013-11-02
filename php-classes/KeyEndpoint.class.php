<?php

class KeyEndpoint extends ActiveRecord
{
	// ActiveRecord configuration
	static public $tableName = 'key_endpoints';
	static public $singularNoun = 'key endpoint';
	static public $pluralNoun = 'key endpoints';

	static public $fields = array(
		'KeyID' => 'uint'
		,'EndpointID' => 'uint'
	);
	
	static public $relationships = array(
		'Key' => array(
			'type' => 'one-one'
			,'class' => 'Key'
		)
		,'Endpoint' => array(
			'type' => 'one-one'
			,'class' => 'Endpoint'
		)
	);
	
	public function validate($deep = true)
	{
		parent::validate($deep);
		
		if (!$this->KeyID || !$this->EndpointID) {
			$this->_validator->addError('KeyEndpoint', 'Both a KeyID and an EndpointID must be specified');
		}
		
		return $this->finishValidation();
	}
}
