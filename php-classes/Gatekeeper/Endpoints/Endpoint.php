<?php

namespace Gatekeeper\Endpoints;

use Cache;
use ActiveRecord;
use HandleBehavior;
use Gatekeeper\Metrics\Metrics;

class Endpoint extends ActiveRecord
{
    public static $metricTTL = 60;
    protected $_metricsCache = [
        'counters' => [],
        'averages' => []
    ];

    // ActiveRecord configuration
    public static $tableName = 'endpoints';
    public static $singularNoun = 'endpoint';
    public static $pluralNoun = 'endpoints';
    public static $collectionRoute = '/endpoints';
    public static $useCache = true;

    public static $fields = [
        'Title',
        'Handle' => [
            'type' => 'varchar',
            'length' => 32
        ],
        'Version' => [
            'type' => 'varchar',
            'length' => 32
        ],
        'InternalEndpoint',
        'AdminName' => [
            'notnull' => false
        ],
        'AdminEmail' => [
            'notnull' => false
        ],
        'DeprecationDate' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'GlobalRateCount' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'GlobalRatePeriod' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'UserRateCount' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'UserRatePeriod' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'KeyRequired' => [
            'type' => 'boolean',
            'default' => false
        ],
        'CachingEnabled' => [
            'type' => 'boolean',
            'default' => true
        ],
        'AlertOnError' => [
            'type' => 'boolean',
            'default' => true
        ],
        'AlertNearMaxRequests' => [
            'type' => 'decimal',
            'length' => '3,2',
            'notnull' => false
        ],
        'DefaultVersion' => [
            'type' => 'boolean',
            'default' => false
        ],
        'PingFrequency' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'PingURI' => [
            'type' => 'string',
            'notnull' => false
        ],
        'PingTestPattern' => [
            'type' => 'string',
            'notnull' => false
        ]
    ];

    public static $relationships = [
        'Rewrites' => [
            'type' => 'one-many',
            'class' => EndpointRewrite::class,
            'order' => 'Priority'
        ]
    ];

    public static $validators = [
        'Title' => [
            'minlength' => 2
        ],
        'Handle' => [
            'required' => false,
            'validator' => 'handle',
            'errorMessage' => 'Handle can only contain letters, numbers, hyphens, and underscores'
        ],
        'Version' => [
            'validator' => 'handle',
            'allowNumeric' => true,
            'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9\-_\.]*$/',
            'errorMessage' => 'Version is required and can only contain letters, numbers, hyphens, periods, and underscores'
        ],
        'InternalEndpoint' => 'URL',
        'AdminEmail' => [
            'validator' => 'email',
            'required' => false
        ],
        'DeprecationDate' => [
            'validator' => 'datetime',
            'required' => false
        ],
        'GlobalRateCount' => [
            'validator' => 'number',
            'required' => false,
            'min' => 1
        ],
        'GlobalRatePeriod' => [
            'validator' => 'number',
            'required' => false,
            'min' => 1
        ],
        'UserRateCount' => [
            'validator' => 'number',
            'required' => false,
            'min' => 1
        ],
        'UserRatePeriod' => [
            'validator' => 'number',
            'required' => false,
            'min' => 1
        ]
    ];

    public static $indexes = [
        'HandleVersion' => [
            'fields' => ['Handle', 'Version'],
            'unique' => true
        ]
    ];

#    public static $sorters = array(
#        'calls-total' => array(__CLASS__, 'sortMetric')
#        ,'calls-week' => array(__CLASS__, 'sortMetric')
#        ,'responsetime' => array(__CLASS__, 'sortMetric')
#        ,'keys' => array(__CLASS__, 'sortMetric')
#        ,'clients' => array(__CLASS__, 'sortMetric')
#    );

