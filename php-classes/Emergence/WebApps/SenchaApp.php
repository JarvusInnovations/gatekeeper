<?php

namespace Emergence\WebApps;

use Site;
use Cache;
use Emergence\Site\Response;


class SenchaApp extends App
{
    protected $manifest;


    public static function load($name)
    {
        $cacheKey = "sencha-app/{$name}";

        if (!$manifest = Cache::fetch($cacheKey)) {
            // TODO: create cache clear event
            $manifestNode = Site::resolvePath([static::$buildsRoot, $name, 'app.json']);

            if (!$manifestNode) {
                return null;
            }

            $manifest = json_decode(file_get_contents($manifestNode->RealPath), true);

            Cache::store($cacheKey, $manifest);
        }

        return new static($name, $manifest);
    }


    public function __construct($name, array $manifest)
    {
        parent::__construct($name);

        $this->manifest = $manifest;
    }

    public function render()
    {
        return new Response('sencha', [
            'app' => $this
        ]);
    }

    public function buildCssMarkup()
    {
        $html = [];

        foreach ($this->manifest['css'] as $css) {
            $node = $this->getAsset($css['path']);

            if (!$node) {
                throw new \Exception('sencha app css asset not found: '.$css['path']);
            }

            $html[] = "<link rel=\"stylesheet\" href=\"{$css['path']}?_sha1={$node->SHA1}\"/>";
        }

        return implode(PHP_EOL, $html);
    }

    public function buildJsMarkup()
    {
        $html = [];

        foreach ($this->manifest['js'] as $js) {
            $node = $this->getAsset($js['path']);

            if (!$node) {
                throw new \Exception('sencha app js asset not found: '.$js['path']);
            }

            $html[] = "<script type=\"text/javascript\" src=\"{$js['path']}?_sha1={$node->SHA1}\"></script>";
        }

        return implode(PHP_EOL, $html);
    }
}