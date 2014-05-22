<?php

class Key extends ActiveRecord
{
    public static $metricTTL = 60;

    // ActiveRecord configuration
    public static $tableName = 'keys';
    public static $singularNoun = 'key';
    public static $pluralNoun = 'keys';
    public static $useCache = true;

    public static $fields = array(
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

    public static $relationships = array(
        'Endpoints' => array(
            'type' => 'many-many'
            ,'class' => 'Endpoint'
            ,'linkClass' => 'KeyEndpoint'
        )
    );

    public static $sorters = array(
        'calls-total' => array(__CLASS__, 'sortMetric')
        ,'calls-week' => array(__CLASS__, 'sortMetric')
        ,'calls-day-avg' => array(__CLASS__, 'sortMetric')
        ,'endpoints' => array(__CLASS__, 'sortMetric')
    );

    public static function getByKey($key)
    {
        return static::getByField('Key', $key);
    }

    public static function getByHandle($handle)
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

    public function canAccessEndpoint(Endpoint $Endpoint)
    {
        if ($this->AllEndpoints) {
            return true;
        }

        $cacheKey = "keys/$this->ID/endpoints";
        if (false == ($allowedEndpoints = Cache::fetch($cacheKey))) {
            $allowedEndpoints = DB::allValues(
                'EndpointID'
                ,'SELECT EndpointID FROM `%s` KeyEndpoint WHERE KeyID = %u'
                ,array(
                    KeyEndpoint::$tableName
                    ,$this->ID
                )
            );

            Cache::store($cacheKey, $allowedEndpoints);
        }

        return in_array($Endpoint->ID, $allowedEndpoints);
    }

    public static function generateUniqueKey()
    {
        do {
            $key = md5(mt_rand(0, mt_getrandmax()));
        }
        while (static::getByKey($key));

        return $key;
    }

    public static function getMetricSQL($metricName)
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

    public static function sortMetric($dir, $name)
    {
        return static::getMetricSQL($name) . ' ' . $dir;
    }
}
