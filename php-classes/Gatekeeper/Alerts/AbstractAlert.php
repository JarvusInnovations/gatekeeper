<?php

namespace Gatekeeper\Alerts;

use Gatekeeper\Endpoint;

abstract class AbstractAlert extends \ActiveRecord
{
    public static $displayType;
    public static $notificationTemplate = 'default';

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
            'class' => Endpoint::class
        ]
    ];

    public function getDisplayType()
    {
        return static::$displayType ? static::$displayType : $this->Class;
    }

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

    public static function open(Endpoint $Endpoint = null, array $metadata = null)
    {
        // try to get existing open alert of same class+endpoint
        $conditions = [
            'Class' => get_called_class(),
            'Status' => 'open'
        ];

        if ($Endpoint) {
            $conditions['EndpointID'] = $Endpoint->ID;
        } else {
            $conditions[] = 'EndpointID IS NULL';
        }

        if ($Alert = static::getByWhere($conditions)) {
            $Alert->Repetitions++;
            $Alert->save();
            return $Alert;
        }


        // create new alert
        return static::create([
            'Endpoint' => $Endpoint,
            'Metadata' => $metadata
        ], true);
    }
}