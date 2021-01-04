<?php

namespace Gatekeeper\Bans;

use Cache;
use Gatekeeper\Keys\Key;
use Gatekeeper\Utils\IPPattern;

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

        if (!$this->KeyID == !$this->IPPattern) {
            $this->_validator->addError('Ban', 'Ban must specify either a API key or an IP pattern');
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
            'ips' => [],
            'keys' => []
        ];

        foreach (Ban::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Ban) {
            if (!empty($Ban->IPPattern)) {
                $parsed = IPPattern::parse($Ban->IPPattern);

                if (is_array($parsed)) {
                    // ip pattern ONLY contains static IPs
                    static::$_activeBans['ips'] = array_merge(static::$_activeBans['ips'], $parsed);
                } else {
                    static::$_activeBans['patterns'][] = $Ban->IPPattern;
                }
            } elseif ($Ban->KeyID) {
                static::$_activeBans['keys'][] = $Ban->KeyID;
            }
        }

        Cache::store('bans', static::$_activeBans, static::$tableCachePeriod);

        return static::$_activeBans;
    }

    public static function isIPAddressBanned($ip)
    {
        $activeBans = static::getActiveBansTable();

        // check for explicit IP ban
        if (in_array($ip, $activeBans['ips'])) {
            return true;
        }

        // check IP Patterns individually
        foreach ($activeBans['patterns'] as $ipPattern) {
            if (IPPattern::match($ipPattern, $ip)) {
                return true;
            }
        }

        return false;
    }

    public static function isKeyBanned(Key $Key)
    {
        return in_array($Key->ID, static::getActiveBansTable()['keys']);
    }
}
