<?php

namespace Gatekeeper;

use Cache;

class Ban extends \ActiveRecord
{
    public static $tableCachePeriod = 300;

    // ActiveRecord configuration
    public static $tableName = 'bans';
    public static $singularNoun = 'ban';
    public static $pluralNoun = 'bans';
    public static $useCache = true;

    public static $fields = array(
        'KeyID' => array(
            'type' => 'uint'
            ,'notnull' => false
        )
        ,'IP' => array(
            'type' => 'uint'
            ,'notnull' => false
        )
        ,'ExpirationDate' => array(
            'type' => 'timestamp'
            ,'notnull' => false
        )
        ,'Notes' => array(
            'type' => 'clob'
            ,'notnull' => false
            ,'fulltext' => true
        )
    );

    public static $relationships = array(
        'Key' => array(
            'type' => 'one-one'
            ,'class' => Key::class
        )
    );

    public static $sorters = array(
        'created' => array(__CLASS__, 'sortCreated')
        ,'expiration' => array(__CLASS__, 'sortExpiration')
    );

    public function validate($deep = true)
    {
        parent::validate($deep);

        if (!$this->KeyID == !$this->IP) {
            $this->_validator->addError('Ban', 'Ban must specifiy either a API key or an IP address');
        }

        $this->_validator->validate(array(
            'field' => 'ExpirationDate'
            ,'validator' => 'datetime'
            ,'required' => false
        ));

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

        static::$_activeBans = array(
            'ips' => array()
            ,'keys' => array()
        );

        foreach (Ban::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Ban) {
            if ($Ban->IP) {
                static::$_activeBans['ips'][] = long2ip($Ban->IP);
            } elseif($Ban->KeyID) {
                static::$_activeBans['keys'][] = $Ban->KeyID;
            }
        }

        Cache::store('bans', static::$_activeBans, static::$tableCachePeriod);

        return static::$_activeBans;
    }

    public static function isIPAddressBanned($ip)
    {
        return in_array($ip, static::getActiveBansTable()['ips']);
    }

    public static function isKeyBanned(Key $Key)
    {
        return in_array($Key->ID, static::getActiveBansTable()['keys']);
    }
}
