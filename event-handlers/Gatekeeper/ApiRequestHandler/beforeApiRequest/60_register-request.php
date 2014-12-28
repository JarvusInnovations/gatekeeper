<?php

namespace Gatekeeper;

use Gatekeeper\Metrics\Metrics;


$Endpoint = $_EVENT['request']->getEndpoint();
$userIdentifier = $_EVENT['request']->getUserIdentifier();


// build identifier string for current user
$_EVENT['metrics']['endpoint-requests'] = Metrics::appendCounter("endpoints/$Endpoint->ID/requests");
$_EVENT['metrics']['endpoint-user-requests'] = Metrics::appendCounter("endpoints/$Endpoint->ID/users/$userIdentifier/requests");