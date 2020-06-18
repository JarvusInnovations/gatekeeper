<?php

function Dwoo_Plugin_ip_wildcard_regex(Dwoo_Core $dwoo, $input)
{
    $ipRegex = preg_replace(
        '/\./',
        '\.',
        preg_replace(
            '/\*/',
            '\d{1,3}',
            $input
        )
    );

    return '/^' . $ipRegex . '$/';
}

