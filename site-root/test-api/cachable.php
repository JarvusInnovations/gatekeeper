<?php

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 10)); // strtotime('2014-02-22')

JSON::respond(array(
    'success' => true,
    'foo' => 'bar',
    'time' => date('Y-m-d H:i:s')
));