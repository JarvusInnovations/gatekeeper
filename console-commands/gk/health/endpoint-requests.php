<?php

use Gatekeeper\Endpoints\Endpoint;

foreach (Endpoint::getAll() AS $Endpoint) {
    printf("%s\t%u\n", $Endpoint->Handle, $Endpoint->getCounterMetric('requests'));
}
