<?php

Site::$debug = true; // set to true for extended query logging
Site::$production = true; // set to true for heavy file caching
#Site::$autoPull = false;

Site::$permittedOrigins = '*';

Site::$skipSessionPaths[] = 'api.php';
Site::$skipSessionPaths[] = 'test-api/cachable.php';
Site::$skipSessionPaths[] = 'test-api/status.php';