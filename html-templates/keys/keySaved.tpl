{extends "designs/site.tpl"}

{block "title"}Key saved &mdash; {$dwoo.parent}{/block}

{block "content"}
	{$Key = $data}
	
	<p class="lead">API key <a href="/keys/{$Key->Key}">{$Key->Key}</a> {tif $Key->isNew ? created : saved} for {$Key->OwnerName|escape}.</p>

	<p><a href="/keys">Browse all keys</a></p>
{/block}