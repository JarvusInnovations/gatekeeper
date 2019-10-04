{extends "designs/site.tpl"}

{block "title"}Key issued &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}

    <p class="lead">API key {apiKey $Key->Key} has been shared with <q>{$.post.Email|escape}</q></p>
{/block}