<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);
Benchmark::startLive();


// get framework
if (empty($_REQUEST['framework'])) {
    die('Parameter framework required');
}

$framework = $_REQUEST['framework'];

Benchmark::mark("configured request: framework=$framework");


// get temporary directory
$tmpPath = Emergence_FS::getTmpDir();
Benchmark::mark("created tmp: $tmpPath");


// write workspace
$workspacePath = 'sencha-workspace';
Benchmark::mark("precaching workspace: $workspacePath");

$cachedFiles = Emergence_FS::cacheTree($workspacePath);
Benchmark::mark("precaching finished: $cachedFiles files loaded/updated");

Benchmark::mark("exporting workspace: $workspacePath to $tmpPath");

$exportResult = Emergence_FS::exportTree($workspacePath, $tmpPath);
Benchmark::mark("exported finished: ".http_build_query($exportResult));


// begin cmd
set_time_limit(0);
$cmd = Sencha::buildCmd($framework, 'generate workspace', $tmpPath);
Benchmark::mark("running CMD: $cmd");

passthru($cmd, $cmdStatus);
Benchmark::mark("CMD finished: exitCode=$cmdStatus");

// import app
if ($cmdStatus == 0) {
    $destPath = 'sencha-workspace';
    Benchmark::mark("importing to: $destPath");

    $filesImported = Emergence_FS::importTree($tmpPath, $destPath);
    Benchmark::mark("imported $filesImported files");
}


// clean up
exec("rm -R $tmpPath");
Benchmark::mark("erased $tmpPath");