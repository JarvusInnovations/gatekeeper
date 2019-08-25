<?php

namespace Gatekeeper;

use DB;
use HttpProxy;
use Gatekeeper\Alerts\TestFailed;
use Gatekeeper\Endpoints\Endpoint;
use Gatekeeper\Transactions\PingTransaction;


// find all endpoints that are overdue or near due for a ping
$endpoints = Endpoint::getAllByQuery(
    'SELECT Endpoint.*'
    .' FROM `%s` Endpoint'
    .' WHERE'
    .'  Endpoint.PingFrequency IS NOT NULL AND'
    .'  IFNULL(('
    .'    SELECT Created FROM `%s` Transaction WHERE EndpointID = Endpoint.ID AND Class = "%s" ORDER BY ID DESC LIMIT 1'
    .'  ), 0) < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL (Endpoint.PingFrequency * 0.7) MINUTE)'
    ,[
        Endpoint::$tableName,
        PingTransaction::$tableName,
        DB::escape(PingTransaction::class)
    ]
);


// loop through all endpoints to execute, test, and record the ping URI
foreach ($endpoints AS $Endpoint) {
    printf('Testing endpoint %s...', $Endpoint->getTitle());

    // execute and capture request
    // TODO: use curl_multi_exec somehow?
    $response = HttpProxy::relayRequest([
        'autoAppend' => false,
        'autoQuery' => false,
        'url' => rtrim($Endpoint->InternalEndpoint, '/') . '/' . ltrim($Endpoint->PingURI, '/'),
        'interface' => ApiRequestHandler::$sourceInterface,
        'timeout' => 15,
        'timeoutConnect' => 5,
        'returnResponse' => true
    ]);


    // evaluate success
    $testPassed =
        $response['info']['http_code'] == 200 &&
        (
            !$Endpoint->PingTestPattern ||
            preg_match($Endpoint->PingTestPattern, $response['body'])
        );


    // record transaction
    list($path, $query) = array_pad(explode('?', $Endpoint->PingURI, 2), 2, null);

    $Transaction = PingTransaction::create([
        'Endpoint' => $Endpoint,
        'ClientIP' => ip2long($response['info']['local_ip']),
        'Method' => 'GET',
        'Path' => $path,
        'Query' => $query,
        'ResponseTime' => $response['info']['starttransfer_time'] * 1000,
        'ResponseCode' => $response['info']['http_code'],
        'ResponseBytes' => $response['info']['size_download'],
        'TestPassed' => $testPassed
    ], true);


    // open alert if necessary, or close any existing one
    if (!$testPassed) {
        TestFailed::open($Endpoint, [
            'transactionId' => $Transaction->ID,
            'request' => [
                'uri' => $Endpoint->PingURI
            ],
            'response' => [
                'status' => $Transaction->ResponseCode,
                'headers' => $response['headers'],
                'body' => $response['body'],
                'bytes' => $Transaction->ResponseBytes,
                'time' => $Transaction->ResponseTime
            ]
        ]);
    } else {
        $OpenAlert = TestFailed::getByWhere([
            'Class' => TestFailed::class,
            'EndpointID' => $Endpoint->ID,
            'Status' => 'open'
        ]);

        if ($OpenAlert) {
            $OpenAlert->Status = 'closed';
            $OpenAlert->save();
        }
    }

    printf("%s\n", $testPassed ? 'passed' : 'failed');
}