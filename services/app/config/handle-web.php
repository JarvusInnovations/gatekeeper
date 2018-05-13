<?php


// initialize site
require('{{ pkg.svc_config_path }}/initialize.php');


// dispatch request
Site::handleRequest();
