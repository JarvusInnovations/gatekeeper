<?php

namespace Gatekeeper;


$url = $_EVENT['request']->getUrl();


// trim trailing query string characters
$url = rtrim($url, '?&');


// if a duplicate ? was appended, convert it to an &
if (substr_count($url, '?') > 1) {
    $url[strrpos($url, '?')] = '&';
}


// save new URL to request object
$_EVENT['request']->setUrl($url);