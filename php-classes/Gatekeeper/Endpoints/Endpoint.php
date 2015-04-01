<?php

namespace Gatekeeper\Endpoints;

use Site;
use Cache;
use ActiveRecord;
use HandleBehavior;
use RecordValidator;
use TableNotFoundException;
use Emergence\People\IPerson;
use Gatekeeper\Gatekeeper;
use Gatekeeper\Metrics\Metrics;
use Gatekeeper\Alerts\AbstractAlert;
use Symfony\Component\Yaml\Yaml;

class Endpoint extends ActiveRecord
{
    public static $metricTTL = 60;
    public static $validPathRegex = '/^[a-zA-Z][a-zA-Z0-9_\\-\\.]*(\\/[a-zA-Z][a-zA-Z0-9_\\-\\.]*)*$/';
    protected $_metricsCache = [
        'counters' => [],
        'averages' => []
    ];
    protected static $_downEndpointIds;

    // ActiveRecord configuration
    public static $tableName = 'endpoints';
    public static $singularNoun = 'endpoint';
    public static $pluralNoun = 'endpoints';
    public static $collectionRoute = '/endpoints';
    public static $useCache = true;

    public static $fields = [
        'Title' => [
            'includeInSummary' => true
        ],
        'Handle' => [
            'unique' => true
        ],
        'Path' => [
            'unique' => true
        ],
        'InternalEndpoint' => [
            'accountLevelEnumerate' => 'Staff'
        ],
        'AdminName' => [
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'AdminEmail' => [
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'Public' => [
            'type' => 'boolean',
            'default' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'Description' => [
            'type' => 'clob',
            'notnull' => false
        ],
        'DeprecationDate' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'GlobalRateCount' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'GlobalRatePeriod' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'UserRateCount' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'UserRatePeriod' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'GlobalBandwidthCount' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'GlobalBandwidthPeriod' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'KeyRequired' => [
            'type' => 'boolean',
            'default' => false
        ],
        'KeySelfRegistration' => [
            'type' => 'boolean',
            'default' => false
        ],
        'CachingEnabled' => [
            'type' => 'boolean',
            'default' => true,
            'accountLevelEnumerate' => 'Staff'
        ],
        'AlertOnError' => [
            'type' => 'boolean',
            'default' => true,
            'accountLevelEnumerate' => 'Staff'
        ],
        'AlertNearMaxRequests' => [
            'type' => 'decimal',
            'length' => '3,2',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'PingFrequency' => [
            'type' => 'uint',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'PingURI' => [
            'type' => 'string',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
        ],
        'PingTestPattern' => [
            'type' => 'string',
            'notnull' => false,
            'accountLevelEnumerate' => 'Staff'
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
            try {
                $endpointPaths = \DB::valuesTable('ID', 'Path', 'SELECT ID, Path FROM `%s` ORDER BY LENGTH(Path) DESC', static::$tableName);
            } catch(TableNotFoundException $e) {
                $endpointPaths = [];
            }

            Cache::store('endpoint-paths', $endpointPaths);
        }


        // normalize path to string without / prefix
        if (is_array($path)) {
            $path = implode('/', $path);
        }

        $path = ltrim($path, '/');


        // match longest path as prefix
        foreach ($endpointPaths AS $endpointId => $endpointPath) {
            if (0 === strcasecmp($endpointPath, $path) || 0 === stripos($path, $endpointPath . '/')) {
                return static::getByID($endpointId);
            }
        }


        return null;
    }

    public static function validatePath(RecordValidator $validator, Endpoint $Endpoint)
    {
        if (!$Endpoint->Path) {
            $validator->addError('Path', 'Path must not be empty');
            return;
        }

        if ($Endpoint->Path[0] == '/') {
            $validator->addError('Path', 'Path must not start with /');
            return;
        }

        if (substr($Endpoint->Path, -1) == '/') {
            $validator->addError('Path', 'Path must not end with /');
            return;
        }

        if (!preg_match(static::$validPathRegex, $Endpoint->Path)) {
            $validator->addError('Path', 'Path must start with a letter and only contain letters, numbers, periods, hyphens, and underscores');
            return;
        }

        $duplicateConditions = [
            'Path' => $Endpoint->Path
        ];

        if ($Endpoint->ID) {
            $duplicateConditions[] = 'ID != ' . $Endpoint->ID;
        }

        if (Endpoint::getByWhere($duplicateConditions)) {
            $validator->addError('Path', 'Path matches an existing endpoint\'s');
            return;
        }
    }

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // implement handles
        HandleBehavior::onValidate($this, $this->_validator);

        // save results
        return $this->finishValidation();
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
            $url .= Site::getConfig('primary_hostname') . '/api';
        }

        $url .= '/' . $this->Path;

        return $url;
    }

    public function getSwagger()
    {
        // get docs
        $docPath = 'api-docs/' . $this->Path . '.yml';

        // TODO: cache docs with a fileWrite invalidator
        if ($docNode = Site::resolvePath($docPath)) {
            $swagger = Yaml::parse(file_get_contents($docNode->RealPath));
        } else {
            $swagger = [];
        }


        // populate Gatekeeper-configured swagger values
        $swagger['host'] = Gatekeeper::$apiHostname ?: Site::getConfig('primary_hostname');
        $swagger['basePath'] = (Gatekeeper::$apiHostname ? '/' : '/api/') . $this->Path;
        $swagger['schemes'] = ['http'];

        if (Site::getConfig('ssl')) {
            array_unshift($swagger['schemas'], 'https');
        }

        if (empty($swagger['info'])) {
            $swagger['info'] = [];
        }

        $swagger['info']['title'] = $this->Title;
        $swagger['info']['x-handle'] = $this->Handle;
        $swagger['info']['x-internal-id'] = $this->ID;
        $swagger['info']['x-subscribed'] = (boolean)$this->getSubscription();
        $swagger['info']['x-key-required'] = $this->KeyRequired;
        $swagger['info']['x-key-self-registration'] = $this->KeySelfRegistration;

        if (empty($swagger['info']['description']) && $this->Description) {
            $swagger['info']['description'] = $this->Description;
        }

        if (empty($swagger['info']['contact'])) {
            if ($this->AdminName) {
                $swagger['info']['contact']['name'] = $this->AdminName;
            }

            if ($this->AdminEmail) {
                $swagger['info']['contact']['email'] = $this->AdminEmail;
            }

            if (Gatekeeper::$portalHostname) {
                $swagger['info']['contact']['url'] = 'http://' . Gatekeeper::$portalHostname;
            }
        }

        if ($this->DeprecationDate) {
            $swagger['info']['x-deprecation-date'] = $this->DeprecationDate;
        }

        return $swagger;
    }

    public static function getDownEndpoints()
    {
        if (isset(static::$_downEndpointIds)) {
            return static::$_downEndpointIds;
        }

        $cacheKey = 'endpoints-down';

        if (false === ($endpointIds = Cache::fetch($cacheKey))) {
            $endpointIds = [];

            foreach (AbstractAlert::getAllByField('Status', 'open') AS $Alert) {
                if ($Alert::$isFatal) {
                    $endpointIds[] = $Alert->EndpointID;
                }
            }

            $endpointIds = array_unique($endpointIds);

            Cache::store($cacheKey, $endpointIds, static::$metricTTL);
        }

        return static::$_downEndpointIds = $endpointIds;
    }

    public function isDown()
    {
        return in_array($this->ID, static::getDownEndpoints());
    }

    public function getSubscription(IPerson $Person = null)
    {
        if (!$Person) {
            if (!$Person = $GLOBALS['Session']->Person) {
                return null;
            }
        }

        return Subscription::getByWhere([
            'EndpointID' => $this->ID,
            'PersonID' => $Person->ID
        ]);
    }
}