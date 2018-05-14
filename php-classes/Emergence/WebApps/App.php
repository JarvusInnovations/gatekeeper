<?php

namespace Emergence\WebApps;

use Site;


abstract class App implements IApp
{
    public static $buildsRoot = 'webapp-builds';
    public static $types = [
        SenchaApp::class
    ];


    protected $name;


    final public static function get($name)
    {
        foreach (static::$types as $type) {
            if (!is_a($type, IApp::class, true)) {
                throw new \Exception("app type $type does not implement IApp interface");
            }

            if ($app = $type::load($name)) {
                return $app;
            }
        }

        return null;
    }


    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAsset($path)
    {
        if (is_string($path)) {
            $path = Site::splitPath($path);
        }

        array_unshift($path, static::$buildsRoot, $this->name);

        return Site::resolvePath($path);
    }

    public function renderAsset($path)
    {
        $assetNode = $this->getAsset($path);

        if (!$assetNode) {
            header('HTTP/1.0 404 Not Found');
            die('asset not found: '.implode('/', $path));
        }

        $assetNode->outputAsResponse();
    }
}