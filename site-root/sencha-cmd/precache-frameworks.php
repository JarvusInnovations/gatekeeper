<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);

Benchmark::startLive();

foreach (Sencha::$frameworks AS $framework => $frameworkConfig) {
    $cachedFiles = Emergence_FS::cacheTree("sencha-workspace/$framework-$frameworkConfig[defaultVersion]");
    Benchmark::mark("precached sencha-workspace/$framework-$frameworkConfig[defaultVersion]");
}

// precache workspace config
$cachedFiles = Emergence_FS::cacheTree('sencha-workspace/.sencha');
Benchmark::mark("precached sencha-workspace/.sencha");