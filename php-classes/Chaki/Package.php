<?php

namespace Chaki;

use Site;
use Cache;
use Emergence_FS;
use Jarvus\Sencha\Framework;
use Jarvus\Sencha\Util;
use Gitonomy\Git\Repository;
use Gitonomy\Git\Reference\Branch;

class Package extends \Jarvus\Sencha\Package
{
    public static $sharedCacheDirectory = '/tmp/chaki-packages';
    public static $packageCacheTime = 60;


    protected $path;
    protected $branch;

    protected $repo;
    protected $virtualPath;


    // factories
    public static function load($name, Framework $framework)
    {
        $cacheKey = "chaki/packages/$framework/$name";

        if (false === ($packageData = Cache::fetch($cacheKey))) {
            // get repo
            $repoPath = static::$sharedCacheDirectory . "/$name.git";

            if (is_dir($repoPath)) {
                $repo = new Repository($repoPath);
            } else {
                $chakiData = @file_get_contents('http://chaki.io/packages/'.$name.'?format=json');

                if (!$chakiData || !($chakiData = @json_decode($chakiData, true))) {
                    Cache::store($cacheKey, null, static::$packageCacheTime);
                    return null;
                }

                $repo = \Gitonomy\Git\Admin::cloneTo($repoPath, 'https://github.com/'.$chakiData['data']['GitHubPath'].'.git', true);
            }


            // ensure branches are all up to date
            $repo->run('fetch', ['--force', 'origin', '*:*']);


            // choose best branch
            $references = $repo->getReferences();

            $branchName = null;
            $frameworkVersionStack = explode('.', $framework->getVersion());

            while (
                count($frameworkVersionStack) &&
                ($branchName = $framework->getName() . '/' . implode('/', $frameworkVersionStack)) &&
                !$references->hasBranch($branchName)
            ) {
                array_pop($frameworkVersionStack);
                $branchName = null;
            }

            if (!$branchName && $references->hasBranch($framework->getName())) {
                $branchName = $framework->getName();
            }

            if (!$branchName) {
                $branchName = 'master';
            }


            // read packages.json
            $packageConfig = @json_decode(Util::cleanJson($repo->run('show', ["$branchName:package.json"])), true);

            if (!$packageConfig || empty($packageConfig['name'])) {
                throw new \Exception('Could not parse package.json for ' . $repo->getPath() . '#' . $branchName);
            }

            if ($name != $packageConfig['name']) {
                throw new \Exception("Name from package.json does not match package directory name for chaki package $name");
            }


            // build and cache package data
            $packageData = [
                'path' => $repoPath,
                'branch' => $branchName,
                'name' => $name,
                'config' => $packageConfig
            ];

            Cache::store($cacheKey, $packageData, static::$packageCacheTime);
        }


        return $packageData ? new static($packageData['name'], $packageData['config'], $packageData['path'], $packageData['branch']) : null;
    }


    // magic methods and property getters
    public function __construct($name, $config, $path, $branch)
    {
        parent::__construct($name, $config);
        $this->path = $path;
        $this->branch = $branch;
    }


    // member methods
    public function getFileContents($path)
    {
        try {
            return $this->getRepo()->run('show', ["$this->branch:$path"]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFilePointer($path)
    {
        $buffer = fopen('php://memory', 'w+b');

        try {
            fwrite($buffer, $this->getRepo()->run('show', ["$this->branch:$path"]));
            rewind($buffer);
            return $buffer;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function writeToDisk($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $this->getRepo(['working_dir' => $path])->run('checkout', ['-f', $this->branch]);

        return true;
    }

    protected function getRepo($options = null)
    {
        if ($options) {
            // no caching of custom-configured repo
            return new Repository($this->path, $options);
        }

        if (!$this->repo) {
            $this->repo = new Repository($this->path);
        }

        return $this->repo;
    }

    public function getVirtualPath($autoLoad = true)
    {
        if ($this->virtualPath) {
            return $this->virtualPath;
        }

        $this->virtualPath = "sencha-workspace/packages/$this";

        $tmpPath = Emergence_FS::getTmpDir();
        $this->writeToDisk($tmpPath);
        Emergence_FS::importTree($tmpPath, $this->virtualPath);

        return $this->virtualPath;
    }

    public function updateRepo()
    {
        $this->getRepo()->run('fetch', ['origin', '+refs/heads/*:refs/heads/*']);
    }
}