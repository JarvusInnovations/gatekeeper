<?php

namespace Gatekeeper;

use Gatekeeper\Metrics\Metrics;


$Endpoint = $_EVENT['request']->getEndpoint();


// log executed response
$_EVENT['metrics']['endpointResponsesExecuted'] = Metrics::appendCounter("endpoints/$Endpoint->ID/responsesExecuted");
$_EVENT['metrics']['endpointBytesExecuted'] = Metrics::appendCounter("endpoints/$Endpoint->ID/bytesExecuted", $_EVENT['Transaction']->ResponseBytes);
$_EVENT['metrics']['endpointResponseTime'] = Metrics::appendAverage("endpoints/$Endpoint->ID/responseTime", $_EVENT['Transaction']->ResponseTime, $_EVENT['metrics']['endpointResponsesExecuted']);