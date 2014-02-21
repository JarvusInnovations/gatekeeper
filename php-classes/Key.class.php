<?php

class Key extends ActiveRecord
{
	static public $metricTTL = 60;
	
	// ActiveRecord configuration
	static public $tableName = 'keys';
	static public $singularNoun = 'key';
	static public $pluralNoun = 'keys';
	static public $useCache = true;

	static public $fields = array(
		'Key' => array(
			'unique' => true
		)
		,'OwnerName'
		,'ContactName' => array(
			'notnull' => false
		)
		,'ContactEmail' => array(
			'notnull' => false
		)
		,'ExpirationDate' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
		,'AllEndpoints' => array(
			'type' => 'boolean'
			,'default' => false
		)
	);
	
	static public $relationships = array(
		'Endpoints' => array(
			'type' => 'many-many'
			,'class' => 'Endpoint'
			,'linkClass' => 'KeyEndpoint'
		)
	);
	
	static public $sorters = array(
		'calls-total' => array(__CLASS__, 'sortMetric')
		,'calls-week' => array(__CLASS__, 'sortMetric')
		,'calls-day-avg' => array(__CLASS__, 'sortMetric')
		,'endpoints' => array(__CLASS__, 'sortMetric')
	);
	
	static public function getByKey($key)
	{
		return static::getByField('Key', $key);
	}
	
	static public function getByHandle($handle)
	{
		return static::getByKey($handle);
	}
	
	public function save($deep = true)
	{
		if (!$this->Key) {
			$this->Key = static::generateUniqueKey();
		}
		
		parent::save($deep);
	}
	
	public function validate($deep = true)
	{
		parent::validate($deep);
		
		$this->_validator->validate(array(
			'field' => 'OwnerName'
			,'minlength' => 2
		));

		$this->_validator->validate(array(
			'field' => 'ContactEmail'
			,'validator' => 'email'
			,'required' => false
		));
		
		// TODO: validate expiration date
		
		return $this->finishValidation();
	}
    
    public function getUnlinkedEndpoints()
    {
        $currentEndpoints = array_map(function($Endpoint) {
            return $Endpoint->ID;
        }, $this->Endpoints);

        return count($currentEndpoints) ? Endpoint::getAllByWhere('ID NOT IN ('.implode(',', $currentEndpoints).')') : Endpoint::getAll();
    }
	
	public static function generateUniqueKey()
	{
		do {
			$key = md5(mt_rand(0, mt_getrandmax()));
		}
		while (static::getByKey($key));
		
		return $key;
	}
	
	public function getMetric($metricName, $forceUpdate = false)
	{
		$cacheKey = "metrics/keys/$this->ID/$metricName";
		
		if (false !== ($metricValue = Cache::fetch($cacheKey))) {
			return $metricValue;
		}
		
		$metricValue = DB::oneValue('SELECT %s FROM `%s` `Key` WHERE `Key`.ID = %u', array(
			static::getMetricSQL($metricName)
			,static::$tableName
			,$this->ID
		));
		
		Cache::store($cacheKey, $metricValue, static::$metricTTL);
		
		return $metricValue;
	}
	
	static public function getMetricSQL($metricName)
	{
		switch($metricName)
		{
			case 'calls-total':
				return sprintf('(SELECT COUNT(*) FROM `%s` WHERE KeyID = `Key`.ID)', LoggedRequest::$tableName);
			case 'calls-week':
				return sprintf('(SELECT COUNT(*) FROM `%s` WHERE KeyID = `Key`.ID AND Created >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK))', LoggedRequest::$tableName);
			case 'calls-day-avg':
				return sprintf('(SELECT COUNT(*) / (DATEDIFF(MAX(Created), MIN(Created)) + 1) FROM `%s` WHERE KeyID = `Key`.ID)', LoggedRequest::$tableName);
			case 'endpoints':
				return sprintf('IF(`Key`.AllEndpoints, (SELECT COUNT(*) FROM `%s`), (SELECT COUNT(*) FROM `%s` WHERE KeyID = `Key`.ID))', Endpoint::$tableName, KeyEndpoint::$tableName);
			default:
				return 'NULL';
		}
	}
	
	static public function sortMetric($dir, $name)
	{
		return static::getMetricSQL($name) . ' ' . $dir;
	}
}
