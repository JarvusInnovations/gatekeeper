<?php

function cidr_range_min($subnet, $mask)
{
    return $subnet & $mask;
}

function cidr_range_max($subnet, $mask)
{
    return $subnet | ~$mask;
}

function Dwoo_Plugin_ip_cidr_range(Dwoo_Core $dwoo, $input)
{
    list ($subnet, $bits) = explode('/', $input);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $bits);

    return [
        'min' => cidr_range_min($subnet, $mask),
        'max' => cidr_range_max($subnet, $mask)
    ];
}


