<?php

namespace Gatekeeper\Exemptions;

use Cache;
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
}
