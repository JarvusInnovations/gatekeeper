{extends designs/site.tpl}

{block title}Endpoints &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Endpoints</h2>
        <div class="header-buttons">
            <a class="button primary" href="/endpoints/create">Create Endpoint</a>
        </div>
    </header>

    <section class="endpoints">
        <header>
            <input type="search" class="list-filter" placeholder="Filter endpoints&hellip;">
        </header>
        {foreach item=Endpoint from=$data}
            <article class="endpoint" {html_attributes_encode $Endpoint->getData() prefix="data-"}>
                <div class="summary">
                    <h3 class="title"><a href="{$Endpoint->getUrl()|escape}">{$Endpoint->getExternalPath()|escape}</a></h3>
                </div>

                <div class="details">
                    <div class="info internal-url">Internal URL: <a class="endpoint-internal-url" href="{$Endpoint->InternalEndpoint|escape}">{$Endpoint->InternalEndpoint|escape}</a></div>
                    <div class="buttons">
                        <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}/edit">Edit</a>
                        <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-docs">View Docs</a>
                        <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-cache">View Cache</a>
                        <a class="button" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-log">View Log</a>
                    </div>
                </div>
            </article>
        {/foreach}
    </section>

{/block}

{block "js-bottom"}
    {$dwoo.parent}

    {if !$.get.jsdebug}
        <script src="{Site::getVersionedRootUrl('js/pages/Endpoints.js')}"></script>
    {/if}

    <script>
        Ext.require('Site.page.Endpoints');
    </script>
{/block}