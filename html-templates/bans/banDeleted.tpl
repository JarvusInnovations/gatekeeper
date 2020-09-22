{extends "designs/site.tpl"}

{block "title"}Ban deleted &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Ban = $data}

    <p class="lead">Ban on {if $Ban->IPPattern}IP Pattern: <strong>{$Ban->IPPattern}</strong>{else}Key: {apiKey $Ban->Key}{/if} deleted.</p>

    <p><a href="/bans">Browse all bans</a></p>
{/block}