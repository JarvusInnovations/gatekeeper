{extends designs/site.tpl}

{block "title"}System Status &mdash; {$dwoo.parent}{/block}

{block "js-bottom"}
    {$dwoo.parent}
    
    <script>
        window.SiteEnvironment = window.SiteEnvironment || { };
        window.SiteEnvironment.gkEndpoints = {JSON::translateObjects(Gatekeeper\Endpoints\Endpoint::getAll(), true)|json_encode};
    </script>

    {if !$.get.jsdebug}
        <script src="{Site::getVersionedRootUrl('js/pages/Status.js')}"></script>
    {/if}

    <script>
        Ext.require('Site.page.Status');
    </script>

    {literal}
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    {/literal}
{/block}

{block "content"}
    <header class="page-header">
        <h2 class="header-title">System Status (Demo)</h2>
    </header>

    <section id="cache-status">
        <h3>Cache status</h3>
    </section>

    <section id="top-endpoints-requests">
        <h3>Top 5 endpoints this month &mdash; requests per hour</h3>
    </section>

    <section id="top-endpoints-bytes">
        <h3>Top 5 endpoints this month &mdash; bytes transferred from origin server per hour</h3>
    </section>

    <section id="top-endpoints-time">
        <h3>Top 5 endpoints this month &mdash; average origin server response time (ms)</h3>
    </section>
{/block}