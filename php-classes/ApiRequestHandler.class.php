<?php

class ApiRequestHandler extends RequestHandler
{
	static public function handleRequest() {
		if (!$endpointHandle = static::shiftPath()) {
			return static::throwInvalidRequestError('Endpoint handle required');
		}
		
		if (!$Endpoint = Endpoint::getByHandle($endpointHandle)) {
			return static::throwNotFoundError('Requested endpoint not found');
		}
		
		if (!($endpointVersion = static::shiftPath()) || !preg_match('/^v\d+$/', $endpointVersion)) {
			return static::throwInvalidRequestError('Endpoint version required');
		}
		
		$endpointVersion = substr($endpointVersion, 1);
		
		if ($endpointVersion != $Endpoint->Version) {
			return static::throwNotFoundError('Requested endpoint version not available');
		}
		
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
		
		$urlPrefix = rtrim($Endpoint->InternalEndpoint, '/');
		$path = '/' . implode('/', static::getPath());
		
		HttpProxy::relayRequest(array(
			'autoAppend' => false
			,'url' => $urlPrefix . $path
			,'afterResponse' => function($responseBody, $options, $ch) use ($Endpoint, $Key, $path) {
				$curlInfo = curl_getinfo($ch);
				
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
				
				file_put_contents($_SERVER['SITE_ROOT'].'/site-data/last_request.log', "cURL info:\n".print_r(Debug::$log, true)."\n\nBody:\n$responseBody\n\n");
			}
		));
	}
	
	static public function throwKeyError(Endpoint $Endpoint, $error)
	{
		header('HTTP/1.0 401 Unauthorized');
		header('WWW-Authenticate: GateKeeper-Key endpoint="'.$Endpoint->Handle.'"');
		JSON::error($error);
	}
}