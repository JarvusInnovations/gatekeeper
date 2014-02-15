{extends designs/site.tpl}

{block title}{$data->OwnerName|escape} &mdash; Keys &mdash; {$dwoo.parent}{/block}

{block content}
	{$Key = $data}

	<h2>Key: {apiKey $Key}</h2>

	<section id="key-log">

		<table>
			<caption>
				<a class="button pull-right" href="/keys/{$Key->Key}/edit">Edit Key</a>
				<h3>Request Log <small>(Last 30)</small></h3>
			</caption>
			<thead>
				<tr>
					<th class="col-endpoint">Endpoint</th>
					<th class="col-request">Request</th>
					<th class="col-timestamp">Timestamp</th>
					<th class="col-response-code"><small>Response</small> Code</th>
					<th class="col-response-time"><small>Response</small> Time</th>
					<th class="col-response-size"><small>Response</small> Size</th>
					<th class="col-client-ip">Client IP</th>
				</tr>
			</thead>

			<tbody>
			{foreach item=Request from=LoggedRequest::getAllByField('KeyID', $Key->ID, array(order="ID DESC", limit=30))}
				<tr>
					<td class="col-endpoint">{endpoint $Request->Endpoint}</td>
					<td class="col-request">{$Request->Method} <small>{$Request->Path}{tif $Request->Query ? "?$Request->Query"}</small></td>
					<td class="col-timestamp">{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
					<td class="col-response-code">{$Request->ResponseCode}</td>
					<td class="col-response-time">{$Request->ResponseTime|number_format}&nbsp;ms</td>
					<td class="col-response-size">{$Request->ResponseBytes|number_format}&nbsp;B</td>
					<td class="col-client-ip">{$Request->ClientIP|long2ip}</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="7" class="col-empty-text">No requests logged yet.</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</section>
{/block}