{extends "designs/site.tpl"}

{block "title"}Exemption saved &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Exemption = $data}

    <p class="lead">
        Exemption on
        {if $Exemption->IPPattern}IP Address Pattern: <strong>{$Exemption->IPPattern}</strong>
        {else}Key: {apiKey $Exemption->Key}
        {/if}
        {tif $Exemption->isNew ? created : saved}.
    </p>

    <p><a href="/exemptions">&larr;&nbsp;Browse all exemptions</a></p>
{/block}