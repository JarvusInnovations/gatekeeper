<?php

namespace Gatekeeper;


$Endpoint = $_EVENT['request']->getEndpoint();


if ($Endpoint->DeprecationDate && $Endpoint->DeprecationDate < $_EVENT['request']->getStartTime()) {
    header('HTTP/1.1 410 Gone');
    \JSON::error('This endpoint+version has been deprecated');
}