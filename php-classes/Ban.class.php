<?php

class Ban extends ActiveRecord
{
	// ActiveRecord configuration
	static public $tableName = 'bans';
	static public $singularNoun = 'ban';
	static public $pluralNoun = 'bans';
	static public $useCache = true;

	static public $fields = array(
		'KeyID' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'IP' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'ExpirationDate' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
		,'Notes' => array(
			'type' => 'clob'
			,'notnull' => false
		)
	);
	
	static public $relationships = array(
		'Key' => array(
			'type' => 'one-one'
			,'class' => 'Key'
		)
	);
	
	static public $sorters = array(
		'created' => array(__CLASS__, 'sortCreated')
		,'expiration' => array(__CLASS__, 'sortExpiration')
	);

	public function validate($deep = true)
	{
		parent::validate($deep);
		
		if (!$this->KeyID == !$this->IP) {
			$this->_validator->addError('Ban', 'Ban must specifiy either a API key or an IP address');
		}
		
		return $this->finishValidation();
	}
	
	static public function sortExpiration($dir, $name) {
		return "ExpirationDate $dir";
	}
	
	static public function sortCreated($dir, $name) {
		return "ID $dir";
	}
}
