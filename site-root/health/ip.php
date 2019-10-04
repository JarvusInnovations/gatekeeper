<?php

use Gatekeeper\ApiRequestHandler;

$GLOBALS['Session']->requireAccountLevel('Staff');

$response = HttpProxy::relayRequest([
    'url' => 'http://ifconfig.me/ip',
    'autoAppend' => false,
    'autoQuery' => false,
    'interface' => ApiRequestHandler::$sourceInterface,
    'returnResponse' => true
]);

if ($response['info']['http_code'] == 200) {
    header('Content-Type: text/plain');
    print($response['body']);
} else {
    http_response_code(400);
    die("request failed");
}
