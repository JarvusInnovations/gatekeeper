{extends "designs/site.tpl"}

{block "title"}Endpoint {tif $data->isNew ? 'created' : 'saved'} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Endpoint = $data}

    <p class="lead">API endpoint {endpoint $Endpoint} {tif $Endpoint->isNew ? created : saved}.</p>

    <p>
        <a href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">&larr;&nbsp;Back to {$Endpoint->Title|escape}</a><br>
        <a href="/endpoints">&larr;&nbsp;Browse all endpoints</a>
    </p>
{/block}