<?php

namespace Gatekeeper;

use Cache;
use Exception;
use HttpProxy;
use Emergence\EventBus;
use Emergence\Mailer\Mailer;

class ApiRequestHandler extends \RequestHandler
{
    public static $poweredByHeader = 'Jarvus Gatekeeper';
    public static $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick
    public static $defaultTimeout = 30;
    public static $defaultTimeoutConnect = 5;
    public static $passthruHeaders = [
        '/^HTTP\//'
        ,'/^Content-Type:/i'
        ,'/^Date:/i'
        ,'/^Set-Cookie:/i'
        ,'/^Location:/i'
        ,'/^ETag:/i'
        ,'/^Last-Modified:/i'
        ,'/^Cache-Control:/i'
        ,'/^Pragma:/i'
        ,'/^Expires:/i'
    ];

    public static $responseMode = 'json'; // override RequestHandler::$responseMode

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


        // execute request against internal API
        HttpProxy::relayRequest([
            'autoAppend' => false
            ,'autoQuery' => false
            ,'url' => rtrim($request->getEndpoint()->InternalEndpoint, '/') . $request->getUrl()
            ,'interface' => static::$sourceInterface
            ,'passthruHeaders' => static::$passthruHeaders
            ,'timeout' => static::$defaultTimeout
            ,'timeoutConnect' => static::$defaultTimeoutConnect
            ,'afterResponseSync' => true // uncomment to debug afterResponse code from browser
            ,'afterResponse' => function($responseBody, $responseHeaders, $options, $curlHandle) use ($request, &$metrics, &$beforeEvent) {

                $curlInfo = curl_getinfo($curlHandle);
                list($path, $query) = explode('?', $request->getUrl());


                // initialize log record
                $Transaction = Transaction::create([
                    'Endpoint' => $request->getEndpoint()
                    ,'Key' => $request->getKey()
                    ,'ClientIP' => ip2long($_SERVER['REMOTE_ADDR'])
                    ,'Method' => $_SERVER['REQUEST_METHOD']
                    ,'Path' => $path
                    ,'Query' => $query
                    ,'ResponseTime' => $curlInfo['starttransfer_time'] * 1000
                    ,'ResponseCode' => $curlInfo['http_code']
                    ,'ResponseBytes' => $curlInfo['size_download']
                ]);


                // fire afterApiRequest
                EventBus::fireEvent('afterApiRequest', 'Gatekeeper\ApiRequestHandler', [
                    'request' => $request,
                    'metrics' => &$metrics,
                    'beforeEvent' => &$beforeEvent,
                    'curlHandle' => $curlHandle,
                    'curlInfo' => $curlInfo,
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

        JSON::error($error);
    }

    public static function sendAdminNotification(Endpoint $Endpoint, $templateName, $data, $throttleKey = null, $throttleTime = 1800)
    {
        // send notification email to staff
        if (!$throttleKey || !Cache::fetch($throttleKey)) {
            $data['Endpoint'] = $Endpoint;

            if ($emailTo = $Endpoint->getNotificationEmailRecipient()) {
                Mailer::sendFromTemplate($emailTo, $templateName, $data);
            }

            if ($throttleKey) {
                Cache::store($throttleKey, true, $throttleTime);
            }
        }
    }
}