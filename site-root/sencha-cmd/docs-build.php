<?php

$GLOBALS['Session']->requireAccountLevel('Developer');
set_time_limit(0);
Benchmark::startLive();

// get app name
if (empty($_REQUEST['name'])) {
    die('Parameter name required');
}

$appName = $_REQUEST['name'];
$App = new Sencha_App($appName);


Benchmark::mark("configured request: appName=$appName");

// get framework
$framework = $App->getFramework();

// set paths
$workspacePath = 'sencha-workspace';
$appPath = "$workspacePath/$appName";
$appSrcPath = "$appPath/app";
$appSassPath = "$appPath/resources/sass";
$appGuidesPath = "$appPath/guides";
$docsPath = "sencha-docs/$appName";


// get temporary directory and set paths
$tmpPath = Emergence_FS::getTmpDir();

$srcTmpPath = "$tmpPath/src";
$appSrcTmpPath = "$srcTmpPath/app";
$appSassTmpPath = "$srcTmpPath/sass";

$appGuidesTmpPath = "$tmpPath/guides";
$docsTmpPath = "$tmpPath/docs";

Benchmark::mark("created tmp: $tmpPath");


// write app JS source files
$exportResult = Emergence_FS::exportTree($appSrcPath, $appSrcTmpPath);
Benchmark::mark("exported $appSrcPath to $appSrcTmpPath: ".http_build_query($exportResult));


// write app SASS source files
$exportResult = Emergence_FS::exportTree($appSassPath, $appSassTmpPath);
Benchmark::mark("exported $appSassPath to $appSassTmpPath: ".http_build_query($exportResult));


// write guides
$guideJson = Site::resolvePath("$appGuidesPath.json");
if ($guideJson) {
    copy($guideJson->RealPath, "$appGuidesTmpPath.json");
    $exportResult = Emergence_FS::exportTree($appGuidesPath, $appGuidesTmpPath);
    Benchmark::mark("exported $appGuidesPath(.json) to $appGuidesTmpPath: ".http_build_query($exportResult));
}


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


// generate docs
$cmd = "jsduck $srcTmpPath";
if ($guideJson) {
    $cmd .= " --guides $appGuidesTmpPath.json";
}
$cmd .= " --title=\"$appName Documentation\"";
$cmd .= " --warnings=-link,-extend,-type_name,+no_doc";
$cmd .= " --output $docsTmpPath 2>&1";
Benchmark::mark("running jsduck: $cmd");

passthru($cmd, $cmdStatus);
Benchmark::mark("CMD finished: exitCode=$cmdStatus");


// import build
if ($cmdStatus == 0) {
    $buildTmpPath = "$tmpPath/build";
    Benchmark::mark("importing $docsTmpPath to $docsPath");

    $importResults = Emergence_FS::importTree($docsTmpPath, $docsPath);
    Benchmark::mark("imported files: ".http_build_query($importResults));
}


// clean up
if (empty($_GET['leaveWorkspace'])) {
    exec("rm -R $tmpPath");
    Benchmark::mark("erased $tmpPath");
}