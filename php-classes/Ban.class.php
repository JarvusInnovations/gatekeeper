<?php

class Ban extends ActiveRecord
{
    static public $tableCachePeriod = 300;

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
            ,'fulltext' => true
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

        $this->_validator->validate(array(
        	'field' => 'ExpirationDate'
			,'validator' => 'datetime'
			,'required' => false
		));

		return $this->finishValidation();
	}

	public function save($deep = true)
	{
		parent::save($deep);

		if ($this->isUpdated || $this->isNew) {
			Cache::delete('bans');
		}
	}

	public function destroy()
	{
		$success = parent::destroy();
		Cache::delete('bans');
        return $success;
	}

	static public function sortExpiration($dir, $name)
	{
		return "ExpirationDate $dir";
	}

	static public function sortCreated($dir, $name)
	{
		return "ID $dir";
	}

	static public function getActiveBansTable()
	{
		if($bans = Cache::fetch('bans')) {
			return $bans;
		}

		$bans = array(
			'ips' => array()
			,'keys' => array()
		);

		foreach (Ban::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Ban) {
			if ($Ban->IP) {
				$bans['ips'][] = long2ip($Ban->IP);
			} elseif($Ban->KeyID) {
				$bans['keys'][] = $Ban->KeyID;
			}
		}

		Cache::store('bans', $bans, static::$tableCachePeriod);

		return $bans;
	}
}
