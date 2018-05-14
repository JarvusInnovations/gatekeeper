<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);
$defaultExclude = array(
    "#/\\.sass-cache(/|$)#"
    ,"#/\\.sencha-backup(/|$)#"
    ,"#/\\.emergence(/|$)#"
);

if (empty($_GET['dumpWorkspace'])) {
    Benchmark::startLive();
}

// get app name
if (empty($_REQUEST['name'])) {
    die('Parameter name required');
}

$appName = $_REQUEST['name'];
$App = new Sencha_App($appName);

Benchmark::mark("configured request: appName=$appName");

// get framework
$framework = $App->getFramework();
$frameworkVersion = $App->getFrameworkVersion();
$cmdVersion = $App->getBuildCfg('app.cmd.version');

if (!$frameworkVersion) {
    die("Unable to determine framework version, if this is an old application you need to manually set app.framework.version in .sencha/app/sencha.cfg");
}

if (!$cmdVersion) {
    die("Unable to determine CMD version, if this is an old application you need to manually set app.cmd.version in .sencha/app/sencha.cfg");
}

// set paths
$workspacePath = 'sencha-workspace';
$workspaceConfigPath = "$workspacePath/.sencha";
$frameworkPath = "$workspacePath/$framework-$frameworkVersion";
$packagesPath = "$workspacePath/packages";
$appPath = "$workspacePath/$appName";
$buildPath = "sencha-build/$appName/production";

// get temporary directory and set paths
$tmpPath = Emergence_FS::getTmpDir();
$workspaceConfigTmpPath = "$tmpPath/.sencha";
$frameworkTmpPath = "$tmpPath/$framework";
$packagesTmpPath = "$tmpPath/packages";
$appTmpPath = "$tmpPath/$appName";
$buildTmpPath = "$tmpPath/build/$appName/production";

Benchmark::mark("created tmp: $tmpPath");


// precache and write workspace config
$cachedFiles = Emergence_FS::cacheTree($workspaceConfigPath);
Benchmark::mark("precached $cachedFiles files in $workspaceConfigPath");
$exportResult = Emergence_FS::exportTree($workspaceConfigPath, $workspaceConfigTmpPath);
Benchmark::mark("exported $workspaceConfigPath to $workspaceConfigTmpPath: ".http_build_query($exportResult));

// ... packages
if (!($requiredPackages = $App->getAppCfg('requires')) || !is_array($requiredPackages)) {
    $requiredPackages = array();
}

if (($themeName = $App->getBuildCfg('app.theme')) && !in_array($themeName, $requiredPackages)) {
    $requiredPackages[] = $themeName;
}

foreach ($requiredPackages AS $packageName) {
    $cachedFiles = Emergence_FS::cacheTree("$packagesPath/$packageName");
    Benchmark::mark("precached $cachedFiles files in $packagesPath/$packageName");
    $exportResult = Emergence_FS::exportTree("$packagesPath/$packageName", "$packagesTmpPath/$packageName");
    Benchmark::mark("exported $packagesPath to $packagesTmpPath/$packageName: ".http_build_query($exportResult));
}

// ... framework
$exportResult = Emergence_FS::exportTree($frameworkPath, $frameworkTmpPath);
Benchmark::mark("exported $frameworkPath to $frameworkTmpPath: ".http_build_query($exportResult));

// ... app
$exportResult = Emergence_FS::exportTree($appPath, $appTmpPath);
Benchmark::mark("exported $appPath to $appTmpPath: ".http_build_query($exportResult));

// ... last production build
mkdir($buildTmpPath, 0777, true);
$exportResult = Emergence_FS::exportTree($buildPath, $buildTmpPath);
Benchmark::mark("exported $buildPath to $buildTmpPath: ".http_build_query($exportResult));


// write any libraries from classpath
$classPaths = explode(',', $App->getBuildCfg('app.classpath'));

foreach ($classPaths AS $classPath) {
    if (strpos($classPath, '${workspace.dir}/x/') === 0) {
        $extensionPath = substr($classPath, 19);
        $classPathSource = "ext-library/$extensionPath";
        $classPathDest = "$tmpPath/x/$extensionPath";
        Benchmark::mark("importing classPathSource: $classPathSource");

#		$cachedFiles = Emergence_FS::cacheTree($classPathSource);
#		Benchmark::mark("precached $cachedFiles files in $classPathSource");

        $sourceNode = Site::resolvePath($classPathSource);

        if (is_a($sourceNode, SiteFile)) {
            mkdir(dirname($classPathDest), 0777, true);
            copy($sourceNode->RealPath, $classPathDest);
            Benchmark::mark("copied file $classPathSource to $classPathDest");
        } else {
            $exportResult = Emergence_FS::exportTree($classPathSource, $classPathDest);
            Benchmark::mark("exported $classPathSource to $classPathDest: ".http_build_query($exportResult));
        }
    }
}

// write archive
if (!empty($_GET['archive'])) {
    try {
        $exportResult = Emergence_FS::exportTree($archivePath, $archiveTmpPath);
        Benchmark::mark("exported $archivePath to $archiveTmpPath: ".http_build_query($exportResult));
    } catch (Exception $e) {
        Benchmark::mark("failed to export $archivePath, continuing");
    }
}

// change into app's directory
chdir($appTmpPath);
Benchmark::mark("chdir to: $appTmpPath");


// prepare cmd
$cmd = Sencha::buildCmd($cmdVersion, 'ant', "-Dbuild.dir=$buildTmpPath", 'sass');
Benchmark::mark("running CMD: $cmd");

// execute CMD
passthru("$cmd 2>&1", $cmdStatus);

Benchmark::mark("CMD finished: exitCode=$cmdStatus");

// import resources directory
if ($cmdStatus == 0) {
    Benchmark::mark("importing $buildTmpPath/resources to $buildPath/resources");

    $importResults = Emergence_FS::importTree("$buildTmpPath/resources", "$buildPath/resources", array(
        'exclude' => $defaultExclude
    ));
    Benchmark::mark("imported files: ".http_build_query($importResults));
}


// clean up
if (empty($_GET['leaveWorkspace'])) {
    exec("rm -R $tmpPath");
    Benchmark::mark("erased $tmpPath");
}
