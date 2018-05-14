{extends "designs/site.tpl"}

{block "title"}Key deleted &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}

    <p class="lead">Key {apiKey $Key} deleted.</p>

    <p><a href="/keys">Browse all keys</a></p>
{/block}