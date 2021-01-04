<?php

namespace Gatekeeper;

use Gatekeeper\Bans\Ban;


if (Ban::isIPAddressBanned($_EVENT['request']->getClientAddress())) {
    header('HTTP/1.1 403 Forbidden');
    \JSON::error('Your IP address is currently banned from using this service');
}