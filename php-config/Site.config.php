<?php

Site::$debug = true; // set to true for extended query logging
Site::$production = true; // set to true for heavy file caching

Site::$permittedOrigins = '*';

Site::$skipSessionPaths[] = 'api.php';