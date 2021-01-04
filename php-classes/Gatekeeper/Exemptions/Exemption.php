<?php

namespace Gatekeeper\Exemptions;

use Cache;
use Gatekeeper\ApiRequest;
use Gatekeeper\Keys\Key;
use Gatekeeper\Utils\IPPattern;

class Exemption extends \ActiveRecord
{
    public static $tableCachePeriod = 300;

    // ActiveRecord configuration
    public static $tableName = 'exemptions';
    public static $singularNoun = 'exemption';
    public static $pluralNoun = 'exemptions';
    public static $collectionRoute = '/exemptions';
    public static $useCache = true;

    public static $fields = [
        'KeyID' => [
            'type' => 'uint',
            'default' => null
        ],
        'IPPattern' => [
            'default' => null
        ],
        'ExpirationDate' => [
            'type' => 'timestamp',
            'default' => null
        ],
        'BypassGlobalLimit' => [
            'type' => 'boolean',
            'default' => false
        ],
        'Notes' => [
            'type' => 'clob',
            'default' => null,
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
            $this->_validator->addError('Exemption', 'Exemption must specify either a API key or an IP pattern');
        }

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if ($this->isUpdated || $this->isNew) {
            Cache::delete('exemptions');
        }
    }

    public function destroy()
    {
        $success = parent::destroy();
        Cache::delete('exemptions');
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

    public static function getForApiRequest(ApiRequest $request)
    {
        $exemptions = static::getActiveExemptionsTable();

        // look for IP match first
        $clientAddress = $request->getClientAddress();
        if ($clientAddress && !empty($exemptions['ips'])) {
            foreach ($exemptions['ips'] as $ip => $exemptionId) {
                if ($ip == $clientAddress) {
                    return static::getById($exemptionId);
                }
            }
        }

        // look for key match second
        $Key = $request->getKey();
        if ($Key && !empty($exemptions['keys'])) {
            foreach ($exemptions['keys'] as $keyId => $exemptionId) {
                if ($keyId == $Key->ID) {
                    return static::getById($exemptionId);
                }
            }
        }

        // finally, execute pattern matches
        if (!empty($exemptions['patterns'])) {
            foreach ($exemptions['patterns'] as $pattern => $exemptionId) {
                if (IPPattern::match($pattern, $ip)) {
                    return static::getById($exemptionId);
                }
            }
        }

        return null;
    }

    public static function getActiveExemptionsTable()
    {
        static $cache = null;

        if ($cache) {
            return $cache;
        }

        if ($cache = Cache::fetch('exemptions')) {
            return $cache;
        }

        $cache = [
            'patterns' => [],
            'ips' => [],
            'keys' => []
        ];

        foreach (static::getAllByWhere('ExpirationDate IS NULL OR ExpirationDate > CURRENT_TIMESTAMP') AS $Exemption) {
            if (!empty($Exemption->IPPattern)) {
                $parsed = IPPattern::parse($Exemption->IPPattern);

                if (is_array($parsed)) {
                    // ip pattern ONLY contains static IPs
                    foreach ($parsed as $ip) {
                        $cache['ips'][$ip] = $Exemption->ID;
                    }
                } else {
                    $cache['patterns'][$Exemption->IPPattern] = $Exemption->ID;
                }
            } elseif ($Exemption->KeyID) {
                $cache['keys'][$Exemption->KeyID] = $Exemption->ID;
            }
        }

        Cache::store('exemptions', $cache, static::$tableCachePeriod);

        return $cache;
    }
}
