<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);

Benchmark::startLive();

if (!$framework = $_GET['framework']) {
    die('parameter "framework" missing');
}
if (!$frameworkVersion = $_GET['version']) {
    die('parameter "version" missing');
}

$cachedFiles = Emergence_FS::cacheTree("sencha-workspace/$framework-$frameworkVersion");
Benchmark::mark("precached $cachedFiles files in sencha-workspace/$framework-$frameworkVersion");