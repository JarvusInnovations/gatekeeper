<?php

namespace Gatekeeper\Alerts;

abstract class AbstractAlert extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'alerts';
    public static $singularNoun = 'alert';
    public static $pluralNoun = 'alerts';
    public static $collectionRoute = '/alerts';
    
    public static $subClasses = [
        BandwidthLimitApproached::class,
        BandwidthLimitExceeded::class,
        RateLimitApproached::class,
        RateLimitExceeded::class,
        ResponseTimeLimitExceeded::class,
        TestFailed::class,
        TransactionFailed::class
    ];

    public static $fields = [
        'Status' => [
            'type' => 'enum',
            'values' => ['open', 'closed', 'dismissed'],
            'default' => 'open'
        ],
        'Opened' => [
            'type' => 'timestamp'
        ],
        'Closed' => [
            'type' => 'timestamp',
            'notnull' => false
        ],
        'Repetitions' => [
            'type' => 'uint',
            'default' => 0
        ],
        'AcknowledgerID' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'EndpointID' => [
            'type' => 'uint',
            'notnull' => false
        ],
        'Metadata' => [
            'type' => 'json',
            'notnull' => false
        ]
    ];

    public static $relationships = [
        'Acknowledger' => [
            'type' => 'one-one',
            'class' => \Emergence\People\Person::class
        ],
        'Endpoint' => [
            'type' => 'one-one',
            'class' => \Gatekeeper\Endpoint::class
        ]
    ];

    public function save($deep = true)
    {
        if (!$this->Opened) {
            $this->Opened = time();
        }

        if ($this->isFieldDirty('Status') && $this->Status == 'closed' && !$this->Closed) {
            $this->Closed = time();
        }

        parent::save($deep);
    }
}