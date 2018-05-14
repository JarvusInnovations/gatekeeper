<?php

namespace Gatekeeper\Transactions;

class PingTransaction extends Transaction
{
    public static $fields = [
        'TestPassed' => [
            'type' => 'boolean'
        ]
    ];
}