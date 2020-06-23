{template exactMatch ipPattern}
    # IP Address = {$ipPattern}
    if ($ipLong === {ip2long($ipPattern)}){literal} {
        return true;
    }{/literal}
{/template}
{template wildcardMatch ipPattern}
    # Wildcard IP Address = {$ipPattern}
    if (preg_match("{$ipPattern|ip_wildcard_regex}", $ipInput)){literal} {
        return true;
    }{/literal}
{/template}
{template cidrMatch ipPattern}
    {$ranges = $ipPattern|ip_cidr_range}
    # CIDR IP Range = {$ipPattern}
    # Min: {$ranges.min|long2ip} Max: {$ranges.max|long2ip}
    if ($ipLong >= {$ranges.min} && $ipLong <= {$ranges.max}){literal} {
        return true;
    }{/literal}
{/template}

{block closure-method}
{"<?php"}
{$ipPatterns = $data}
{literal}return function($ipInput) {{/literal}
    $ipLong = ip2long($ipInput);
    {foreach from=$ipPatterns.ip item=ipPattern}
        {if $ipPattern}{exactMatch $ipPattern}{/if}
    {/foreach}
    {foreach from=$ipPatterns.cidr item=ipPattern}
        {if $ipPattern}{cidrMatch $ipPattern}{/if}
    {/foreach}
    {foreach from=$ipPatterns.wildcard item=ipPattern}
        {if $ipPattern}{wildcardMatch $ipPattern}{/if}
    {/foreach}

    return false;
{literal}}{/literal}
{"?>"}
{/block}