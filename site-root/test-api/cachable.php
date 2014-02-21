<?php

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 30));

JSON::respond(array(
    'success' => true,
    'foo' => 'bar',
    'time' => date('Y-m-d H:i:s')
));