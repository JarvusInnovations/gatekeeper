<?php

namespace Gatekeeper\Transactions;

use Gatekeeper\Endpoints\Endpoint;
use Gatekeeper\Keys\Key;

class Transaction extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'transactions';
    public static $singularNoun = 'transaction';
    public static $pluralNoun = 'transactions';
    public static $subClasses = [__CLASS__, PingTransaction::class];

    public static $fields = [
        'CreatorID' => null,
        'EndpointID' => [
            'type' => 'uint',
            'index' => true
        ],
        'KeyID' => [
            'type' => 'uint',
            'notnull' => false,
            'index' => true
        ],
        'ClientIP' => 'uint',
        'Method',
        'Path',
        'Query' => 'clob',
        'ResponseTime' => [
            'type' => 'mediumint',
            'unsigned' => true
        ],
        'ResponseCode' => [
            'type' => 'smallint',
            'unsigned' => true
        ],
        'ResponseBytes' => [
            'type' => 'mediumint',
            'unsigned' => true
        ]
    ];

    public static $relationships = [
        'Endpoint' => [
            'type' => 'one-one',
            'class' => Endpoint::class
        ],
        'Key' => [
            'type' => 'one-one',
            'class' => Key::class
        ]
    ];
}
