<?php

return [
    'title' => 'Skip Transaction Inserts (Degradation Mode)',
    'description' => 'Enable or disable degredation mode which skips saving transactions to the DB. This functionality aids with the speed of requests with sites while under failure or high load',
    'icon' => 'power-off',
    'handler' => function () {
        $flag = 'flags/gatekeeper/skip-insert-transaction';
        $ttl = $_REQUEST['ttl'] ?: Gatekeeper\ApiRequestHandler::$degradationTimeout;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_REQUEST['status'] == 'enable') {
                Cache::store($flag, true, $ttl);
            } else {
                Cache::delete($flag);
            }
        }

        return static::respond('skip-insert-transactions', [
            'ttl' => $ttl,
            'enabled' => !!Cache::fetch($flag)
        ]);
    }
];