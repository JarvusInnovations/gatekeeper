<?php

class ApiRequestHandler extends RequestHandler
{
	static public $sourceInterface = null; // string=hostname or IP, null=http hostname, false=let cURL pick
	
	static public function handleRequest() {
		
		// check required parameters
		if (!$endpointHandle = static::shiftPath()) {
			return static::throwInvalidRequestError('Endpoint handle required');
		}
		
		if (!($endpointVersion = static::shiftPath()) || !preg_match('/^v\d+$/', $endpointVersion)) {
			return static::throwInvalidRequestError('Endpoint version required');
		}
		
		
		// get endpoint record and check version
		// TODO: support multiple versions
		if (!$Endpoint = Endpoint::getByHandle($endpointHandle)) {
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
				return static::throwKeyError($Endpoint, 'gatekeeper key valid but does not permit this endpoint');
			}
		}
		
		
		// build identifier string for current user
		$userKey = $Key ? "keys/$Key-ID" : "ips/$_SERVER[REMOTE_ADDR]";
		
		
		// drip into endpoint+user bucket first so that abusive users can't polute the global bucket
		$retryAfter = HitBuckets::drip("endpoints/$Endpoint->ID/$userKey", function() use ($Endpoint) {
			return array('seconds' => $Endpoint->UserRatePeriod, 'count' => $Endpoint->UserRateCount);
		});
		
		if ($retryAfter) {
			return static::throwRateError($retryAfter, 'Your rate limit for this endpoint has been exceeded');
		}
		
		
		// TODO: implement a per-user throttle that applies across all endpoints? Might not be useful...
		
		
		// drip into endpoint bucket
		$retryAfter = HitBuckets::drip("endpoints/$Endpoint->ID", function() use ($Endpoint) {
			return array('seconds' => $Endpoint->GlobalRatePeriod, 'count' => $Endpoint->GlobalRateCount);
		});
		
		if ($retryAfter) {
			return static::throwRateError($retryAfter, 'The global rate limit for this endpoint has been exceeded');
		}


		// configure and execute internal API call
		$urlPrefix = rtrim($Endpoint->InternalEndpoint, '/');
		$path = '/' . implode('/', static::getPath());
		
		HttpProxy::relayRequest(array(
			'autoAppend' => false
			,'url' => $urlPrefix . $path
			,'interface' => static::$sourceInterface
			,'afterResponse' => function($responseBody, $options, $ch) use ($Endpoint, $Key, $path) {
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