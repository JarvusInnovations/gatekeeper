{extends "designs/site.tpl"}

{block "title"}Endpoint deleted &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Endpoint = $data}

    <p class="lead">Endpoint {endpoint $Endpoint} deleted.</p>

    <p><a href="/endpoints">Browse all endpoints</a></p>
{/block}