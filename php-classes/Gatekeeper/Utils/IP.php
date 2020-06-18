<?php

namespace Gatekeeper\Utils;

class IP
{

    public static function isInRange($ip, $ranges = [])
    {
        if (is_string($ranges)) {
            $ranges = [$ranges];
        }

        $formattedRanges = array_map(['static', 'prepareRange'], $ranges);

        static::sortRanges($formattedRanges);

        $rangeByIP = static::findRangeByIP($ip, $formattedRanges);

        return $rangeByIP;
    }

    protected static function findRangeByIP($ip, $ranges = [], $start = null, $end = null) {
        if ($end < $start || $start > $end) {
            return false;
        }

        if(is_null($start)) {
            $start = 0;
        }

        if(is_null($end)) {
            $end = count($ranges) - 1;
        }

        $ipLong = ip2long($ip);
        $mid = (int)floor(($end + $start) / 2);

        switch(static::inRange($ipLong, $ranges[$mid])) {
            case 0:
                return $ranges[$mid][2];
            case -1:
                return static::findRangeByIP($ip, $ranges, $start, $mid-1);
            case 1:
                return static::findRangeByIP($ip, $ranges, $mid+1, $end);
        }
    }


    protected static function inRange($ipLong, $range)
    {
        list($start, $end) = $range;

        if ($ipLong < $start) {
            return -1;
        } elseif ($ipLong > $end) {
            return 1;
        } else {
            return 0;
        }
    }

    protected static function prepareRange($range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $min = $subnet & $mask;
        $max = $subnet | ~$mask;

        return [
            $min,
            $max,
            $range
        ];
    }

    protected static function sortRanges(array &$ranges = [])
    {
        // sort by start, then by end. aka from narrowest overlapping range to widest
        usort($ranges, function($a, $b) {
            return $a[0] - $b[0] === 0 ?
                $a[1] - $b[1] :
                $a[0] - $b[0] ;
        });
    }
}