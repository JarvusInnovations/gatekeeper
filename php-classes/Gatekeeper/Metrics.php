<?php

namespace Gatekeeper;

use Cache;

class Metrics
{
    public static $sampleDuration = 30; // 3600; // 1 hr

    protected static $_currentSampleIndex;

    public static function getCurrentSampleIndex()
    {
        if (!static::$_currentSampleIndex) {
            static::$_currentSampleIndex = floor(time() / static::$sampleDuration);
        }

        return static::$_currentSampleIndex;
    }

    public static function appendCounter($counterKey)
    {
        $sampleIndex = static::getCurrentSampleIndex();
        $cacheKey = "metrics/$counterKey/$sampleIndex";

        $newValue = Cache::increase($cacheKey);

        if ($newValue === false) {
            Cache::store($cacheKey, 1);
            $newValue = 1;
        }

        return $newValue;
    }

    public static function estimateCounter($counterKey)
    {
        $currentSampleIndex = static::getCurrentSampleIndex();

        $currentSampleValue = Cache::fetch("metrics/$counterKey/$currentSampleIndex");
        if ($currentSampleValue === false) {
            $currentSampleValue = 0;
        }

        $currentSampleSecondsRemaining = ($currentSampleIndex + 1) * static::$sampleDuration - time();
        if ($currentSampleSecondsRemaining == 0) {
            return $currentSampleValue;
        }

        $previousSampleValue = Cache::fetch("metrics/$counterKey/" . ($currentSampleIndex - 1));
        if ($previousSampleValue === false) {
            $previousSampleValue = 0;
        }

        return round($currentSampleValue + $previousSampleValue * ($currentSampleSecondsRemaining / static::$sampleDuration));
    }

    public static function appendAverage($averageKey, $newValue, $sampleCount = null)
    {
        $sampleIndex = static::getCurrentSampleIndex();
        $cacheKey = "metrics/$averageKey/$sampleIndex";

        // if a sample count is known, use it to append the old average
        if (
            $sampleCount &&
            ($oldAverage = Cache::fetch($cacheKey)) !== false
        ) {
            // knowing the last average and how many samples are included in it we can proportionally adjust the average by the new value
            $newAverage = round(($newValue + $oldAverage * ($sampleCount - 1)) / $sampleCount);
        } else {
            $newAverage = $newValue;
        }

        Cache::store($cacheKey, $newAverage);

        return $newAverage;
    }

    public static function estimateAverage($averageKey, $counterKey)
    {
        $currentSampleIndex = static::getCurrentSampleIndex();

        $currentSampleValue = Cache::fetch("metrics/$averageKey/$currentSampleIndex");

        $currentSampleSecondsRemaining = ($currentSampleIndex + 1) * static::$sampleDuration - time();
        if ($currentSampleSecondsRemaining == 0) {
            return $currentSampleValue;
        }

        $previousSampleIndex = $currentSampleIndex - 1;
        $previousSampleValue = Cache::fetch("metrics/$averageKey/$previousSampleIndex");
        
        if ($currentSampleValue === false && $previousSampleValue === false) {
            return null;
        } elseif ($currentSampleValue === false) {
            return $previousSampleValue;
        } elseif ($previousSampleValue === false) {
            return $currentSampleValue;
        } else {
            $currentSampleWeight = Cache::fetch("metrics/$counterKey/$currentSampleIndex") * (1 - ($currentSampleSecondsRemaining / static::$sampleDuration));
            $previusSampleWeight = Cache::fetch("metrics/$counterKey/$previousSampleIndex") * ($currentSampleSecondsRemaining / static::$sampleDuration);
            
            return round(
                (
                    $currentSampleValue * $currentSampleWeight
                    +
                    $previousSampleValue * $previusSampleWeight
                )
                /
                ($currentSampleWeight + $previusSampleWeight)
            );
        }
    }
}