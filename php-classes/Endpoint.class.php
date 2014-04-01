<?php

class Endpoint extends ActiveRecord
{
	static public $metricTTL = 60;
	
	// ActiveRecord configuration
	static public $tableName = 'endpoints';
	static public $singularNoun = 'endpoint';
	static public $pluralNoun = 'endpoints';
	static public $useCache = true;

	static public $fields = array(
		'Title'
		,'Handle' => array(
            'type' => 'varchar'
            ,'length' => 32
        )
		,'Version' => array(
            'type' => 'varchar'
            ,'length' => 32
        )
		,'InternalEndpoint'
		,'AdminName' => array(
			'notnull' => false
		)
		,'AdminEmail' => array(
			'notnull' => false
		)
		,'DeprecationDate' => array(
			'type' => 'timestamp'
			,'notnull' => false
		)
		,'GlobalRateCount' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'GlobalRatePeriod' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'UserRateCount' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'UserRatePeriod' => array(
			'type' => 'uint'
			,'notnull' => false
		)
		,'KeyRequired' => array(
			'type' => 'boolean'
			,'default' => false
		)
		,'CachingEnabled' => array(
			'type' => 'boolean'
			,'default' => true
		)
		,'AlertOnError' => array(
			'type' => 'boolean'
			,'default' => true
		)
		,'AlertNearMaxRequests' => array(
			'type' => 'decimal'
			,'length' => '3,2'
			,'notnull' => false
		)
	);
	
	static public $relationships = array(
		'Rewrites' => array(
			'type' => 'one-many'
			,'class' => 'EndpointRewrite'
            ,'order' => 'Priority'
		)
	);
    
    static public $indexes = array(
        'HandleVersion' => array(
            'fields' => array('Handle', 'Version')
            ,'unique' => true
        )
    );
	
	static public $sorters = array(
		'calls-total' => array(__CLASS__, 'sortMetric')
		,'calls-week' => array(__CLASS__, 'sortMetric')
		,'responsetime' => array(__CLASS__, 'sortMetric')
		,'keys' => array(__CLASS__, 'sortMetric')
		,'clients' => array(__CLASS__, 'sortMetric')
	);
	
	static public function getByHandleAndVersion($handle, $version)
	{
        $cacheKey = "endpoints-lookup/$handle/$version";
        
        if ($endpointID = Cache::fetch($cacheKey)) {
            $Endpoint = static::getByID($endpointID);
        } elseif($Endpoint = static::getByWhere(array('Handle' => $handle, 'Version' => $version))) {
            static::mapDependentCacheKey($Endpoint->ID, $cacheKey);
            Cache::store($cacheKey, $Endpoint->ID);
        }
        
        return $Endpoint;
	}
	
	public function save($deep = true)
	{
		HandleBehavior::onSave($this);
		
		parent::save($deep);
	}
	
	public function validate($deep = true)
	{
		parent::validate($deep);
		
		$this->_validator->validate(array(
			'field' => 'Title'
			,'minlength' => 2
		));
        
        $this->_validator->validate(array(
			'field' => 'Handle'
			,'required' => false
			,'validator' => 'handle'
			,'errorMessage' => 'Handle can only contain letters, numbers, hyphens, and underscores'
		));
        
        $this->_validator->validate(array(
			'field' => 'Version'
			,'validator' => 'handle'
            ,'allowNumeric' => true
            ,'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9\-_\.]*$/'
			,'errorMessage' => 'Version is required and can only contain letters, numbers, hyphens, periods, and underscores'
		));

    	$this->_validator->validate(array(
			'field' => 'InternalEndpoint'
			,'validator' => 'URL'
		));

        $this->_validator->validate(array(
			'field' => 'AdminEmail'
			,'validator' => 'email'
			,'required' => false
		));

        $this->_validator->validate(array(
    		'field' => 'DeprecationDate'
			,'validator' => 'datetime'
			,'required' => false
		));

        $this->_validator->validate(array(
            'field' => 'GlobalRateCount'
			,'validator' => 'number'
			,'required' => false
            ,'min' => 1
		));

        $this->_validator->validate(array(
        	'field' => 'GlobalRatePeriod'
			,'validator' => 'number'
			,'required' => false
            ,'min' => 1
		));

        $this->_validator->validate(array(
            'field' => 'UserRateCount'
			,'validator' => 'number'
			,'required' => false
            ,'min' => 1
		));

        $this->_validator->validate(array(
        	'field' => 'UserRatePeriod'
			,'validator' => 'number'
			,'required' => false
            ,'min' => 1
		));
		
		return $this->finishValidation();
	}
	
