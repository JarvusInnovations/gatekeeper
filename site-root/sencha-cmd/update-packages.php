<?php

$GLOBALS['Session']->requireAccountLevel('Developer');






/**
 * Setup environment
 */
    set_time_limit(0);
    Site::$debug = !empty($_REQUEST['debug']);
    Benchmark::startLive();








/**
 * Load top-level components
 */
    // get app
    if (empty($_REQUEST['app'])) {
        die('Parameter app required');
    }

    $app = Jarvus\Sencha\App::get($_REQUEST['app']);

    if (!$app) {
        throw new \Exception('Failed to load app');
    }

    Benchmark::mark("loaded app: $app");


    // get framework
    $framework = $app->getFramework();

    if (!$framework) {
        throw new \Exception('Failed to load framework');
    }

    Benchmark::mark("loaded framework: $framework");



/**
 * Write fromework and packages to VFS
 */

    // get path to framework on disk
    $frameworkVirtualPath = $framework->getVirtualPath();
    Benchmark::mark("got virtual path to framework: $frameworkVirtualPath");

    foreach ($app->getAllRequiredPackages() AS $packageName => $package) {
        if (!($package instanceof \Chaki\Package) && ($chakiPackage = \Chaki\Package::load($packageName, $framework))) {
            Benchmark::mark("found chaki package for $packageName");

            $chakiPackage->updateRepo();
            Benchmark::mark("updated repository for $packageName");

            $packageVirtualPath = $chakiPackage->getVirtualPath();
            Benchmark::mark("got virtual path to package: $packageVirtualPath");
        }
    }