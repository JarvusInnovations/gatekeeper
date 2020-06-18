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
        }
    }

    public function destroy()
    {
        $success = parent::destroy();
        Cache::delete('bans');
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
            'keys' => []
        ];

        foreach (Ban::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Ban) {
            if ($Ban->IPPattern) {
                static::$_activeBans['patterns'][] = $Ban->IPPattern;
            } elseif($Ban->KeyID) {
                static::$_activeBans['keys'][] = $Ban->KeyID;
            }
        }

        Cache::store('bans', static::$_activeBans, static::$tableCachePeriod);

        return static::$_activeBans;
    }

    public static function getIPPatternBanClosure($ipPattern)
    {
        static $ipPatternCaches = [];

        $ipPatternSafe = static::getIPPatternSafe($ipPattern);

        if (in_array($ipPatternSafe, $ipPatternCaches)) {
            return $ipPatternCaches[$ipPatternSafe];
        }

        if ($ipPatternCache = Cache::fetch("ip-pattern:{$ipPatternSafe}")) {
            return $ipPatternCache;
        }

        // todo: use or remove?
        //$localStorageRoot = Storage::getLocalStorageRoot();
        $fileName = "/patterns/matchers/{$ipPatternSafe}.php";

        try {
            return Storage::getFileSystem('site-root')->read(ltrim($fileName, '/'));
        } catch (\Exception $e) {
            $ipPatternSplit = [];
            foreach (preg_split("/[\s,]+/", $ipPattern) as $pattern) {
                $ipPatternSplit[$pattern] = static::getPatternType($pattern);
            }
            $order = ['ip', 'cidr', 'wildcard'];
            uasort($ipPatternSplit, function($a, $b) use ($order) {
                if ($a === $b) {
                    return 0;
                } else {
                    return (array_search($a, $order) - array_search($b, $order));
                }
            });

            $phpScript =  DwooEngine::getSource('ip-patterns/ip-pattern', [
                'data' => $ipPatternSplit
            ]);

            Storage::getFileSystem('site-root')->write(ltrim($fileName, '/'), $phpScript);

            return $phpScript;
        }

        return null;

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

    public static function isIPAddressBanned($ip)
    {
        $bannedPatterns = static::getActiveBansTable()['patterns'];

        if (in_array($ip, $bannedPatterns)) {
            return true;
        }

        $isBanned = false;
        foreach($bannedPatterns as $ipPattern) {
            $closure = eval( '?>' . static::getIPPatternBanClosure($ipPattern));
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
