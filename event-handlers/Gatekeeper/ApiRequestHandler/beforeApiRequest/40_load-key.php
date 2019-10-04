<?php

namespace Gatekeeper;

use Gatekeeper\Keys\Key;
use Gatekeeper\Keys\InvalidKeyException;


// load key if present
try {
    if ($Key = Key::getFromRequest()) {
        $_EVENT['request']->setKey($Key);
    }

} catch (InvalidKeyException $e) {
    \JSON::error('provided gatekeeper key is invalid', 401);
}