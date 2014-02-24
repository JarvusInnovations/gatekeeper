<?php

class ApiRequestHandler extends RequestHandler
{
    static public $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick
    
    static public $responseMode = 'json'; // override RequestHandler::$responseMode

	static public function handleRequest() {

		// check required parameters
		if (!$endpointHandle = static::shiftPath()) {
			return static::throwInvalidRequestError('Endpoint handle required');
		}

		if (!($endpointVersion = static::shiftPath()) || !preg_match('/^v.+$/', $endpointVersion)) {
			return static::throwInvalidRequestError('Endpoint version required');
		}


		// get active bans
		//	- cache entire list of currently active bans in an array, maybe this will prove less efficient when the list grows
		$activeBans = Ban::getActiveBansTable();


		// check if IP is banned
		if (in_array($_SERVER['REMOTE_ADDR'], $activeBans['ips'])) {
			header('HTTP/1.1 403 Forbidden');
			JSON::error('Your IP address is currently banned from using this service');
		}


		// get endpoint record and check version
		if (!$Endpoint = Endpoint::getByWhere(array('Handle' => $endpointHandle, 'Version' => substr($endpointVersion, 1)))) {
			return static::throwNotFoundError('Requested endpoint not found');
		}

		$endpointVersion = substr($endpointVersion, 1);

		if ($endpointVersion != $Endpoint->Version) {
			return static::throwNotFoundError('Requested endpoint version not available');
		}


		// verify key if required
		if ($Endpoint->KeyRequired) {
			if (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/^GateKeeper-Key\s+(\w+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $keyMatches)) {
				$keyString = $keyMatches[1];
			} elseif (!empty($_REQUEST['gatekeeperKey'])) {
				$keyString = $_REQUEST['gatekeeperKey'];
			}

			if (!$keyString) {
				return static::throwKeyError($Endpoint, 'gatekeeper key required for this endpoint');
			} elseif(!$Key = Key::getByKey($keyString)) {
				return static::throwKeyError($Endpoint, 'gatekeeper key invalid');
			} elseif($Key->ExpirationDate && $Key->ExpirationDate < time()) {
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

			if ($bucket['hits'] <= 0) {
				return static::throwRateError($bucket['seconds'], 'Your rate limit for this endpoint has been exceeded');
			}
		}


		// TODO: implement a per-user throttle that applies across all endpoints? Might not be useful...


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

			if ($bucket['hits'] <= 0) {
                static::sendAdminNotification($Endpoint, 'endpointRateLimitReached', array(
                    'bucket' => $bucket
            	), "endpoints/$Endpoint->ID/rate-notification-sent", $bucket['seconds']);
                
				return static::throwRateError($bucket['seconds'], 'The global rate limit for this endpoint has been exceeded');
			}
		}


		// configure and execute internal API call
		$urlPrefix = rtrim($Endpoint->InternalEndpoint, '/');
		$path = '/' . implode('/', static::getPath());
        $url = $path . '?' . $_SERVER['QUERY_STRING'];
        
        
        // apply rewrite rules
        foreach ($Endpoint->Rewrites AS $Rewrite) {
            if (preg_match($Rewrite->Pattern, $url)) {
                $url = preg_replace($Rewrite->Pattern, $Rewrite->Replace, $url);
                
                if ($Rewrite->Last) {
                    break;
                }
            }
        }


        // normalize URL after rewrite
        $url = rtrim($url, '?&');

        if (substr_count($url, '?') > 1) {
            $url[strrpos($url, '?')] = '&';
        }


        // TODO: migrate caching implementation to HttpProxy and include headers in cache
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && $Endpoint>CachingEnabled) {
            $cacheKey = "response:$Endpoint->ID:$path?$_SERVER[QUERY_STRING]";
            
            if ($cachedResponse = Cache::fetch($cacheKey)) {
                if ($cachedResponse['expires'] < time()) {
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

		HttpProxy::relayRequest(array(
			'autoAppend' => false
            ,'autoQuery' => false
			,'url' => $urlPrefix . $url
			,'interface' => static::$sourceInterface
            ,'afterResponseSync' => true
			,'afterResponse' => function($responseBody, $responseHeaders, $options, $ch) use ($Endpoint, $Key, $url, $cacheKey) {
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
                    $now = time();

                    if ($expires > $now) {
                        $cachableHeaders = array();
                        
                        foreach ($responseHeaders AS $headerKey => $headerValue) {
                            $header = "$headerKey: $headerValue";
                            foreach (HttpProxy::$defaultPassthruHeaders AS $pattern) {
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
			}
		));
	}

	static protected function throwKeyError(Endpoint $Endpoint, $error)
	{
		header('HTTP/1.0 401 Unauthorized');
		header('WWW-Authenticate: GateKeeper-Key endpoint="'.$Endpoint->Handle.'"');
		JSON::error($error);
	}

	static protected function throwRateError($retryAfter = null, $error = 'Rate limit exceeded')
	{
		header('HTTP/1.1 429 Too Many Requests');

		if ($retryAfter) {
			header("Retry-After: $retryAfter");
			$error .= ", retry after $retryAfter seconds";
		}

		JSON::error($error);
	}
    
    static protected function sendAdminNotification(Endpoint $Endpoint, $templateName, $data, $throttleKey = null, $throttleTime = 60)
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