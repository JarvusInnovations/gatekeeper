<?php

namespace Gatekeeper\Endpoints;

use Cache;
use ActiveRecord;
use HandleBehavior;
use RecordValidator;
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
            'unique' => true
        ],
        'Path' => [
            'type' => 'string',
            'unique' => true
        ],
#        'Version' => [
#            'type' => 'varchar',
#            'length' => 32
#        ],
        'InternalEndpoint',
        'AdminName' => [
            'notnull' => false
        ],
        'AdminEmail' => [
            'notnull' => false
        ],
        'Public' => [
            'type' => 'boolean',
            'default' => false
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
        'GlobalBandwidthCount' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'GlobalBandwidthPeriod' => [
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
        'Path' => [
            'required' => true,
            'validator' => [__CLASS__, 'validatePath']
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

    public static function getFromPath($path)
    {
        // get sorted list of paths (longest to shortest)
        if (false == ($endpointPaths = Cache::fetch('endpoint-paths'))) {
            $endpointPaths = \DB::valuesTable('ID', 'Path', 'SELECT ID, Path FROM `%s` ORDER BY LENGTH(Path) DESC', static::$tableName);

            Cache::store('endpoint-paths', $endpointPaths);
        }


        // normalize path to string without / prefix
        if (is_array($path)) {
            $path = implode('/', $path);
        }

        $path = ltrim($path, '/');


        // match longest path as prefix
        foreach ($endpointPaths AS $endpointId => $endpointPath) {
            if ($endpointPath == $path || 0 === strpos($path, $endpointPath . '/')) {
                return static::getByID($endpointId);
            }
        }


        return null;
    }

    public static function validatePath(RecordValidator $validator, Endpoint $Endpoint)
    {
        // TODO: test that path doesn't overlap with another path
        \Debug::dumpVar($Endpoint, true, 'valiadting path');
    }

    public function save($deep = true)
    {
        HandleBehavior::onSave($this, $this->Path);

        parent::save($deep);

        // clear paths cache if path changes
        if ($this->isFieldDirty('Path')) {
            Cache::delete('endpoint-paths');
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

    public function getExternalUrl()
    {
        $url = 'http://';

        if (Gatekeeper::$apiHostname) {
            $url .= Gatekeeper::$apiHostname;
        } else {
            $url .= $_SERVER['HTTP_HOST'] . '/api';
        }

        $url .= '/' . $this->Path;

        return $url;
    }
}