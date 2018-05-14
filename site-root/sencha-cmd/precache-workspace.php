<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);

Benchmark::startLive();

// precache workspace config
$cachedFiles = Emergence_FS::cacheTree('sencha-workspace/.sencha');
Benchmark::mark("precached sencha-workspace/.sencha");

// precache workspace packages
$cachedFiles = Emergence_FS::cacheTree('sencha-workspace/packages');
Benchmark::mark("precached sencha-workspace/packages");

// precache workspace microloaders
$cachedFiles = Emergence_FS::cacheTree('sencha-workspace/microloaders');
Benchmark::mark("precached sencha-workspace/microloaders");