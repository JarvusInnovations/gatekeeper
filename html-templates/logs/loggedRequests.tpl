{extends designs/site.tpl}

{block title}Logged Requests &mdash; {$dwoo.parent}{/block}

{block content}
{load_templates subtemplates/paging.tpl}
    
    <header class="page-header">
        <h2 class="page-title">Logged Requests {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
    </header>
    
    {capture assign=pagingHtml}
	    {if $limit}{pagingLinks $total pageSize=$limit showAll=true}{/if}
    {/capture}

    {$pagingHtml}

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
				<td class="col-key">{if $Request->Key}{apiKey $Request->Key}{else}<small class="muted">&mdash;</small></td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="7" class="col-empty-text">No requests logged yet.</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	{$pagingHtml}

    <a class="button" href="/logs/json{tif $.server.QUERY_STRING ? "?$.server.QUERY_STRING"}">Download JSON</a>
    <a class="button" href="/logs/csv{tif $.server.QUERY_STRING ? "?$.server.QUERY_STRING"}">Download CSV</a>
{/block}
