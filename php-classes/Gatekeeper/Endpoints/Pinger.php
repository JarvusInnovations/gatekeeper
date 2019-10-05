<?php

namespace Gatekeeper\Endpoints;

use Site;
use Cache;
use HttpProxy;

use Gatekeeper\ApiRequestHandler;
use Gatekeeper\Alerts\TestFailed;
use Gatekeeper\Transactions\PingTransaction;


class Pinger
{
    public static function pingOverdueEndpoints($verbose = false)
    {
        $endpoints = Endpoint::getAllByWhere('PingFrequency IS NOT NULL');

        foreach ($endpoints as $Endpoint) {
            $lastPing = Cache::fetch("endpoints/{$Endpoint->ID}/last-ping");

            if (
                $lastPing === false
                || $Endpoint->PingFrequency*60 < time()-$lastPing['time']
            ) {
                static::pingEndpoint($Endpoint, $verbose);
            }
        }
    }

    public static function pingEndpoint(Endpoint $Endpoint, $verbose = false)
    {
        $verbose && printf('Testing endpoint: /%s...', $Endpoint->Path) && flush();


        // execute and capture request
        // TODO: use curl_multi_exec somehow?
        $response = HttpProxy::relayRequest([
            'autoAppend' => false,
            'autoQuery' => false,
            'method' => 'GET',
            'url' => rtrim($Endpoint->InternalEndpoint, '/') . '/' . ltrim($Endpoint->PingURI, '/'),
            'interface' => ApiRequestHandler::$sourceInterface,
            'timeout' => 15,
            'timeoutConnect' => 5,
            'returnResponse' => true,
            'forwardHeaders' => [],
            'headers' => [
                'Accept: */*',
                'Accept-Language: *',
                'User-Agent: ' . (ApiRequestHandler::$poweredByHeader ?: Site::$title)
            ]
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


        $verbose && printf("%s\n", $testPassed ? 'passed' : 'failed') && flush();


        // cache result and timestamp
        Cache::store("endpoints/{$Endpoint->ID}/last-ping", [
            'time' => time(),
            'testPassed' => $testPassed
        ], ($Endpoint->PingFrequency+5)*60);

        return $testPassed;
    }
}
