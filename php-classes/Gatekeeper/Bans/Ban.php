<?php

namespace Gatekeeper\Bans;

use Cache;
use Emergence\Dwoo\Engine as DwooEngine;
use Emergence\Site\Storage;
use Gatekeeper\Keys\Key;

class Ban extends \ActiveRecord
{
    public static $tableCachePeriod = 300;

    // ActiveRecord configuration
    public static $tableName = 'bans';
    public static $singularNoun = 'ban';
    public static $pluralNoun = 'bans';
    public static $collectionRoute = '/bans';
    public static $useCache = true;

    public static $fields = [
        'KeyID' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'IP' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'IPPattern' => [
            'notnull' => false
        ],
        'ExpirationDate' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'Notes' => [
            'type' => 'clob',
            'notnull' => false,
            'fulltext' => true
        ]
    ];

    public static $relationships = [
        'Key' => [
            'type' => 'one-one',
            'class' => Key::class
        ]
    ];

    public static $dynamicFields = [
        'Key'
    ];

    public static $validators = [
        'ExpirationDate' => [
            'validator' => 'datetime',
            'required' => false
        ]
    ];

    public static $sorters = [
        'created' => [__CLASS__, 'sortCreated'],
        'expiration' => [__CLASS__, 'sortExpiration']
    ];

    public function validate($deep = true)
    {
        parent::validate($deep);

        if (!$this->KeyID == !($this->IP || $this->IPPattern)) { // todo: replace when column is migrated
            $this->_validator->addError('Ban', 'Ban must specifiy either a API key or an IP address');
        }

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if ($this->isUpdated || $this->isNew) {
            Cache::delete('bans');
            Cache::delete("ip-pattern:${static::getIPPatternSafe($this->IPPattern)}");
        }
    }

    public function destroy()
    {
        $success = parent::destroy();
        Cache::delete('bans');
        Cache::delete("ip-pattern:${static::getIPPatternSafe($this->IPPattern)}");
        return $success;
    }

    public static function sortExpiration($dir, $name)
    {
        return "ExpirationDate $dir";
    }

    public static function sortCreated($dir, $name)
    {
        return "ID $dir";
    }


    public static function parseIPPatterns($ipPattern, $returnType = null)
    {
        $bansByType = [
            'ip' => [],
            'cidr' => [],
            'wildcard' => []
        ];

        foreach (preg_split("/[\s,]+/", $ipPattern) as $pattern) {
            if (!empty($pattern)) {
                $bansByType[static::getPatternType($pattern)][] = $pattern;
            }
        }

        if (!empty($returnType)) {
            return $bansByType[$returnType];
        }

        return $bansByType;
    }

    protected static $_activeBans;
    public static function getActiveBansTable()
    {
        if (isset(static::$_activeBans)) {
            return static::$_activeBans;
        }

        if (static::$_activeBans = Cache::fetch('bans')) {
            return static::$_activeBans;
        }

        static::$_activeBans = [
            'patterns' => [],
            'ips' => [],
            'keys' => []
        ];

        foreach (Ban::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Ban) {
            if ($Ban->IPPattern) {
                static::$_activeBans['patterns'][] = $Ban->IPPattern;
                static::$_activeBans['ips'] = array_merge(static::$_activeBans['ips'], static::parseIPPatterns($Ban, 'ip'));
            } elseif($Ban->KeyID) {
                static::$_activeBans['keys'][] = $Ban->KeyID;
            }
        }

        Cache::store('bans', static::$_activeBans, static::$tableCachePeriod);

        return static::$_activeBans;
    }

    public static function getIPPatternBanClosure($ipPattern, $ignoreCache = false)
    {
        static $ipPatternCaches = [];

        // todo: confirm if this is needed
        $ipPatternSafe = static::getIPPatternSafe($ipPattern);

        if (!$ignoreCache && !empty($ipPatternCaches[$ipPatternSafe])) {
            return $ipPatternCaches[$ipPatternSafe];
        }

        if (!$ignoreCache && $ipPatternCache = Cache::fetch("ip-pattern:{$ipPatternSafe}")) {
            return $ipPatternCache;
        }

        $bucketId = 'ip-patterns';
        $filesystem = Storage::getFileSystem($bucketId);
        $fileName = "matchers/{$ipPatternSafe}.php";
        $matcher = null;

        // try {
        //     $matcher = $filesystem->read($fileName);

        // } catch (\Exception $e) {
        // }

        if (!$filesystem->has($fileName)) {
            $matcher = DwooEngine::getSource('ip-patterns/ip-pattern', [
                'data' => static::parseIPPatterns($ipPattern)
            ]);

            $filesystem->write($fileName, $matcher);
        }

        $closure = require join('/', [Storage::getLocalStorageRoot(), $bucketId, $fileName]);

        // cache matcher
        $ipPatternCaches[$ipPatternSafe] = $closure;// $matcher;
        // todo: confirm if we want to cache for a specific time period
        Cache::store("ip-pattern:{$ipPatternSafe}", $closure); // $matcher

        return $closure;
        // return $matcher;
    }

    protected static function getIPPatternSafe($ipPattern)
    {
        return str_replace(['/', '*', ',', ' '], ['-', 'x', '_', '_'], $ipPattern);
    }

    protected static function getPatternType($ipPattern)
    {
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}/', $ipPattern)) {
            return 'cidr';
        } elseif (preg_match('/(\d{1,3})\.(\d{1,3})\.([0-9]{1,3}|\*)\.(\*)/', $ipPattern)) {
            return 'wildcard';
        } elseif (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $ipPattern)) {
            return 'ip';
        }
    }

    public static function isIPAddressBanned($ip, $ignoreCache = false)
    {
        $bannedPatterns = static::getActiveBansTable()['patterns'];

        // check for explicit IP ban
        if (in_array($ip, $bannedPatterns)) {
            return true;
        }

        // check IP Patterns individually
        $isBanned = false;
        foreach($bannedPatterns as $ipPattern) {
            $matcher = static::getIPPatternBanClosure($ipPattern, $ignoreCache);
            \MICS::dump(trim($matcher), 'matcher', !empty($_REQUEST['debug']));
            $closure = eval($matcher . " ?>");
            \MICS::dump($closure, 'matcher', isset($_REQUEST['debug']));
            if (call_user_func($closure, $ip)) {
                $isBanned = true;
                break;
            }
        }

        return $isBanned;
    }

    public static function isKeyBanned(Key $Key)
    {
        return in_array($Key->ID, static::getActiveBansTable()['keys']);
    }
}