	public function getMetric($metricName, $forceUpdate = false)
	{
		$cacheKey = "metrics/endpoints/$this->ID/$metricName";
		
		if (false !== ($metricValue = Cache::fetch($cacheKey))) {
			return $metricValue;
		}
		
		try {
			$metricValue = DB::oneValue('SELECT %s FROM `%s` Endpoint WHERE Endpoint.ID = %u', array(
				static::getMetricSQL($metricName)
				,static::$tableName
				,$this->ID
			));
		
			Cache::store($cacheKey, $metricValue, static::$metricTTL);
		} catch (TableNotFoundException $e) {
			return null;
		}
		
		return $metricValue;
	}
	
	static public function getMetricSQL($metricName)
	{
		switch($metricName)
		{
			case 'calls-total':
				return sprintf('(SELECT COUNT(*) FROM `%s` WHERE EndpointID = Endpoint.ID)', LoggedRequest::$tableName);
			case 'calls-week':
				return sprintf('(SELECT COUNT(*) FROM `%s` WHERE EndpointID = Endpoint.ID AND Created >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK))', LoggedRequest::$tableName);
			case 'responsetime':
				return sprintf('(SELECT AVG(ResponseTime) FROM `%s` WHERE EndpointID = Endpoint.ID)', LoggedRequest::$tableName);
			case 'keys':
				return sprintf('(SELECT COUNT(*) FROM `%s` K LEFT JOIN `%s` KE ON (KE.KeyID = K.ID) WHERE K.AllEndpoints OR KE.EndpointID = Endpoint.ID)', Key::$tableName, KeyEndpoint::$tableName);
			case 'clients':
				return sprintf('(SELECT COUNT(DISTINCT ClientIP) FROM `%s` WHERE EndpointID = Endpoint.ID)', LoggedRequest::$tableName);
			default:
				return 'NULL';
		}
	}
	
	static public function sortMetric($dir, $name)
	{
		return static::getMetricSQL($name) . ' ' . $dir;
	}
    
    public function getCachedResponses()
    {
        $cachedResponses = array();
        foreach (Cache::getIterator("/^response\:{$this->ID}/") AS $cachedResponse) {
            $cachedResponses[] = $cachedResponse;
        }
        
        return $cachedResponses;
    }
    
    public function getNotificationEmailRecipient()
    {
        $email = $this->AdminEmail;
        
        if (!$email) {
            return null;
        }
        
        if ($this->AdminName) {
            $email = "$this->AdminName <$email>";
        }
        
        return $email;
    }
    
    public function applyRewrites($url)
    {
        $cacheKey = "endpoints/$this->ID/rewrites";

        // get ordered list of rewrites
        if (false == ($rewriteIDs = Cache::fetch($cacheKey))) {
            $rewriteIDs = array_map(function($Rewrite) {
                return $Rewrite->ID;
            }, $this->Rewrites);
            
            Cache::store($cacheKey, $rewriteIDs);
        }
        
        // apply each rewrite
        foreach ($rewriteIDs AS $rewriteID) {
            $Rewrite = EndpointRewrite::getByID($rewriteID);

            if (preg_match($Rewrite->Pattern, $url)) {
                $url = preg_replace($Rewrite->Pattern, $Rewrite->Replace, $url);
                
                if ($Rewrite->Last) {
                    break;
                }
            }
        }
        
        return $url;
    }
    
    public function getExternalUrl()
    {
        $url = 'http://';

        if (Gatekeeper::$apiHostname) {
            $url .= Gatekeeper::$apiHostname;
        } else {
            $url .= $_SERVER['HTTP_HOST'] . '/api';
        }
        
        $url .= "/$this->Handle/v$this->Version";
        
        return $url;
    }
}