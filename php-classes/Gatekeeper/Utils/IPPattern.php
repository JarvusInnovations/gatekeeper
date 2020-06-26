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
    * @param string $ipPattern The IP Pattern string to parse (ex. 10.0.0.1/24,192.168.1.1*)
    * @param string $returnType The type of IP patterns to return ONLY. (ex. ip)
    *
    * @return array|closure Returns an array if pattern contains ONLY static IPs, or returns a closure function
    * that can be used to compare if an IP matches the pattern.
    *
    */
    public static function parse($pattern, $uid)
    {
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

        if (count($subPatternsByType['cidr']) === 0 && count($subPatternsByType['wildcard']) === 0) {
            return $subPatternsByType['ip'];
        }

        return static::generateClosure($subPatternsByType, $uid);
    }

   /**
    *
    * Generate Closure function and write to filesystem with the name of the pattern hashed
    *
    * @param array $patterns Multi-dimensional array of sub-patterns, keyed by pattern type.
    * @param string $patternHash Original Pattern hashed.
    *
    * @return closure Closure function
    */
    protected static function generateClosure(array $patterns, $patternHash)
    {

        $closureFunctionString =
<<<DOC
            <?php

            return function(\$ipInput) {

DOC;

        foreach ($patterns['ip'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'ip');
        }

        foreach ($patterns['cidr'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'cidr');
        }

        foreach ($patterns['wildcard'] as $ipPattern) {
            $closureFunctionString .= static::generateClosureCondition($ipPattern, 'wildcard');
        }

        $closureFunctionString .= <<<DOC
            };
DOC;

        $filesystem = Storage::getFileSystem(static::$fsRootDir);
        $fileName = "{$patternHash}.php";

        $filesystem->write(
            $fileName,
            $closureFunctionString
        );

        return include join('/', [Storage::getLocalStorageRoot(), static::$fsRootDir, $fileName]);
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
                    if (ip2long(\$ipInput) === $ipLong) {
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
                    \$ipLong = ip2long(\$ipInput);
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