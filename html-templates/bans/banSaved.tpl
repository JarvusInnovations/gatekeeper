{extends "designs/site.tpl"}

{block "title"}Ban saved &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Ban = $data}

    <p class="lead">
        Ban on
        {if $Ban->IP}IP Address: <strong>{$Ban->IP|long2ip}</strong>
        {elseif $Ban->IPPattern}IP Address Pattern: <strong>{$Ban->IPPattern}</strong>
        {else}Key: {apiKey $Ban->Key}
        {/if}
        {tif $Ban->isNew ? created : saved}.
    </p>

    <p><a href="/bans">&larr;&nbsp;Browse all bans</a></p>
{/block}