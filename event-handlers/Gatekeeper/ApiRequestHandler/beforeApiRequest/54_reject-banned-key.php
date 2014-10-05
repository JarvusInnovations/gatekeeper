<?php

$Key = $_EVENT['request']->getKey();


if ($Key && Ban::isKeyBanned($Key)) {
    JSON::error('Your API key is currently banned from using this service', 403);
}