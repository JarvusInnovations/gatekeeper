<?php

class ApiRequestHandler extends RequestHandler
{
    public static $poweredByHeader = 'Jarvus Gatekeeper';
    public static $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick
    public static $defaultTimeout = 30;
    public static $defaultTimeoutConnect = 5;
    public static $passthruHeaders = array(
        '/^HTTP\//'
        ,'/^Content-Type:/'
        ,'/^Date:/'
        ,'/^Set-Cookie:/'
        ,'/^Location:/'
        ,'/^ETag:/'
        ,'/^Last-Modified:/'
    );

    public static $responseMode = 'json'; // override RequestHandler::$responseMode

    public static function handleRequest() {
        $now = time();

        // set Gatekeeper headers
        if (static::$poweredByHeader) {
            header('X-Powered-By: '.static::$poweredByHeader);
        }


        // get active bans
        //    - cache entire list of currently active bans in an array, maybe this will prove less efficient when the list grows
        $activeBans = Ban::getActiveBansTable();


        // check if IP is banned
        if (in_array($_SERVER['REMOTE_ADDR'], $activeBans['ips'])) {
            header('HTTP/1.1 403 Forbidden');
            JSON::error('Your IP address is currently banned from using this service');
        }


        // read endpoint handle from path
        if (!$endpointHandle = static::shiftPath()) {
            return static::throwInvalidRequestError('Endpoint handle required');
        }

        // read endpoint version from path and get endpoint
        if (
            ($endpointVersion = static::peekPath()) &&
            $endpointVersion[0] == 'v' &&
            ($endpointVersion = substr($endpointVersion, 1)) &&
            preg_match(Endpoint::$versionPattern, $endpointVersion) &&
            ($Endpoint = Endpoint::getByHandleAndVersion($endpointHandle, $endpointVersion))
        ) {
            static::shiftPath();
        } else {
            $Endpoint = Endpoint::getByHandleAndVersion($endpointHandle);
        }


        // ensure endpoint was found
        if (!$Endpoint) {
            return static::throwNotFoundError('Requested endpoint+version not found, be sure to specify a version if this endpoint requires it');
        }


        // check if endpoint is deprecated
        if ($Endpoint->DeprecationDate && $Endpoint->DeprecationDate < $now) {
            header('HTTP/1.1 410 Gone');
            JSON::error('This endpoint+version has been deprecated');
        }


        // verify key if required
        if ($Endpoint->KeyRequired) {
            if (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/^Gatekeeper-Key\s+(\w+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $keyMatches)) {
                $keyString = $keyMatches[1];
            } elseif (!empty($_REQUEST['gatekeeperKey'])) {
                $keyString = $_REQUEST['gatekeeperKey'];
            }

            if (!$keyString) {
                return static::throwKeyError($Endpoint, 'gatekeeper key required for this endpoint');
            } elseif(!$Key = Key::getByKey($keyString)) {
                return static::throwKeyError($Endpoint, 'gatekeeper key invalid');
            } elseif($Key->ExpirationDate && $Key->ExpirationDate < $now) {
                return static::throwKeyError($Endpoint, 'gatekeeper key valid but expired');
            } elseif(!$Key->canAccessEndpoint($Endpoint)) {
                return static::throwKeyError($Endpoint, 'gatekeeper key valid but does not permit this endpoint');
            }

            // check if key is banned
            if (in_array($Key->ID, $activeBans['keys'])) {
                header('HTTP/1.1 403 Forbidden');
                JSON::error('Your API key is currently banned from using this service');
            }
        }


        // build identifier string for current user
        $userKey = $Key ? "keys/$Key->ID" : "ips/$_SERVER[REMOTE_ADDR]";


        // drip into endpoint+user bucket first so that abusive users can't pollute the global bucket
        if ($Endpoint->UserRatePeriod && $Endpoint->UserRateCount) {
            $bucket = HitBuckets::drip("endpoints/$Endpoint->ID/$userKey", function() use ($Endpoint) {
                return array('seconds' => $Endpoint->UserRatePeriod, 'count' => $Endpoint->UserRateCount);
            });

            if ($bucket['hits'] < 0) {
                return static::throwRateError($bucket['seconds'], 'Your rate limit for this endpoint has been exceeded');
            }
        }


        // TODO: implement a per-user throttle that applies across all endpoints? Might not be useful...


        // configure and execute internal API call
        $urlPrefix = rtrim($Endpoint->InternalEndpoint, '/');
        $path = '/' . implode('/', static::getPath());
        $url = rtrim($path . '?' . $_SERVER['QUERY_STRING'], '?&');


        // apply rewrite rules
        $url = $Endpoint->applyRewrites($url);


        // normalize URL after rewrite
        $url = rtrim($url, '?&');

        if (substr_count($url, '?') > 1) {
            $url[strrpos($url, '?')] = '&';
        }


        // TODO: migrate caching implementation to HttpProxy and include headers in cache
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && $Endpoint->CachingEnabled) {
            $cacheKey = "response:$Endpoint->ID:$url";

            if ($cachedResponse = Cache::fetch($cacheKey)) {
                if ($cachedResponse['expires'] < $now) {
                    Cache::delete($cacheKey);
                    $cachedResponse = false;
                } else {
                    foreach ($cachedResponse['headers'] AS $header) {
                        header($header);
                    }
                    print($cachedResponse['body']);
                    exit();
                }
            }
        }


