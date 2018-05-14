{extends "designs/site.tpl"}

{block "title"}Alert acknowledged &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Alert = $data}

    <p class="lead">You <strong>acknowledged</strong> alert {alert $Alert}.</p>

    <p><a href="/alerts">Browse all alerts</a></p>
{/block}