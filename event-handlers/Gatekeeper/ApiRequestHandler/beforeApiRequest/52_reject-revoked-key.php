<?php

namespace Gatekeeper;


$Key = $_EVENT['request']->getKey();


if ($Key && $Key->Status == 'revoked') {
    \JSON::error('provided gatekeeper key is revoked', 403);
}