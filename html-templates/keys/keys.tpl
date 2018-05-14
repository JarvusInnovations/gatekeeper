{extends designs/site.tpl}

{block title}Keys &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Keys</h2>
        <div class="header-buttons">
            <a class="button primary" href="/keys/create">Create Key</a>
        </div>
    </header>

    <section class="keys trafficstack">
        <header>
            <input type="search" class="list-filter" placeholder="Filter keys&hellip;">

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
        {foreach item=Key from=$data}
            <article class="key trafficstack-row" {html_attributes_encode $Key->getData() prefix="data-"}>
                <div class="summary">
                    <h3 class="title">{apiKey $Key}</h3>
                </div>

                <div class="details">
                    <div class="info contact">
                        Contact:
                        {if $Key->ContactEmail}
                            {$recipient = $Key->ContactEmail}
                            {if $Key->ContactName}
                                {$recipient = "$Key->ContactName <$recipient>"}
                            {/if}

                            <a href="mailto:{$recipient|escape}" title="Contact key owner">{$recipient|escape}</a>
                        {elseif $Key->ContactName}
                            {$Key->ContactName}
                        {/if}
                    </div>
                    <div class="buttons">
                        <a class="button" href="{$Key->getUrl('/edit')}">Edit</a>
                        <a class="button" href="/bans/create?KeyID={$Key->Key}">Suspend</a>
                        <a class="button" href="{$Key->getUrl()}#key-log">View Log</a>
                    </div>
                </div>
            </article>
        {/foreach}
    </section>

{/block}

{block "js-bottom"}
    {$dwoo.parent}

    {if $.get.jsdebug}
        {sencha_bootstrap
            patchLoader=false
            packageRequirers=array('sencha-workspace/pages/src/abstractpage/TrafficStack.js', 'sencha-workspace/pages/src/pages/Keys.js')
        }
    {else}
        <script src="{Site::getVersionedRootUrl('js/pages/Keys.js')}"></script>
    {/if}

    <script>
        Ext.require('Site.page.Keys');
    </script>
{/block}