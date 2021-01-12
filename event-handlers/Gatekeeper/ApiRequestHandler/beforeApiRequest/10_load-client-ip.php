<?php

namespace Gatekeeper;

use Emergence\Site\Client;


// populate request with client IP address
$_EVENT['request']->setClientAddress(Client::getAddress());
