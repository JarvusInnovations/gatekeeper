{extends designs/site.tpl}

{block title}{$data->Title|escape} &mdash; Endpoints &mdash; {$dwoo.parent}{/block}

{block content}
	{$Endpoint = $data}

	<h2>Endpoint: {endpoint $Endpoint}</h2>

	<form method="GET">
    	<a class="button pull-right" href="/endpoints/{$Endpoint->Handle}/edit">Edit Endpoint</a>
	</form>
	
	<section id="endpoint-docs">
		<h3>Documentation</h3>

		<p class="muted"><em>Not yet implemented. Either <a href="https://github.com/mashery/iodocs">I/O Docs</a> or <a href="https://github.com/wordnik/swagger-ui">Swagger</a> will be integrated in the future to allow JSON-defined documentation to be inputted here.</em></p>
	</section>

	<section id="endpoint-cache">
		<h3>Cache Status</h3>

		<p class="muted"><em>Coming soon</em></p>
	</section>

	<section id="endpoint-log">
		<h3>Request Log <small>(Last 30)</small></h3>

		<table>
			<tr>
				<th>Request</th>
				<th>Timestamp</th>
				<th>Response Code</th>
				<th>Response Time</th>
				<th>Response Size</th>
				<th>Client IP</th>
				<th>Key</th>
			</tr>

			{foreach item=Request from=LoggedRequest::getAllByField('EndpointID', $Endpoint->ID, array(order="ID DESC", limit=30))}
				<tr>
					<td>{$Request->Method} {$Request->Path}{tif $Request->Query ? "?$Request->Query"}</td>
					<td>{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
					<td>{$Request->ResponseCode}</td>
					<td>{$Request->ResponseTime|number_format} ms</td>
					<td>{$Request->ResponseBytes|number_format} B</td>
					<td>{$Request->ClientIP|long2ip}</td>
					<td>{if $Request->Key}{key $Request->Key}{else}<small class="muted">&mdash;</small></td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="7"><em class="muted">No requests logged yet</em></td>
				</tr>
			{/foreach}
		</table>
	</section>
{/block}