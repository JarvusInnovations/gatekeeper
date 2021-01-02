<?php

namespace Gatekeeper;

use Cache;
use Exception;
use HttpProxy;
use Emergence\EventBus;
use Emergence\Site\Client;
use Gatekeeper\Transactions\Transaction;

class ApiRequestHandler extends \RequestHandler
{
    public static $poweredByHeader = 'Jarvus Gatekeeper';
    public static $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick
    public static $defaultTimeout = 30;
    public static $defaultTimeoutConnect = 5;
    public static $passthruHeaders = [
        '/.*/'
    ];

    public static $forwardHeaders = [
        'Authorization'
    ];

    public static $responseMode = 'json'; // override RequestHandler::$responseMode

    public static $degradationTimeout = 60;

    public static function handleRequest() {

        // initialize request object
        $request = new ApiRequest(static::getPath());
        $metrics = [];


        // fire beforeApiRequest event to configure request
        $beforeEvent = EventBus::fireEvent('beforeApiRequest', 'Gatekeeper\ApiRequestHandler', [
            'request' => $request,
            'metrics' => &$metrics
        ]);


        // check that request is ready
        if (!$request->isReady()) {
            throw new Exception('ApiRequest is not ready');
        }


        // get client IP
        $clientIp = Client::getAddress();


        // execute request against internal API
        HttpProxy::relayRequest([
            'autoAppend' => false
            ,'autoQuery' => false
            ,'url' => rtrim($request->getEndpoint()->InternalEndpoint, '/') . $request->getUrl()
            ,'interface' => static::$sourceInterface
            ,'headers' => [
                "X-Forwarded-For: {$clientIp}"
            ]
            ,'passthruHeaders' => static::$passthruHeaders
            ,'forwardHeaders' => array_merge(HttpProxy::$defaultForwardHeaders, static::$forwardHeaders)
            ,'timeout' => static::$defaultTimeout
            ,'timeoutConnect' => static::$defaultTimeoutConnect
            // ,'debug' => true // uncomment to debug proxy process and see output following response
            // ,'afterResponseSync' => true // true to debug afterResponse code from browser
            ,'afterResponse' => function ($responseBody, $responseHeaders, $options, $curlHandle) use ($request, $clientIp, &$metrics, &$beforeEvent) {

                $curlInfo = curl_getinfo($curlHandle);
                list($path, $query) = explode('?', $request->getUrl());


                // initialize log record
                if (!Cache::fetch('flags/gatekeeper/skip-insert-transaction')) {
                    try {
                        $Transaction = Transaction::create([
                            'Endpoint' => $request->getEndpoint()
                            ,'Key' => $request->getKey()
                            ,'ClientIP' => ip2long($clientIp)
                            ,'Method' => $_SERVER['REQUEST_METHOD']
                            ,'Path' => $path
                            ,'Query' => $query
                            ,'ResponseTime' => $curlInfo['starttransfer_time'] * 1000
                            ,'ResponseCode' => $curlInfo['http_code']
                            ,'ResponseBytes' => $curlInfo['size_download']
                        ]);
                    } catch (\Exception $e) {
                        Cache::store('flags/gatekeeper/skip-insert-transaction', true, static::$degradationTimeout);
                        \Emergence\Logger::general_warning(
                            'Transaction Exception: {exceptionMessage}. Setting degredation flag for {seconds} seconds',
                            [
                                'exception' => $e,
                                'exceptionMessage' => $e->getMessage(),
                                'seconds' => static::$degradationTimeout
                            ]
                        );
                    }
                }


                // fire afterApiRequest
                EventBus::fireEvent('afterApiRequest', 'Gatekeeper\ApiRequestHandler', [
                    'request' => $request,
                    'metrics' => &$metrics,
                    'beforeEvent' => &$beforeEvent,
                    'curlHandle' => $curlHandle,
                    'curlInfo' => $curlInfo,
                    'curlError' => curl_errno($curlHandle),
                    'Transaction' => $Transaction,
                    'responseCode' => $curlInfo['http_code'],
                    'responseHeaders' => &$responseHeaders,
                    'responseBody' => &$responseBody
                ]);
            }
        ]);
    }

    public static function throwRateError($retryAfter = null, $error = 'Rate limit exceeded')
    {
        header('HTTP/1.1 429 Too Many Requests');

        if ($retryAfter) {
            header("Retry-After: $retryAfter");
            $error .= ", retry after $retryAfter seconds";
        }

        \JSON::error($error);
    }
}