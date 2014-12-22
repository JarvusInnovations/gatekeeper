{extends "designs/site.tpl"}

{block "title"}Alert dismissed &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Alert = $data}

    <p class="lead">You <strong>acknowledged and dismissed</strong> alert {alert $Alert}.</p>

    <p><a href="/alerts">Browse all alerts</a></p>
{/block}