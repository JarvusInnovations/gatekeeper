{extends designs/site.tpl}

{block title}Endpoints &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Endpoints</h2>
        <div class="header-buttons">
            <a class="button primary" href="/endpoints/create">Create Endpoint</a>
        </div>
    </header>

    <section class="endpoints trafficstack">
        <header>
            <input type="search" class="list-filter" placeholder="Filter endpoints&hellip;">

            <div class="trafficstack-col-headers">
                <form class="trafficstack-col-header title-col">
{*                     <span class="radio-set-label">Show:</span> *}
                    <label class="radio-set-item"><input type="radio" name="mode" value="requests" checked> <span class="label-text">Request Count</span></label>
                    <label class="radio-set-item"><input type="radio" name="mode" value="bytes"> <span class="label-text">Bytes Transferred</span></label>
                </form>

                <div class="trafficstack-col-header metric-secondary-col"><div class="header-text">Response Time (ms)</div></div>
                <div class="trafficstack-col-header metric-secondary-col"><div class="header-text">Cache Hit Ratio</div></div>
            </div>
        </header>
        {foreach item=Endpoint from=$data}
            <article class="endpoint trafficstack-row" {html_attributes_encode $Endpoint->getData() prefix="data-"}>
                <div class="summary">
                    <h3 class="title"><a href="{$Endpoint->getUrl()|escape}">/{$Endpoint->Path|escape}</a></h3>
                </div>

                <div class="details">
                    <div class="info internal-url">Internal URL: <a class="endpoint-internal-url" href="{$Endpoint->InternalEndpoint|escape}">{$Endpoint->InternalEndpoint|escape}</a></div>
                    <div class="buttons">
                        <a class="button" href="{$Endpoint->getURL('/edit')}">Edit</a>
                        <a class="button" href="/api-docs/{$Endpoint->Path}">View Docs</a>
                        <a class="button" href="{$Endpoint->getURL()}#endpoint-cache">View Cache</a>
                        <a class="button" href="{$Endpoint->getURL()}#endpoint-log">View Log</a>
                        <a class="button" href="mailto:endpoint-subscribers+{$Endpoint->Handle}@{Site::getConfig(primary_hostname)}">Email Subscribers</a>
                    </div>
                </div>
            </article>
        {/foreach}
    </section>

{/block}

{block "js-bottom"}
    {$dwoo.parent}

    {jsmin "pages/Endpoints.js"}

    <script>
        Ext.require('Site.page.Endpoints');
    </script>
{/block}