{extends "designs/site.tpl"}

{block "title"}Endpoint saved &mdash; {$dwoo.parent}{/block}

{block "content"}
	{$Endpoint = $data}
	
	<p class="lead">API endpoint <a href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape}</a> saved.</p>

	<p><a href="/endpoints">Browse all endpoints</a></p>
{/block}