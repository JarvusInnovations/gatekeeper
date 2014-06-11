<?php

Gatekeeper::authorizeTestApiAccess();

$cacheSecs = !empty($_GET['secs']) && ctype_digit($_GET['secs']) ? $_GET['secs'] : 30;

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheSecs));

JSON::respond(array(
    'success' => true,
    'foo' => 'bar',
    'time' => date('Y-m-d H:i:s')
));