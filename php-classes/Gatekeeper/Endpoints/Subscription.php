<?php

namespace Gatekeeper\Endpoints;

use Emergence\People\Person;

class Subscription extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'endpoint_subscriptions';
    public static $singularNoun = 'endpoint subscription';
    public static $pluralNoun = 'endpoint subscriptions';

    public static $fields = [
        'EndpointID' => 'uint',
        'PersonID' => 'uint'
    ];

    public static $relationships = [
        'Endpoint' => [
            'type' => 'one-one',
            'class' => Endpoint::class
        ],
        'Person' => [
            'type' => 'one-one',
            'class' => Person::class
        ]
    ];

    public static $validators = [
        'Endpoint' => 'require-relationship',
        'Person' => 'require-relationship'
    ];

    public static $indexes = [
        'EndpointPerson' => [
            'fields' => ['EndpointID', 'PersonID'],
            'unique' => true
        ]
    ];
}
