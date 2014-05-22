{extends designs/site.tpl}

{block title}Ban #{$data->ID} &mdash; Bans &mdash; {$dwoo.parent}{/block}

{block content}
    {$Ban = $data}

    <article class="ban">
	    <div class="details">
			<header class="page-header">
				<h3 class="header-title">
					{if $Ban->IP}
						IP Address: <strong>{$Ban->IP|long2ip}</strong>
					{else}
						Key: <strong>{apiKey $Ban->Key}</strong>
					{/if}
				</h3>

				<div class="ban-term">Banned {if $Ban->ExpirationDate}until {$Ban->ExpirationDate|date_format}{else}indefinitely{/if}.</div>
			</header>
			{if $Ban->Notes}
				<pre class="ban-notes">{$Ban->Notes|escape}</pre>
			{/if}
	    </div>
		<footer>
			<a class="button" href="/bans/{$Ban->ID}/edit">Edit</a>
			<a class="button" href="/bans/{$Ban->ID}/delete">Remove</a>
		</footer>
	</article>
{/block}