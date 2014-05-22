<?php

class Endpoint extends ActiveRecord
{
    public static $metricTTL = 60;
    public static $versionPattern = '/^[a-zA-Z0-9][a-zA-Z0-9\-_\.]*$/';

    // ActiveRecord configuration
    public static $tableName = 'endpoints';
    public static $singularNoun = 'endpoint';
    public static $pluralNoun = 'endpoints';
    public static $useCache = true;

    public static $fields = array(
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
        ,'DefaultVersion' => array(
            'type' => 'boolean'
            ,'default' => false
        )
    );

    public static $relationships = array(
        'Rewrites' => array(
            'type' => 'one-many'
            ,'class' => 'EndpointRewrite'
            ,'order' => 'Priority'
        )
    );

    public static $indexes = array(
        'HandleVersion' => array(
            'fields' => array('Handle', 'Version')
            ,'unique' => true
        )
    );

    public static $sorters = array(
        'calls-total' => array(__CLASS__, 'sortMetric')
        ,'calls-week' => array(__CLASS__, 'sortMetric')
        ,'responsetime' => array(__CLASS__, 'sortMetric')
        ,'keys' => array(__CLASS__, 'sortMetric')
        ,'clients' => array(__CLASS__, 'sortMetric')
    );

    public static function getByHandleAndVersion($handle, $version = null)
    {
        $cacheKey = sprintf('endpoints-lookup/%s/%s', $handle, $version ? $version : '_default');

        if ($endpointID = Cache::fetch($cacheKey)) {
            $Endpoint = static::getByID($endpointID);
        } else {
            $where = array('Handle' => $handle);

            if ($version) {
                $where['Version'] = $version;
            } else {
                $where[] = 'DefaultVersion';
            }

            if ($Endpoint = static::getByWhere($where)) {
                static::mapDependentCacheKey($Endpoint->ID, $cacheKey);
                Cache::store($cacheKey, $Endpoint->ID);
            }
        }

        return $Endpoint;
    }

    public function save($deep = true)
    {
        HandleBehavior::onSave($this);

        parent::save($deep);

        // if this is endpoint is being set to the default version, unset it from sibling endpoints
        if ($this->isFieldDirty('DefaultVersion') && $this->DefaultVersion) {
            $otherDefault = static::getByWhere(array(
                'DefaultVersion' => true
                ,'Handle' => $this->Handle
                ,'ID != ' . $this->ID
            ));

            if ($otherDefault) {
                $otherDefault->DefaultVersion = false;
                $otherDefault->save();
            }
        }
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
            ,'pattern' => static::$versionPattern
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

    public static function getMetricSQL($metricName)
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

    public static function sortMetric($dir, $name)
    {
        return static::getMetricSQL($name) . ' ' . $dir;
    }

    public function getCachedResponses($limit = null)
    {
        $cachedResponses = array();
        foreach (Cache::getIterator("/^response\:{$this->ID}/") AS $cachedResponse) {
            $cachedResponses[] = $cachedResponse;
        }

        // sort by hits desc, created desc
        usort($cachedResponses, function($a, $b) {
            if ($a['num_hits'] == $b['num_hits']) {
                if ($a['creation_time'] == $b['creation_time']) {
                    return 0;
                } else {
                    return ($a['creation_time'] > $b['creation_time']) ? -1 : 1;
                }
            } else {
                return ($a['num_hits'] > $b['num_hits']) ? -1 : 1;
            }
        });

        // limit
        if ($limit) {
            $cachedResponses = array_slice($cachedResponses, 0, $limit);
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