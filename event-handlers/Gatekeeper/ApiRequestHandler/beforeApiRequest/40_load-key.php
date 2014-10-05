<?php

// load key if present
if (!empty($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/^Gatekeeper-Key\s+(\w+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $keyMatches)) {
    $keyString = $keyMatches[1];
} elseif (!empty($_REQUEST['gatekeeperKey'])) {
    $keyString = $_REQUEST['gatekeeperKey'];
}

if ($keyString) {
    if ($Key = Key::getByKey($keyString)) {
        $_EVENT['request']->setKey($Key);
    } else {
        JSON::error('provided gatekeeper key is invalid', 401);
    }
}