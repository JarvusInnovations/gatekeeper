<?php

namespace Gatekeeper;

use ActiveRecord;

class MetricSample extends ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'metric_samples';
    public static $singularNoun = 'metric sample';
    public static $pluralNoun = 'metric samples';

    public static $fields = [
        'Class' => null,
        'Created' => null,
        'CreatorID' => null,
        'Timestamp' => 'timestamp',
        'Key' => 'string',
        'Value' => 'uint'
    ];

    public static $indexes = [
        'TimestampKey' => [
            'fields' => ['Timestamp', 'Key'],
            'unique' => true
        ]
    ];
}