        // drip into endpoint bucket
        if ($Endpoint->GlobalRatePeriod && $Endpoint->GlobalRateCount) {
            $bucket = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
                return array('seconds' => $Endpoint->GlobalRatePeriod, 'count' => $Endpoint->GlobalRateCount);
            });

            if ($bucket['hits'] < (1 - $Endpoint->AlertNearMaxRequests) * $Endpoint->GlobalRateCount) {
                static::sendAdminNotification($Endpoint, 'endpointRateLimitNear', array(
                    'bucket' => $bucket
                ), "endpoints/$Endpoint->ID/rate-warning-sent", $bucket['seconds']);
            }

            if ($bucket['hits'] < 0) {
                static::sendAdminNotification($Endpoint, 'endpointRateLimitReached', array(
                    'bucket' => $bucket
                ), "endpoints/$Endpoint->ID/rate-notification-sent", $bucket['seconds']);

                return static::throwRateError($bucket['seconds'], 'The global rate limit for this endpoint has been exceeded');
            }
        }


        // execute request against internal API
        HttpProxy::relayRequest(array(
            'autoAppend' => false
            ,'autoQuery' => false
            ,'url' => $urlPrefix . $url
            ,'interface' => static::$sourceInterface
            ,'passthruHeaders' => static::$passthruHeaders
            ,'timeout' => static::$defaultTimeout
            ,'timeoutConnect' => static::$defaultTimeoutConnect
            ,'afterResponse' => function($responseBody, $responseHeaders, $options, $ch) use ($Endpoint, $Key, $url, $cacheKey, $now) {
                $curlInfo = curl_getinfo($ch);
                list($path, $query) = explode('?', $url);

                // log request to database
                $LoggedRequest = LoggedRequest::create(array(
                    'Endpoint' => $Endpoint
                    ,'Key' => $Key
                    ,'ClientIP' => ip2long($_SERVER['REMOTE_ADDR'])
                    ,'Method' => $_SERVER['REQUEST_METHOD']
                    ,'Path' => $path
                    ,'Query' => $query
                    ,'ResponseTime' => $curlInfo['starttransfer_time'] * 1000
                    ,'ResponseCode' => $curlInfo['http_code']
                    ,'ResponseBytes' => $curlInfo['size_download']
                ), true);

                // cache request
                if (!empty($responseHeaders['Expires']) && $cacheKey) {
                    $expires = strtotime($responseHeaders['Expires']);

                    if ($expires > $now) {
                        $cachableHeaders = array();

                        foreach ($responseHeaders AS $headerKey => $headerValue) {
                            $header = "$headerKey: $headerValue";
                            foreach (static::$passthruHeaders AS $pattern) {
                                if (preg_match($pattern, $header)) {
                                    $cachableHeaders[] = $header;
                                    break;
                                }
                            }
                        }

                        Cache::store($cacheKey, array(
                            'path' => $path
                            ,'query' => $query
                            ,'expires' => $expires
                            ,'headers' => $cachableHeaders
                            ,'body' => $responseBody
                        ), $expires - $now);
                    }
                }

                // send error alert
                if ($curlInfo['http_code'] >= 500 AND $Endpoint->AlertOnError) {
                    static::sendAdminNotification($Endpoint, 'endpointError', array(
                        'LoggedRequest' => $LoggedRequest
                        ,'responseHeaders' => $responseHeaders
                        ,'responseBody' => $responseBody
                    ), "endpoints/$Endpoint->ID/error-notification-sent");
                }

                // log SQL queries to file for auditing
                //file_put_contents('/tmp/gatekeeper-last-queries', var_export(Debug::$log, true));
            }
        ));
    }

    protected static function throwKeyError(Endpoint $Endpoint, $error)
    {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Gatekeeper-Key endpoint="'.$Endpoint->Handle.'"');
        JSON::error($error);
    }

    protected static function throwRateError($retryAfter = null, $error = 'Rate limit exceeded')
    {
        header('HTTP/1.1 429 Too Many Requests');

        if ($retryAfter) {
            header("Retry-After: $retryAfter");
            $error .= ", retry after $retryAfter seconds";
        }

        JSON::error($error);
    }

    protected static function sendAdminNotification(Endpoint $Endpoint, $templateName, $data, $throttleKey = null, $throttleTime = 60)
    {
        // send notification email to staff
        if (!$throttleKey || !Cache::fetch($throttleKey)) {
            $data['Endpoint'] = $Endpoint;

            if ($emailTo = $Endpoint->getNotificationEmailRecipient()) {
                \Emergence\Mailer\Mailer::sendFromTemplate($emailTo, $templateName, $data);
            }

            if ($throttleKey) {
                Cache::store($throttleKey, true, $throttleTime);
            }
        }
    }
}