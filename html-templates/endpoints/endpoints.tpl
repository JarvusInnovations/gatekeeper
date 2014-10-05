{extends designs/site.tpl}

{block title}Endpoints &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Endpoints</h2>
        <div class="header-buttons">
            <a class="button primary" href="/endpoints/create">Create Endpoint</a>
        </div>
    </header>

    {*
    <form method="GET">
        <label>
            Sort by
            <select name="sort" onchange="this.form.submit()">
                <option value="">No sort</option>
                <option value="calls-total" {refill field=sort selected=calls-total}>Total calls</option>
                <option value="calls-week" {refill field=sort selected=calls-week}>Calls this week</option>
                <option value="responsetime" {refill field=sort selected=responsetime}>Average response time</option>
                <option value="keys" {refill field=sort selected=keys}>API keys</option>
                <option value="clients" {refill field=sort selected=clients}>Unique clients</option>
            </select>
            <select name="dir" onchange="this.form.submit()">
                <option {refill field=dir selected=DESC}>DESC</option>
                <option {refill field=dir selected=ASC}>ASC</option>
            </select>
        </label>
    </form>
    *}
    
    {$sampleDuration = Gatekeeper\Metrics::$sampleDuration}
    {capture assign=sampleDurationString}{strip}
        {if $sampleDuration == 3600}
            hour
        {else}
            {$sampleDuration} second{tif $sampleDuration != 1 ? s}
        {/if}
    {/strip}{/capture}

    <section class="endpoints">
        {*<p class="muted">Metrics are updated every {Endpoint::$metricTTL} seconds.</p>*}

        {foreach item=Endpoint from=$data}
            {$metrics = array(
                requests = $Endpoint->getCounterMetric('requests'),
                responseTime = $Endpoint->getAverageMetric('response-time', 'requests'),
                responsesExecuted = $Endpoint->getCounterMetric('responses-executed'),
                responsesCached = $Endpoint->getCounterMetric('responses-cached'),
            )}

            <article class="endpoint">
                <div class="key-metric good">
                    <strong>{$metrics.requests|number_format} request{tif $metrics.requests != 1 ? s}</strong>
                    <small>per {$sampleDurationString}</small>
                </div>
                <div class="details">
                    <header>
                        <h3 class="title">{endpoint $Endpoint}</h3>
                        {$externalEndpoint = $Endpoint->getExternalUrl()}
                        <dl class="endpoint-urls">
                            <dt class="external">External</dt>
                            <dd class="external"><a href="{$externalEndpoint|escape}">{$externalEndpoint|escape}</a>
                            <dt class="internal">Internal</dt>
                            <dd class="internal"><a class="endpoint-internal-url" href="{$Endpoint->InternalEndpoint|escape}">{$Endpoint->InternalEndpoint|escape}</a></dd>
                        </dl>
                    </header>
                    <ul class="other-metrics">
                        <li>
                            <strong>{tif $metrics.responseTime === null ? '&mdash;' : $metrics.responseTime|number_format} ms</strong>
                            <small>avg response time</small>
                        </li>
                        <li>
                            <strong>{$metrics.responsesExecuted|number_format} response{tif $metrics.responsesExecuted != 1 ? s}</strong>
                            <small>executed per {$sampleDurationString}</small>
                        </li>
                        <li>
                            <strong>{$metrics.responsesCached|number_format} response{tif $metrics.responsesCached != 1 ? s}</strong>
                            <small>hit cache per {$sampleDurationString}</small>
                        </li>
                        {*<li>
                            <strong>{$metrics.keys|number_format} key{tif $metrics.keys != 1 ? s}</strong>
                            <small>assigned</small>
                        </li>
                        <li>
                            <strong>{$metrics.clients|number_format}</strong>
                            <small>unique client{tif $metrics.clients != 1 ? s}</small>
                        </li>*}
                    </ul>
                </div>
                <footer>
                    <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}/edit">Edit</a>
                    <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-docs">View Docs</a>
                    <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-cache">View Cache</a>
                    <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-log">View Log</a>
                </footer>
            </article>
        {/foreach}

    </section>

{/block}