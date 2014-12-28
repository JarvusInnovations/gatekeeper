<?php

namespace Gatekeeper;


// load key if present
try {
    if ($Key = Key::getFromRequest()) {
        $_EVENT['request']->setKey($Key);
    }

} catch (\Gatekeeper\Keys\InvalidKeyException $e) {
    \JSON::error('provided gatekeeper key is invalid', 401);
}