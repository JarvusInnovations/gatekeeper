{extends "designs/site.tpl"}

{block "title"}Endpoint {tif $data->isNew ? 'created' : 'saved'} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Endpoint = $data}

    <p class="lead">API endpoint {endpoint $Endpoint} {tif $Endpoint->isNew ? created : saved}.</p>

    <p>
        <a href="{$Endpoint->getURL()}">&larr;&nbsp;Back to {$Endpoint->getTitle()|escape}</a><br>
        <a href="/endpoints">&larr;&nbsp;Browse all endpoints</a>
    </p>
{/block}