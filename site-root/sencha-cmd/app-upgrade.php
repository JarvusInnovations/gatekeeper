<?php

/** THIS SCRIPT HAS NOT BEEN VERIFIED TO WORK CORRECTLY YET FOR ANY PARTICULAR CMD UPGRADE PATHS **/

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);
Benchmark::startLive();

// get app name
if (empty($_REQUEST['name'])) {
    die('Parameter name required');
}

$appName = $_REQUEST['name'];
$App = new Sencha_App($appName);

// get target framework version
if (empty($_REQUEST['cmdVersion'])) {
    die('Parameter cmdVersion required');
}

$cmdVersion = $_REQUEST['cmdVersion'];



Benchmark::mark("configured request: appName=$appName");

// get framework
$framework = $App->getFramework();
$frameworkVersion = $App->getFrameworkVersion();

if (!$frameworkVersion) {
    die("Unable to determine framework version, if this is an old application you need to manually set app.framework.version in .sencha/app/sencha.cfg");
}

// set paths
$workspacePath = 'sencha-workspace';
$workspaceConfigPath = "$workspacePath/.sencha";
$frameworkPath = "$workspacePath/$framework-$frameworkVersion";
$packagesPath = "$workspacePath/packages";
$appPath = "$workspacePath/$appName";
$archivePath = "sencha-build/$appName/archive";

// get temporary directory and set paths
$tmpPath = Emergence_FS::getTmpDir();
$workspaceConfigTmpPath = "$tmpPath/.sencha";
$frameworkTmpPath = "$tmpPath/$framework";
$packagesTmpPath = "$tmpPath/packages";
$appTmpPath = "$tmpPath/$appName";
$archiveTmpPath = "$appTmpPath/archive";

Benchmark::mark("created tmp: $tmpPath");


// precache and write workspace config
$cachedFiles = Emergence_FS::cacheTree($workspaceConfigPath);
Benchmark::mark("precached $workspaceConfigPath");
$exportResult = Emergence_FS::exportTree($workspaceConfigPath, $workspaceConfigTmpPath);
Benchmark::mark("exported $workspaceConfigPath to $workspaceConfigTmpPath: ".http_build_query($exportResult));

// ... packages
$cachedFiles = Emergence_FS::cacheTree($packagesPath);
Benchmark::mark("precached $packagesPath");
$exportResult = Emergence_FS::exportTree($packagesPath, $packagesTmpPath);
Benchmark::mark("exported $packagesPath to $packagesTmpPath: ".http_build_query($exportResult));

// ... framework
$cachedFiles = Emergence_FS::cacheTree($frameworkPath);
Benchmark::mark("precached $frameworkPath");
$exportResult = Emergence_FS::exportTree($frameworkPath, $frameworkTmpPath);
Benchmark::mark("exported $frameworkPath to $frameworkTmpPath: ".http_build_query($exportResult));

// ... app
$cachedFiles = Emergence_FS::cacheTree($appPath);
Benchmark::mark("precached $appPath");
$exportResult = Emergence_FS::exportTree($appPath, $appTmpPath);
Benchmark::mark("exported $appPath to $appTmpPath: ".http_build_query($exportResult));


// write any libraries from classpath
$classPaths = explode(',', $App->getBuildCfg('app.classpath'));

foreach ($classPaths AS $classPath) {
    if (substr($classPath, 0, 2) == 'x/') {
        $classPathSource = 'ext-library'.substr($classPath, 1);
        $cachedFiles = Emergence_FS::cacheTree($classPathSource);
        Benchmark::mark("precached $cachedFiles files from $classPathSource");
        $exportResult = Emergence_FS::exportTree($classPathSource, "$appTmpPath/$classPath");
        Benchmark::mark("exported $classPathSource to $appTmpPath/$classPath: ".http_build_query($exportResult));
    }
}



// change into app's directory
chdir($appTmpPath);
Benchmark::mark("chdir to: $appTmpPath");

// prepare and run upgrade command
$upgradeCmd = Sencha::buildCmd($cmdVersion, 'app upgrade --noframework');
Benchmark::mark("running upgrade CMD: $upgradeCmd");

passthru($upgradeCmd, $upgradeCmdStatus);
Benchmark::mark("Upgrade CMD finished: exitCode=$upgradeCmdStatus");

// import results
Benchmark::mark("importing $appTmpPath");

$importResults = Emergence_FS::importTree($appTmpPath, $appPath, array(
    'exclude' => array(
        "#^/x(/|$)#"
        ,"#/\\.sass-cache(/|$)#"
        ,"#/\\.sencha-backup(/|$)#"
        ,"#/\\.emergence(/|$)#"
    )
));
Benchmark::mark("imported files: ".http_build_query($importResults));


// clean up
if (empty($_GET['leaveWorkspace'])) {
    exec("rm -R $tmpPath");
    Benchmark::mark("erased $tmpPath");
}