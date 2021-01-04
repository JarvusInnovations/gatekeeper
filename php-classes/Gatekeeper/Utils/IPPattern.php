<?php

namespace Gatekeeper\Utils;

use Emergence\Site\Storage;

use InvalidArgumentException;
use UnexpectedValueException;

// should take a IP Pattern and return a closure
class IPPattern {

    public static $fsRootDir = 'ip-patterns/matchers';
   /**
    *
    * Parse IP pattens by splitting them by spaces or commas and grouping them
    * in the available groups: ip, cidr, or wildcard.
    *
    * @param string $pattern The IP Pattern string to parse (ex. 10.0.0.1/24,192.168.1.1*)
    *
    * @return array|closure Returns an array if pattern contains ONLY static IPs, or returns a closure function
    * that can be used to compare if an IP matches the pattern.
    *
    */
    public static function parse($pattern)
    {
        // maintain results in a static cache
        static $cache = [];

        // return from statci cache early if available
        $patternHash = sha1($pattern);

        if (!empty($cache[$patternHash])) {
            return $cache[$patternHash];
        }

        // try to load from filesystem cache
        $cacheFilePath = join('/', [
            Storage::getLocalStorageRoot(),
            static::$fsRootDir,
            $patternHash . '.php'
        ]);

        try {
            $closure = include($cacheFilePath);

            if ($closure) {
                return $cache[$patternHash] = $closure;
            }
        } catch (\Exception $e) {
            // continue...
        }

        // parse patterns and organize by type
        $subPatternsByType = [
            'ip' => [],
            'cidr' => [],
            'wildcard' => []
        ];

        $count = 0;
        foreach (preg_split("/[\s,]+/", $pattern) as $subPattern) {
            $subPatternType = static::getPatternType($subPattern);
            if (!empty($subPattern) && array_key_exists($subPatternType, $subPatternsByType)) {
                $subPatternsByType[$subPatternType][] = $subPattern;
                $count++;
            }
        }

        if ($count === 0) {
            throw new InvalidArgumentException("Unable to parse IP pattern: $pattern");
        }

        // fast path for static IP lists
        if (count($subPatternsByType['cidr']) === 0 && count($subPatternsByType['wildcard']) === 0) {
            // TODO: cache to a file?
            return $cache[$patternHash] = $subPatternsByType['ip'];
        }

        // generate source code for closure
        $closureFunctionString = "<?php\n\n";
        $closureFunctionString .= "return function(\$ipInput) {\n";

        if (count($subPatternsByType['ip']) || count($subPatternsByType['cidr'])) {
            $closureFunctionString .= "    \$ipLong = ip2long(\$ipInput);\n\n";
        }

        foreach ($subPatternsByType['ip'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'ip') . PHP_EOL;
        }

        foreach ($subPatternsByType['cidr'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'cidr') . PHP_EOL;
        }

        foreach ($subPatternsByType['wildcard'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'wildcard') . PHP_EOL;
        }

        $closureFunctionString .= "};\n";

        // write to filesystem
        file_put_contents($cacheFilePath, $closureFunctionString);

        return $cache[$patternHash] = include($cacheFilePath);
    }

    /**
     *
     * Generate Closure condition string for use in the generateClosure() function.
     *
     * @param string $pattern Sub-pattern to create condition for.
     * @param string $patternType Type of ip pattern (ip, cidr, wilcdcard).
     *
     * @return string If Condition in string format
     */
    protected static function generateClosureCondition($pattern, $patternType)
    {
        switch ($patternType) {
            case 'ip':
                $ipLong = ip2long($pattern);
                $conditionString = <<<DOC
                    # IP Address = $pattern
                    if (\$ipLong === $ipLong) {
                        return true;
                    }

DOC;
                break;

            case 'cidr':
                list($min, $max) = static::getRangeLongMinMax($pattern);
                $minIp = long2ip($min);
                $maxIp = long2ip($max);

                $conditionString = <<<DOC
                    # CIDR IP Range = $pattern
                    # Min: $minIp Max: {$maxIp}
                    if (\$ipLong >= $min && \$ipLong <= $max) {
                        return true;
                    }

DOC;

                break;

            case 'wildcard':
                $patternRegex = static::getWildcardRegex($pattern);
                return <<<DOC
                    # Wildcard IP Address = $pattern
                    if (preg_match("$patternRegex", \$ipInput)) {
                        return true;
                    }

DOC;

                break;

            default:
                throw new InvalidArgumentException('Invalid IP pattern: '. $pattern);
        }

        return $conditionString;
    }

   /**
    *
    * Get Regex pattern for checking if a given IP address matches.
    *
    * @param string $wildcardPattern The wildcard pattern to match against
    *
    * @return string string containing wildcard Regex pattern.
    */
    protected static function getWildcardRegex($wildcardPattern)
    {
        $ipRegex = preg_replace(
            '/\./',
            '\.',
            preg_replace(
                '/\*/',
                '\d{1,3}',
                $wildcardPattern
            )
        );

        return '/^' . $ipRegex . '$/';
    }

   /**
    *
    * Get min/max values for a Classless Inter-Domain Routing (CIDR) IP range.
    *
    * @param string $range The CIDR IP range to convert (ex. 10.0.0.1/24).
    *
    * @return array containing min and max IP addresses in Long notation.
    */
    protected static function getRangeLongMinMax($range)
    {
        list ($subnet, $bits) = explode('/', $range);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $min = $subnet & $mask;
        $max = $subnet | ~$mask;

        return [
            $min,
            $max
        ];
    }

   /**
    *
    * Get ip pattern type.
    *
    * @param string $pattern
    *
    * @return string Pattern type. Either: ip, cidr, or wildcard.
    */
    protected static function getPatternType($pattern)
    {
        if (strpos($pattern, '*') !== false) {
            return 'wildcard';
        }

        if (strpos($pattern, '/')) {
            return 'cidr';
        }

        if (filter_var($pattern, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'ip';
        }

        throw new UnexpectedValueException("IP Pattern could not be validated: $pattern");
    }
}
