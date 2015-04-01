{extends designs/site.tpl}

{block "title"}System Status &mdash; {$dwoo.parent}{/block}

{block "js-bottom"}
    {$dwoo.parent}
    
    <script>
        window.SiteEnvironment = window.SiteEnvironment || { };
        {*window.SiteEnvironment.gkEndpoints = {JSON::translateObjects(Gatekeeper\Endpoints\Endpoint::getAll(), true)|json_encode};*}
        {literal}
        window.SiteEnvironment.gkEndpoints = [{"ID":34,"Title":"Polling Places"},{"ID":32,"Title":"ULRS Rest"},{"ID":31,"Title":"OPA Property Data"},{"ID":30,"Title":"OPA Property Data (Staging)"},{"ID":29,"Title":"ULRS Rest Stage"},{"ID":28,"Title":"Open 311 (Prod)"},{"ID":27,"Title":"Open 311 (Staging)"},{"ID":26,"Title":"Open 311 (Test)"},{"ID":25,"Title":"ArcGIS"},{"ID":24,"Title":"Payments"},{"ID":23,"Title":"L&I Legacy"},{"ID":22,"Title":"GateKeeper Test: Status"},{"ID":21,"Title":"L&I Release (staging)"},{"ID":20,"Title":"Real Estate Tax (staging)"},{"ID":19,"Title":"Authentication Service (staging)"},{"ID":18,"Title":"L&I Beta (staging)"},{"ID":16,"Title":"Authentication Service"},{"ID":15,"Title":"Part I Crime Incidents"},{"ID":14,"Title":"ULRS311"},{"ID":8,"Title":"L&I Release"},{"ID":6,"Title":"PHL Flight Info API"},{"ID":3,"Title":"L&I Beta"},{"ID":2,"Title":"OPA Property Data"}];
        {/literal}
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

    <p><strong>These charts are currently hard-wired to pull data from developer.phila.gov to provide a realistic demonstration &mdash; ensure you're logged into a staff account at <a href="http://developer.phila.gov" target="_blank">http://developer.phila.gov</a></strong></p>

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