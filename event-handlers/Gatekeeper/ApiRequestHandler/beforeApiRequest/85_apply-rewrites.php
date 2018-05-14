<?php

namespace Gatekeeper;


$url = $_EVENT['request']->getUrl();


// apply rewrite rules
$url = $_EVENT['request']->getEndpoint()->applyRewrites($url);


// save new URL to request
$_EVENT['request']->setUrl($url);