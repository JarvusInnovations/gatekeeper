<?php

namespace Gatekeeper;


// build request URL from remaining path stack
$path = '/' . implode('/', array_map('rawurlencode', $_EVENT['request']->getPathStack()));


// pass query string through as-is
$query = $_SERVER['QUERY_STRING'];


// strip gatekeeper key from query
$query = preg_replace('/(^|&)gatekeeperKey=[a-zA-Z0-9]+(&|$)/', '$2', $query);


// save URL to request object
$_EVENT['request']->setUrl(rtrim($path . '?' . ltrim($query, '?&'), '?&'));