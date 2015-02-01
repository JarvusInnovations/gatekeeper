{extends designs/site.tpl}

{block "title"}Docs &mdash; {$dwoo.parent}{/block}

{block "branding"}
<div class="site-branding title-snug">
    <a href="/">
        <div class="text">{$.server.HTTP_HOST}</div>
    </a>
</div>
{/block}
{block "header-bottom"}{/block}

{block "js-bottom"}
    {$dwoo.parent}


{/block}

{block "content"}
    <div class="split-view">
        <div class="nav-view">
            <ul class="docs-toc">
                <li><a class="current" shref="#overview">Overview</a></li>
                <li><a href="#keys">API Keys</a></li>
                <li><a href="#paths">Paths</a></li>
                <li>
                    <a href="#models">Models</a>
                    <ul>
                        <li><a href="#models/vehicle">Vehicle</a></li>
                        <li><a href="#models/route">Route</a></li>
                        <li><a href="#models/stop">Stop</a></li>
                    </ul>
                </li>
                <li><a href="#community">Community Code &amp; Uses</a></li>
            </ul>
        </div>
    
        <div class="detail-view">
            <header class="page-header" id="overview">
                {$Endpoint = Gatekeeper\Endpoints\EndpointsRequestHandler::getRecordByHandle('livenote-v1')}
                <h2 class="header-title">{endpoint $Endpoint}</h2>
                <div class="header-buttons">
                    <label class="toggle"><input type="checkbox"> Subscribe</label>
                </div>
            </header>
        
            <p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dapibus ante nec dolor tincidunt, in euismod augue molestie. Duis ut tortor suscipit, feugiat est eu, semper ipsum. Integer vehicula lorem eget purus ultricies pellentesque. Phasellus pellentesque vitae enim vel dignissim. Sed condimentum urna ultricies efficitur lobortis. Fusce egestas eros maximus, lobortis velit a, sagittis augue. </p>
        
            <section class="page-section" id="keys">
                <header class="section-header">
                    <h2 class="header-title">API Keys</h2>
                    <div class="header-buttons">
                        <a class="button primary" href="/keys/create">Create</a>
                </header>
        
                {$Keys = Gatekeeper\Keys\Key::getAll()}
                {foreach item=Key from=$Keys}
                    {$metrics = array(
                        callsTotal = $Key->getMetric(calls-total)
                        ,callsWeek = $Key->getMetric(calls-week)
                        ,callsDayAvg = $Key->getMetric(calls-day-avg)
                        ,endpoints = tif($Key->AllEndpoints, null, $Key->getMetric(endpoints))
                    )}
                    <article class="key">
                        <div class="primary-metric"><strong>{$metrics.callsTotal|number_format} call{tif $metrics.callsTotal != 1 ? s}</strong> all time</div>
                        <div class="details">
                            <header>
                                <h3 class="title">{apiKey $Key}</h3>
                                <div class="meta owner">{if $Key->ContactEmail}
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
                                <li><strong>{$metrics.callsWeek|number_format} call{tif round($metrics.callsWeek) != 1 ? s}</strong> this week</li>
                                <li><strong>{$metrics.callsDayAvg|number_format} call{tif round($metrics.callsDayAvg) != 1 ? s}</strong> avg per day</li>
                                <li><strong>{tif $Key->AllEndpoints ? 'All' : $metrics.endpoints|number_format} endpoint{tif $Key->AllEndpoints || $metrics.endpoints != 1 ? s}</strong> permitted</li>
                            </ul>
                        </div>
                        <footer>
                            <a class="button" href="{$Key->getURL()}/share">Share</a>
                            <a class="button destructive" href="{$Key->getURL()}/delete">Delete</a>
                        </footer>
                    </article>
                {/foreach}
            </section>
        
            <section class="page-section" id="paths">
                <header class="section-header">
                    <h2 class="header-title">Paths</h2>
                </header>
        
                <div id="swagger-ct" class="swagger-section"></div>
            </section>
        </div>
    </div>
{/block}