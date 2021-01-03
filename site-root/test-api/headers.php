<?php


Gatekeeper\Gatekeeper::authorizeTestApiAccess();

$headers = [];

foreach ($_SERVER as $key => $value) {
    if (substr($key, 0, 5) != 'HTTP_') {
        continue;
    }

    $key = substr($key, 5);
    $key = str_replace('_', '-', $key);
    $key = ucwords(strtolower($key), '-');

    $headers[$key] = $value;
}

JSON::respond($headers);
