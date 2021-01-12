<?php

Gatekeeper\Gatekeeper::authorizeTestApiAccess();

$clientIp = Emergence\Site\Client::getAddress();

JSON::respond([
    'success' => $clientIp != null,
    'client_ip' => $clientIp
]);
