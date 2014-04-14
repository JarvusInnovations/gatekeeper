{extends designs/site.tpl}

{block title}{$data->Title|escape} &mdash; Endpoints &mdash; {$dwoo.parent}{/block}

{block js-bottom}
    {$dwoo.parent}
    <script>
        Ext.onReady(function(){
            var colOptions = Ext.select('.col-options');
            
            colOptions.on('click', function(ev, t){
                var opt = Ext.get(t);
                    opt.radioCls('selected');
                    opt.up('table').toggleCls('query-expand');
            }, null, { delegate: '.col-option' });
        });
    </script>
{/block}

{block content}
	{$Endpoint = $data}

	<header class="page-header">
	    <h2 class="page-title">Endpoint: {endpoint $Endpoint}</h2>
	    <div class="page-buttons">
        	<a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}/edit">Edit Endpoint</a>
	    </div>
	</header>

	<section class="page-section" id="endpoint-docs">
		<h3>Documentation</h3>

		<p class="muted"><em>Not yet implemented. Either <a href="https://github.com/mashery/iodocs">I/O Docs</a> or <a href="https://github.com/wordnik/swagger-ui">Swagger</a> will be integrated in the future to allow JSON-defined documentation to be entered here.</em></p>
	</section>

    <form class="page-section" id="endpoint-rewrites" action="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}/rewrites" method="POST">
        <table>
			<caption>
				<h3>Rewrite Rules</h3>
			</caption>
			<thead>
				<tr>
					<th class="col-priority">Priority</th>
					<th class="col-pattern">Pattern</th>
					<th class="col-replace">Replace</th>
					<th class="col-last">Last?</th>
				</tr>
			</thead>

			<tbody>
                {foreach item=Rewrite from=$Endpoint->Rewrites}
        			<tr>
    					<td class="col-priority">{field name="rewrites[$Rewrite->ID][Priority]" default=$Rewrite->Priority}</td>
    					<td class="col-pattern">{field name="rewrites[$Rewrite->ID][Pattern]" default=$Rewrite->Pattern}</td>
    					<td class="col-replace">{field name="rewrites[$Rewrite->ID][Replace]" default=$Rewrite->Replace}</td>
    					<td class="col-last">{checkbox name="rewrites[$Rewrite->ID][Last]" value=1 unsetValue=0 default=$Rewrite->Last}</td>
    				</tr>
                {/foreach}
        		<tr>
					<td class="col-priority">{field name="rewrites[new][Priority]" placeholder=EndpointRewrite::getFieldOptions(Priority, default)}</td>
					<td class="col-pattern">{field name="rewrites[new][Pattern]" placeholder="|^/routes/([^/]+)|i"}</td>
					<td class="col-replace">{field name="rewrites[new][Replace]" placeholder="/?route=\$1"}</td>
					<td class="col-last">{checkbox name="rewrites[new][Last]" value=1 unsetValue=0}</td>
				</tr>
            </tbody>
        </table>
        <input type="submit" value="Save Rewrites">
    </form>

    {if $Endpoint->CachingEnabled}
	<section class="page-section" id="endpoint-cache">
    	<table>
			<caption>
				<h3>Cached Responses <small>(Top and most recent 30)</small></h3>
			</caption>
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
                {foreach item=response from=$Endpoint->getCachedResponses(30)}
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
	</section>
    {/if}

	<section class="page-section" id="endpoint-log">
		<table>
			<caption>
				<h3>Request Log <small>(Last 30)</small></h3>
			</caption>
			<thead>
				<tr>
					<th class="col-request">
					    Request
					    <ul class="col-options">
					        <li class="col-option query-inline selected" title="Show Query Params Inline">Inline</li>
					        <li class="col-option query-list" title="Show Query Params as List">List</li>
					    </ul>
                    </th>
					<th class="col-timestamp">Timestamp</th>
					<th class="col-response-code"><small>Response</small> Code</th>
					<th class="col-response-time"><small>Response</small> Time</th>
					<th class="col-response-size"><small>Response</small> Size</th>
					<th class="col-client-ip">Client IP</th>
					<th class="col-key">Key</th>
				</tr>
			</thead>

			<tbody>
			{foreach item=Request from=LoggedRequest::getAllByField('EndpointID', $Endpoint->ID, array(order="ID DESC", limit=30))}
				<tr>
					<td class="col-request">{$Request->Method} <small>{$Request->Path|escape|default:/}{tif $Request->Query ? "?$Request->Query"|query_string}</small></td>
					<td class="col-timestamp">{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
					<td class="col-response-code">{$Request->ResponseCode}</td>
					<td class="col-response-time">{$Request->ResponseTime|number_format}&nbsp;ms</td>
					<td class="col-response-size">{$Request->ResponseBytes|number_format}&nbsp;B</td>
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
        <a class="button" href="/logs?endpoint={$Endpoint->Handle}&endpointVersion={$Endpoint->Version}">View Full Log &rarr;</a>
	</section>
{/block}