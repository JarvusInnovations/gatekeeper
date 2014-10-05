<?php

$Key = $_EVENT['request']->getKey();


if ($Key && $Key->ExpirationDate && $Key->ExpirationDate < $_EVENT['request']->getStartTime()) {
    JSON::error('provided gatekeeper key is expired', 403);
}