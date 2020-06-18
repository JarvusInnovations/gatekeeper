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

{"<?php"}

{block closure-method}
{literal}return function($ipInput) {{/literal}
    $ipLong = ip2long($ipInput);
    {foreach from=$data key=ipPattern item=type}
        {if $ipPattern && $type === 'ip'}{exactMatch $ipPattern}
        {elseif $ipPattern && $type === 'wildcard'}{wildcardMatch $ipPattern}
        {elseif $ipPattern && $type === 'cidr'}{cidrMatch $ipPattern}
        {/if}
    {/foreach}
    return false;
{literal}}{/literal}
{/block}

{"?>"}