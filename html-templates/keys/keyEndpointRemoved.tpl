{extends "designs/site.tpl"}

{block "title"}Endpoint removed from key &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$KeyEndpoint = $data}

    <p class="lead">Endpoint {endpoint $KeyEndpoint->Endpoint} has been <strong>removed</strong> from key {apiKey $KeyEndpoint->Key}.</p>

    <p><a href="/keys/{$KeyEndpoint->Key->Key}">&larr;&nbsp;Back to {$KeyEndpoint->Key->OwnerName}</a></p>
{/block}