<?php

use Gatekeeper\Metrics;
use Gatekeeper\MetricSample;

$cronKey = Site::getConfig('cron_key');

if (!$cronKey) {
    header('HTTP/1.0 501 Not Implemented');
    die('Host does not have a cron_key configured');
}

if (empty($_GET['cron_key'])) {
    header('HTTP/1.0 401 Unauthorized');
    die('cron_key required');
}

if ($_GET['cron_key'] != $cronKey) {
    header('HTTP/1.0 401 Unauthorized');
    die('cron_key is incorrect');
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    die('POST method required');
}

$cacheKeyPrefix = Cache::getKeyPrefix();
$currentSampleIndex = Metrics::getCurrentSampleIndex();

$flushed = 0;
foreach (Cache::getIterator('|^metrics/|') AS $cacheEntry) {
    if (!preg_match('|^'.preg_quote($cacheKeyPrefix).'(metrics/(.*)/(\d+))$|', $cacheEntry['key'], $matches)) {
        continue;
    }

    $cacheKey = $matches[1];
    $metricKey = $matches[2];
    $sampleIndex = (int)$matches[3];

    // skip active and previus samples
    if ($sampleIndex >= $currentSampleIndex - 1) {
        continue;
    }

    // delete sample from cache
    Cache::delete($cacheKey);

    // save metric to DB
    $sample = MetricSample::create([
        'Timestamp' => $sampleIndex * Metrics::$sampleDuration,
        'Key' => $metricKey,
        'Value' => $cacheEntry['value']
    ], true);

    // increment counter
    $flushed++;
}

printf('Flushed %u metrics' . PHP_EOL, $flushed);