    public static function getByHandleAndVersion($handle, $version = null)
    {
        $cacheKey = sprintf('endpoints-lookup/%s/%s', $handle, $version ? $version : '_default');

        if ($endpointID = Cache::fetch($cacheKey)) {
            $Endpoint = static::getByID($endpointID);
        } else {
            $where = ['Handle' => $handle];

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

    public function getTitle()
    {
        return $this->Title . ' v' . $this->Version;
    }

    public function getURL($suffix = '/', $params = [])
    {
        $suffix = ltrim($suffix, '/');
        $suffix = 'v' . $this->Version . ($suffix ? '/' . $suffix : '');

        return parent::getURL($suffix, $params);
    }

    public function save($deep = true)
    {
        HandleBehavior::onSave($this);

        parent::save($deep);

        // if this is endpoint is being set to the default version, unset it from sibling endpoints
        if ($this->isFieldDirty('DefaultVersion') && $this->DefaultVersion) {
            $otherDefault = static::getByWhere([
                'DefaultVersion' => true,
                'Handle' => $this->Handle,
                'ID != ' . $this->ID
            ]);

            if ($otherDefault) {
                $otherDefault->DefaultVersion = false;
                $otherDefault->save();
            }
        }
    }
    
    public function getCounterMetric($counterName)
    {
        if (!array_key_exists($counterName, $this->_metricsCache['counters'])) {
            $this->_metricsCache['counters'][$counterName] = Metrics::estimateCounter("endpoints/$this->ID/$counterName");
        }

        return $this->_metricsCache['counters'][$counterName];
    }
    
    public function getAverageMetric($averageName, $counterName)
    {
        if (!array_key_exists($averageName, $this->_metricsCache['averages'])) {
            $this->_metricsCache['averages'][$averageName] = Metrics::estimateAverage("endpoints/$this->ID/$averageName", "endpoints/$this->ID/$counterName");
        }

        return $this->_metricsCache['averages'][$averageName];
    }

#    public function getMetric($metricName, $forceUpdate = false)
#    {
#        $cacheKey = "metrics/endpoints/$this->ID/$metricName";
#
#        if (false !== ($metricValue = Cache::fetch($cacheKey))) {
#            return $metricValue;
#        }
#
#        try {
#            $metricValue = DB::oneValue('SELECT %s FROM `%s` Endpoint WHERE Endpoint.ID = %u', array(
#                static::getMetricSQL($metricName)
#                ,static::$tableName
#                ,$this->ID
#            ));
#
#            Cache::store($cacheKey, $metricValue, static::$metricTTL);
#        } catch (TableNotFoundException $e) {
#            return null;
#        }
#
#        return $metricValue;
#    }
#
#    public static function getMetricSQL($metricName)
#    {
#        switch($metricName)
#        {
#            case 'calls-total':
#                return sprintf('(SELECT COUNT(*) FROM `%s` WHERE EndpointID = Endpoint.ID)', Transaction::$tableName);
#            case 'calls-week':
#                return sprintf('(SELECT COUNT(*) FROM `%s` WHERE EndpointID = Endpoint.ID AND Created >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK))', Transaction::$tableName);
#            case 'responsetime':
#                return sprintf('(SELECT AVG(ResponseTime) FROM `%s` WHERE EndpointID = Endpoint.ID)', Transaction::$tableName);
#            case 'keys':
#                return sprintf('(SELECT COUNT(*) FROM `%s` K LEFT JOIN `%s` KE ON (KE.KeyID = K.ID) WHERE K.AllEndpoints OR KE.EndpointID = Endpoint.ID)', Key::$tableName, KeyEndpoint::$tableName);
#            case 'clients':
#                return sprintf('(SELECT COUNT(DISTINCT ClientIP) FROM `%s` WHERE EndpointID = Endpoint.ID)', Transaction::$tableName);
#            default:
#                return 'NULL';
#        }
#    }
#
#    public static function sortMetric($dir, $name)
#    {
#        return static::getMetricSQL($name) . ' ' . $dir;
#    }

    public function getCachedResponses($limit = null)
    {
        $cachedResponses = [];
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

    public function getExternalPath()
    {
        return "/$this->Handle/v$this->Version";
    }

    public function getExternalUrl()
    {
        $url = 'http://';

        if (Gatekeeper::$apiHostname) {
            $url .= Gatekeeper::$apiHostname;
        } else {
            $url .= $_SERVER['HTTP_HOST'] . '/api';
        }

        $url .= $this->getExternalPath();

        return $url;
    }
}