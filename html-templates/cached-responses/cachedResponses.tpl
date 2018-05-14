{extends designs/site.tpl}

{block title}Logged Requests &mdash; {$dwoo.parent}{/block}

{block content}
{load_templates subtemplates/paging.tpl}

    <header class="page-header">
        <h2 class="page-title">Cached Responses {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
        <div class="page-buttons">
            <small class="muted">Download this page:&nbsp;</small>
            <a class="button" href="/cached-responses?{refill_query format=json}">JSON</a>
            <a class="button" href="/cached-responses?{refill_query format=csv}">CSV</a>

            <small class="muted">Download all:&nbsp;</small>
            <a class="button" href="/cached-responses?{refill_query format=json limit=0 offset=0}">JSON</a>
            <a class="button" href="/cached-responses?{refill_query format=csv limit=0 offset=0}">CSV</a>
        </div>
    </header>

    <table>
        <thead>
            <tr>
                <th class="col-request">Request</th>
                <th class="col-created">Created</th>
                <th class="col-hits">Hits</th>
                <th class="col-last-hit">Last Hit</th>
                <th class="col-expiration">Expiration</th>
            </tr>
        </thead>

        <tbody>
            {foreach item=response from=$data}
                <tr>
                    <td class="col-request">GET <small>{$response.value.path|escape|default:'/'}{tif $response.value.query ? "?$response.value.query"|query_string}</small></td>
                    <td class="col-created">{$response.creation_time|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                    <td class="col-hits">{$response.num_hits}</td>
                    <td class="col-last-hit">{$response.access_time|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                    <td class="col-expiration">{$response.value.expires|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="5" class="col-empty-text">No responses cached right now.</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    {if $limit}{pagingLinks $total pageSize=$limit showAll=true}{/if}
{/block}
