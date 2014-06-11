<?php

$delay = !empty($_REQUEST['delay']) && is_numeric($_REQUEST['delay']) ? $_REQUEST['delay'] : 5;

sleep($delay);

JSON::respond(array(
    'success' => 200,
    'delay' => $delay
));