<?php

namespace Gatekeeper;

class PingTransaction extends Transaction
{
    public static $fields = [
        'TestPassed' => [
            'type' => 'boolean'
        ]
    ];
}