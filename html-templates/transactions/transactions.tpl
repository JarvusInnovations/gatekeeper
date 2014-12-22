{extends designs/site.tpl}

{block title}Transactions Log &mdash; {$dwoo.parent}{/block}

{block content}
    {load_templates subtemplates/paging.tpl}

    <header class="page-header">
        <h2 class="page-title">Logged Requests {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
        <div class="page-buttons">
            <small class="muted">Download this page:&nbsp;</small>
            <a class="button" href="?{refill_query format=json}">JSON</a>
            <a class="button" href="?{refill_query format=csv}">CSV</a>

            <small class="muted">Download all:&nbsp;</small>
            <a class="button" href="?{refill_query format=json limit=0 offset=0}">JSON</a>
            <a class="button" href="?{refill_query format=csv limit=0 offset=0}">CSV</a>
        </div>
    </header>

    <table>
        <thead>
                <th class="col-request">Request</th>
                <th class="col-timestamp">Timestamp</th>
                <th class="col-response-code"><small>Response</small> Code</th>
                <th class="col-response-time"><small>Response</small> Time</th>
                <th class="col-response-size"><small>Response</small> Size</th>
                <th class="col-client-ip">Client IP</th>
                <th class="col-key">Key</th>
            </tr>
        </thead>

        <tbody>
        {foreach item=Request from=$data}
            <tr>
                <td class="col-request">{$Request->Method} <small>{$Request->Path|default:/}{tif $Request->Query ? "?$Request->Query"|query_string}</small></td>
                <td class="col-timestamp">{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                <td class="col-response-code">{$Request->ResponseCode}</td>
                <td class="col-response-time">{$Request->ResponseTime|number_format} ms</td>
                <td class="col-response-size">{$Request->ResponseBytes|number_format} B</td>
                <td class="col-client-ip">{$Request->ClientIP|long2ip}</td>
                <td class="col-key">{if $Request->Key}{apiKey $Request->Key}{else}<small class="muted">&mdash;</small>{/if}</td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="7" class="col-empty-text">No requests logged yet.</td>
            </tr>
        {/foreach}
        </tbody>
    </table>

    {if $limit}
        {$count = count($data)}
        Showing {$offset+1}&ndash;{$offset+$count}
        {if $count == $limit}
            <a class="paging-link next" href="?{refill_query offset=$offset+$limit}">Next&nbsp;&rarr;</a>
        {/if}
    {/if}
{/block}
