<?php

namespace Gatekeeper\Utils;

class IP
{
    // todo: remove if not needed
    /**
    *
    * Sort IP ranges by start, then by end (from narrowest overlapping range to widest).
    *
    * @param array $ranges The array of IP ranges to sort
    * @return string
    *
    */

    public static function sortRanges(array &$ranges = [])
    {
        // sort
        usort($ranges, function($a, $b) {
            return $a[0] - $b[0] === 0 ?
                $a[1] - $b[1] :
                $a[0] - $b[0] ;
        });
    }

}