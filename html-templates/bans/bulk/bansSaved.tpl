{extends "designs/site.tpl"}

{block "title"}Ban saved &mdash; {$dwoo.parent}{/block}

{block "content"}
    <h4>Bans created for the following patterns:</h4>
    {foreach from=$data item=Ban}
        <p class="lead">{$Ban->IPPattern}</p>
    {/foreach}

    <p><a class="button" href="/bans">&larr;&nbsp;Browse all bans</a></p>
{/block}