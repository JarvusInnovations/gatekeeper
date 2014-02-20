<?php

class ApiRequestHandler extends RequestHandler
{
    static public $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick

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
			} elseif(!$Key->AllEndpoints) {
				// TODO: check explicit endpoints list
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


		// drip into endpoint+user bucket first so that abusive users can't polute the global bucket
		if ($Endpoint->UserRatePeriod && $Endpoint->UserRateCount) {
			$retryAfter = HitBuckets::drip("endpoints/$Endpoint->ID/$userKey", function() use ($Endpoint) {
				return array('seconds' => $Endpoint->UserRatePeriod, 'count' => $Endpoint->UserRateCount);
			});

			if ($retryAfter) {
				return static::throwRateError($retryAfter, 'Your rate limit for this endpoint has been exceeded');
			}
		}


		// TODO: implement a per-user throttle that applies across all endpoints? Might not be useful...


		// drip into endpoint bucket
		if ($Endpoint->GlobalRatePeriod && $Endpoint->GlobalRateCount) {
			$retryAfter = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
				return array('seconds' => $Endpoint->GlobalRatePeriod, 'count' => $Endpoint->GlobalRateCount);
			});

			if ($retryAfter) {
				return static::throwRateError($retryAfter, 'The global rate limit for this endpoint has been exceeded');
			}
		}


		// configure and execute internal API call
		$urlPrefix = rtrim($Endpoint->InternalEndpoint, '/');
		$path = '/' . implode('/', static::getPath());
        
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
			,'url' => $urlPrefix . $path
			,'interface' => static::$sourceInterface
            ,'afterResponseSync' => true
			,'afterResponse' => function($responseBody, $responseHeaders, $options, $ch) use ($Endpoint, $Key, $path, $cacheKey) {
				$curlInfo = curl_getinfo($ch);

				// log request to database
				LoggedRequest::create(array(
					'Endpoint' => $Endpoint
					,'Key' => $Key
					,'ClientIP' => ip2long($_SERVER['REMOTE_ADDR'])
					,'Method' => $_SERVER['REQUEST_METHOD']
					,'Path' => $path
					,'Query' => $_SERVER['QUERY_STRING']
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
                            'headers' => $cachableHeaders
                            ,'expires' => $expires
                            ,'body' => $responseBody
                        ), $expires - $now);
                    }
                }
			}
		));
	}

	static public function throwKeyError(Endpoint $Endpoint, $error)
	{
		header('HTTP/1.0 401 Unauthorized');
		header('WWW-Authenticate: GateKeeper-Key endpoint="'.$Endpoint->Handle.'"');
		JSON::error($error);
	}

	static public function throwRateError($retryAfter = null, $error = 'Rate limit exceeded')
	{
		header('HTTP/1.1 429 Too Many Requests');

		if ($retryAfter) {
			header("Retry-After: $retryAfter");
			$error .= ", retry after $retryAfter seconds";
		}

		JSON::error($error);
	}
}