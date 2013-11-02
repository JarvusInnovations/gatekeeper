{extends designs/site.tpl}

{block title}{$data->OwnerName|escape} &mdash; Keys &mdash; {$dwoo.parent}{/block}

{block content}
	{$Key = $data}

	<h2>Key: {key $Key}</h2>

	<form method="GET">
    	<a class="button pull-right" href="/keys/{$Key->Key}/edit">Edit Key</a>
	</form>

	<section id="key-log">
		<h3>Request Log <small>(Last 30)</small></h3>

		<table>
			<tr>
				<th>Endpoint</th>
				<th>Request</th>
				<th>Timestamp</th>
				<th>Response Code</th>
				<th>Response Time</th>
				<th>Response Size</th>
				<th>Client IP</th>
			</tr>

			{foreach item=Request from=LoggedRequest::getAllByField('KeyID', $Key->ID, array(order="ID DESC", limit=30))}
				<tr>
					<td>{endpoint $Request->Endpoint}</td>
					<td>{$Request->Method} {$Request->Path}{tif $Request->Query ? "?$Request->Query"}</td>
					<td>{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
					<td>{$Request->ResponseCode}</td>
					<td>{$Request->ResponseTime|number_format} ms</td>
					<td>{$Request->ResponseBytes|number_format} B</td>
					<td>{$Request->ClientIP|long2ip}</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="7"><em class="muted">No requests logged yet</em></td>
				</tr>
			{/foreach}
		</table>
	</section>
{/block}