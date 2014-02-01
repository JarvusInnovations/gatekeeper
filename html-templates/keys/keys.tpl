{extends designs/site.tpl}

{block title}Keys &mdash; {$dwoo.parent}{/block}

{block content}

	<h2>Keys</h2>

	<form method="GET">
		<a class="button pull-right" href="/keys/create">Create Key</a>
		<label>
			Sort by
			<select name="sort" onchange="this.form.submit()">
				<option value="">No sort</option>
				<option value="calls-total" {refill field=sort selected=calls-total}>Total calls</option>
				<option value="calls-week" {refill field=sort selected=calls-week}>Calls this week</option>
				<option value="calls-day-avg" {refill field=sort selected=calls-day-avg}>Average calls per day</option>
				<option value="endpoints" {refill field=sort selected=endpoints}># of endpoints</option>
			</select>
			<select name="dir" onchange="this.form.submit()">
				<option {refill field=dir selected=DESC}>DESC</option>
				<option {refill field=dir selected=ASC}>ASC</option>
			</select>
		</label>
	</form>
	
	<section class="keys">
		<p class="muted">Metrics are updated every {Key::$metricTTL} seconds.</p>

		{foreach item=Key from=$data}
			{$metrics = array(
				callsTotal = $Key->getMetric(calls-total)
				,callsWeek = $Key->getMetric(calls-week)
				,callsDayAvg = $Key->getMetric(calls-day-avg)
				,endpoints = tif($Key->AllEndpoints, null, $Key->getMetric(endpoints))
			)}

			<article class="key">
				<div class="key-metric"><strong>{$metrics.callsTotal|number_format} call{tif $metrics.callsTotal != 1 ? s}</strong> all time</div>
                <div class="details">
    				<header>
    					<h3 class="title">{key $Key}</h3>
    					<div class="owner">{if $Key->ContactEmail}
    						{$recipient = $Key->ContactEmail}
    						{if $Key->ContactName}
    							{$recipient = "$Key->ContactName <$recipient>"}
    						{/if}
        						<a href="mailto:{$recipient|escape}" title="Contact key owner">{$recipient|escape}</a>
        					{elseif $Key->ContactName}
        						{$Key->ContactName}
        					{/if}
    					</div>
    				</header>
    				<ul class="other-metrics">
    					<li><strong>{$metrics.callsWeek|number_format} call{tif $metrics.callsWeek != 1 ? s}</strong> this week</li>
    					<li><strong>{$metrics.callsDayAvg|number_format} call{tif $metrics.callsDayAvg != 1 ? s}</strong> avg per day</li>
    					<li><strong>{tif $Key->AllEndpoints ? 'All' : $metrics.endpoints|number_format} endpoint{tif $Key->AllEndpoints || $metrics.endpoints != 1 ? s}</strong> permitted</li>
    				</ul>
                </div>
				<footer>
					<a class="button" href="/keys/{$Key->Key}/edit">Edit</a>
					<a class="button" href="#suspend">Suspend</a>
					<a class="button" href="/keys/{$Key->Key}#key-log">View Log</a>
				</footer>
			</article>
		{/foreach}

	</section>
{/block}