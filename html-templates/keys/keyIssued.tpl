{extends "designs/site.tpl"}

{block "title"}Key issued &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}
    {$Endpoint = $Key->Endpoints[0]}

    <p class="lead">API key {apiKey $Key->Key} has been issued for /{$Endpoint->Path}.</p>

    <p>
        <a href="/api-docs/{$Endpoint->Path}">&larr;&nbsp;Back to docs for /{$Endpoint->Path}</a><br>
    </p>
{/block}