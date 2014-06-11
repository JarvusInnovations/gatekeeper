<?php

Gatekeeper::authorizeTestApiAccess();

$code = !empty($_REQUEST['statusCode']) && is_numeric($_REQUEST['statusCode']) ? $_REQUEST['statusCode'] : 200;
$message = !empty($_REQUEST['statusMessage']) && preg_match('/^[a-zA-Z0-9 \\-]+$/', $_REQUEST['statusMessage']) ? $_REQUEST['statusMessage'] : 'OK';

header("HTTP/1.1 $code $message");

JSON::respond(array(
    'success' => $code < 300,
    'code' => $code,
    'message' => $message
));