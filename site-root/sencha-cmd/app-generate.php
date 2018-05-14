<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);
Benchmark::startLive();


// get app name
if (empty($_REQUEST['name'])) {
    die('Parameter name required');
}

$appName = $_REQUEST['name'];

// get framework
if (empty($_REQUEST['framework']) || !array_key_exists($_REQUEST['framework'], Sencha::$frameworks)) {
    die('Parameter framework required');
}

$framework = $_REQUEST['framework'];

// get framework version
$frameworkVersion = Sencha::normalizeFrameworkVersion($framework, empty($_REQUEST['frameworkVersion']) ? Sencha::$frameworks[$framework]['defaultVersion'] : $_REQUEST['frameworkVersion']);

Benchmark::mark("configured request: appName=$appName, framework=$framework, frameworkVersion=$frameworkVersion");

// get temporary directory
$tmpPath = Emergence_FS::getTmpDir();
$tmpConfigPath = "$tmpPath/.sencha";
$tmpFrameworkPath = "$tmpPath/$framework";
Benchmark::mark("created tmp: $tmpPath");


$workspacePath = 'sencha-workspace';
$workspaceConfigPath = "$workspacePath/.sencha";
$workspaceFrameworkPath = "$workspacePath/$framework-$frameworkVersion";

// precache framework and workspace config
$cachedFiles = Emergence_FS::cacheTree($workspaceConfigPath);
Benchmark::mark("precached $workspaceConfigPath");

$cachedFiles = Emergence_FS::cacheTree($workspaceFrameworkPath);
Benchmark::mark("precached $workspaceFrameworkPath");

// write workspace to tmp
$exportResult = Emergence_FS::exportTree($workspaceConfigPath, $tmpConfigPath);
Benchmark::mark("exported $workspaceConfigPath to $tmpConfigPath: ".http_build_query($exportResult));

$exportResult = Emergence_FS::exportTree($workspaceFrameworkPath, $tmpFrameworkPath);
Benchmark::mark("exported $workspaceFrameworkPath to $tmpFrameworkPath: ".http_build_query($exportResult));

// begin cmd
$appPath = "$workspacePath/$appName";
$appTmpPath = "$tmpPath/$appName";

$cmd = Sencha::buildCmd(null, "-sdk $tmpFrameworkPath", 'generate app', $appName, $appTmpPath); //config -prop templates.dir=/root/templates then
Benchmark::mark("running CMD: $cmd");

passthru("$cmd 2>&1", $cmdStatus);
Benchmark::mark("CMD finished: exitCode=$cmdStatus");


// import app
if ($cmdStatus == 0) {
    Benchmark::mark("importing $appTmpPath to $appPath");

    $importResults = Emergence_FS::importTree($appTmpPath, $appPath, array(
        'exclude' => array(
            "#^/$framework/#"
        )
    ));
    Benchmark::mark("imported files: ".http_build_query($importResults));
}


// clean up
if (empty($_GET['leaveWorkspace'])) {
    exec("rm -R $tmpPath");
    Benchmark::mark("erased $tmpPath");
}