{extends designs/site.tpl}

{block title}Logged Requests &mdash; {$dwoo.parent}{/block}

{block content}
{load_templates subtemplates/paging.tpl}
    
    <h2>Logged Requests</h2>

    {capture assign=pagingHtml}
	    {if $limit}{pagingLinks $total pageSize=$limit}{/if}
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
		{foreach item=LoggedRequest from=$data}
			<tr>
				<td class="col-request">{$LoggedRequest->Method} <small>{$Request->Path|default:/}{tif $Request->Query ? "?$Request->Query"}</small></td>
				<td class="col-timestamp">{$LoggedRequest->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
				<td class="col-response-code">{$LoggedRequest->ResponseCode}</td>
				<td class="col-response-time">{$LoggedRequest->ResponseTime|number_format} ms</td>
				<td class="col-response-size">{$LoggedRequest->ResponseBytes|number_format} B</td>
				<td class="col-client-ip">{$LoggedRequest->ClientIP|long2ip}</td>
				<td class="col-key">{if $LoggedRequest->Key}{key $LoggedRequest->Key}{else}<small class="muted">&mdash;</small></td>
			</tr>
		{foreachelse}
			<tr>
				<td colspan="7" class="col-empty-text">No requests logged yet.</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	{$pagingHtml}
{/block}
