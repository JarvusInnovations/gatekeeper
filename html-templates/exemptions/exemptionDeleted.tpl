{extends "designs/site.tpl"}

{block "title"}Exemption deleted &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Exemption = $data}

    <p class="lead">Exemption on {if $Exemption->IPPattern}IP Pattern: <strong>{$Exemption->IPPattern}</strong>{else}Key: {apiKey $Exemption->Key}{/if} deleted.</p>

    <p><a href="/exemptions">Browse all exemptions</a></p>
{/block}