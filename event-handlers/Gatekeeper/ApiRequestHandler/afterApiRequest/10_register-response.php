<?php

namespace Gatekeeper;


$Endpoint = $_EVENT['request']->getEndpoint();


// log executed response
$_EVENT['metrics']['endpoint-responses-executed'] = Metrics::appendCounter("endpoints/$Endpoint->ID/responses-executed");
$_EVENT['metrics']['endpoint-response-time'] = Metrics::appendAverage("endpoints/$Endpoint->ID/response-time", $_EVENT['Transaction']->ResponseTime, $_EVENT['metrics']['endpoint-responses-executed']);