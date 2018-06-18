{extends designs/site.tpl}

{block "title"}Developer Portal{/block}

{block "js-bottom"}
    {$dwoo.parent}

    {if $.get.jsdebug}
        {sencha_bootstrap
            patchLoader=false
            packageRequirers=array('sencha-workspace/pages/src/page/Portal.js')
        }
    {else}
        <script src="{Site::getVersionedRootUrl('js/pages/Portal.js')}"></script>
    {/if}

    <script>
        Ext.require('Site.page.Portal');
    </script>
{/block}

{block "content"}
    <header class="page-header">
        <h1 class="header-title">Find an API</h1>
    </header>

    <form class="api-search">
        <input class="api-search-input" type="search">
    </form>

{*
    <section class="page-section">
        <header class="section-header">
            <h2 class="header-title">Browse by category</h2>
        </header>

        <ul class="category-grid">
            {loop array(
                'Agriculture',
                'Business',
                'Climate',
                'Consumer',
                'Ecosystems',
                'Education',
                'Energy',
                'Finance',
                'Health',
                'Local Government',
                'Manufacturing',
                'Ocean',
                'Public Safety',
                'Science & Research'
            )}
            <li class="category-grid-item">
                <a class="category-grid-link" href="/categories/{$|lower|whitespace:_|escape:url}">
                    <img class="category-grid-image" src="http://lorempixel.com/128/128?{$.loop.default.index}" width="64" height="64">
                    <div class="category-grid-title">{$|escape}</div>
                    <div class="category-grid-count">{mt_rand(1, 200)}</div>
                </a>
            </li>
            {/loop}
        </ul>
    </section>
*}

    <section class="page-section">
        <header class="section-header">
            <h2 class="header-title">
                {if $order == popularity}
                    Top APIs this week
                {elseif $order == alpha}
                    APIs by path
                {else}
                    Newest APIs
                {/if}
            </h2>
            <div class="header-buttons">
                <span class="button-group">
                    <label>Sort:</label>
                    <a class="button small {tif $order == popularity ? active}" href="?order=popularity">Popularity</a>
                    <a class="button small {tif $order == alpha ? active}" href="?order=alpha">Alpha</a>
                    <a class="button small {tif $order == newest ? active}" href="?order=newest">Newest</a>
                </span>
            </div>
        </header>

        <ul class="endpoint-list cardlist">
        {foreach item=Endpoint from=$data}
            <li class="endpoint-list-item cardlist-item" data-endpoint-id="{$Endpoint->ID}">
                {$avgResponseTime = $Endpoint->getAverageMetric('responseTime', 'requests')}

                {if $Endpoint->isDown()}
                    {$status = down}
                {elseif $avgResponseTime > 1000}
                    {$status = bad}
                {elseif $avgResponseTime > 150}
                    {$status = mid}
                {elseif $avgResponseTime}
                    {$status = good}
                {else}
                    {$status = idle}
                {/if}
                <div class="primary-metric {$status}">
                    <strong class="metric">
                        {if $status == down}DOWN
                        {else}
                            {if $avgResponseTime>99999}99K+
                            {elseif $avgResponseTime>9999}{floor(math('$avgResponseTime/1000'))}K+
                            {elseif $status == idle}&mdash;
                            {else}{$avgResponseTime|number_format}{/if}
                        {/if}
                    </strong>
                    {if $status != idle && $status != down}<span class="unit">ms</span>{/if}
                </div>
                <div class="endpoint-text">
                    <h3 class="endpoint-name">
                        <a class="endpoint-path" href="/api-docs/{$Endpoint->Path}">/{$Endpoint->Path|escape}</a>
                        <small class="endpoint-title">{$Endpoint->getTitle()}</small>
                    </h3>
                    <div class="endpoint-description">{$Endpoint->Description|escape|markdown}</div>
                </div>
            </li>
        {/foreach}
        </ul>
    </section>
{/block}