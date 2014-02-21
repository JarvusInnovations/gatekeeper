<?php

$code = !empty($_REQUEST['statusCode']) ? $_REQUEST['statusCode'] : 200;
$message = !empty($_REQUEST['statusMessage']) ? $_REQUEST['statusMessage'] : 'OK';

header("HTTP/1.1 $code $message");

JSON::respond(array(
    'success' => $code < 300,
    'code' => $code,
    'message' => $message
));