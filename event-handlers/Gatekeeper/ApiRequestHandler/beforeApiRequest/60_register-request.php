<?php

namespace Gatekeeper;

use Gatekeeper\Metrics\Metrics;


$Endpoint = $_EVENT['request']->getEndpoint();
$userIdentifier = $_EVENT['request']->getUserIdentifier();


// build identifier string for current user
$_EVENT['metrics']['endpointRequests'] = Metrics::appendCounter("endpoints/$Endpoint->ID/requests");
$_EVENT['metrics']['endpointUserRequests'] = Metrics::appendCounter("endpoints/$Endpoint->ID/users/$userIdentifier/requests");
$_EVENT['metrics']['userRequests'] = Metrics::appendCounter("users/$userIdentifier/requests");