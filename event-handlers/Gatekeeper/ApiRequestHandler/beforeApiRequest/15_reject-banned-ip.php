<?php

namespace Gatekeeper;

use Gatekeeper\Bans\Ban;
use Emergence\Site\Client;


if (Ban::isIPAddressBanned(Client::getAddress())) {
    header('HTTP/1.1 403 Forbidden');
    \JSON::error('Your IP address is currently banned from using this service');
}