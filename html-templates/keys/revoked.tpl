{extends "designs/site.tpl"}

{block "title"}Key revoked &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}

    <p class="lead">API key {apiKey $Key->Key} has been revoked.</p>
{/block}