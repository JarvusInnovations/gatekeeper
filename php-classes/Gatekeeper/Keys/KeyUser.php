<?php

namespace Gatekeeper\Keys;

use Emergence\People\IPerson;
use Emergence\People\Person;
use Gatekeeper\Endpoints\Endpoint;

class KeyUser extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'key_users';
    public static $singularNoun = 'key user';
    public static $pluralNoun = 'key users';

    public static $fields = [
        'KeyID' => 'uint',
        'PersonID' => 'uint',
        'Role' => [
            'type' => 'enum',
            'values' => ['user', 'owner'],
            'default' => 'user'
        ]
    ];

    public static $relationships = [
        'Key' => [
            'type' => 'one-one',
            'class' => Key::class
        ],
        'Person' => [
            'type' => 'one-one',
            'class' => Person::class
        ]
    ];

    public static $dynamicFields = [
        'Key',
        'Person'
    ];

    public static $validators = [
        'Key' => 'require-relationship',
        'Person' => 'require-relationship'
    ];

    public static $indexes = [
        'PersonKey' => [
            'fields' => ['PersonID', 'KeyID'],
            'unique' => true
        ]
    ];

    public static function getAllForEndpointUser(Endpoint $Endpoint, IPerson $User = null)
    {
        if (!$User) {
            $User = $GLOBALS['Session']->Person;
        }

        if (!$User) {
            return [];
        }

        return static::getAllByQuery('
                SELECT `KeyUser`.*
                  FROM `%s` AS KeyUser
                  JOIN `%s` AS KeyEndpoint
                    ON KeyEndpoint.KeyID = KeyUser.KeyID
                 WHERE KeyUser.PersonID = %u
                   AND KeyEndpoint.EndpointID = %u
            ',
            [
                static::$tableName,
                KeyEndpoint::$tableName,
                $User->ID,
                $Endpoint->ID
            ]
        );
    }
}
