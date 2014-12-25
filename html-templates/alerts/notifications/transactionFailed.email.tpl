{$Transaction = Gatekeeper\Transaction::getByID($Alert->Metadata.transactionId)}

{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Error status {$Transaction->ResponseCode}
{/capture}

{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body>
        <p>
            The status code {$Transaction->ResponseCode} was received
            from <a href="http://{Site::getConfig('primary_hostname')}{$Alert->Endpoint->getURL()}">{$Alert->Endpoint->getTitle()|escape}</a>
            at {$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}.
        </p>

        <dl>
            {if $Transaction->Key}
                <dt>Key</dt><dd>{apiKey $Transaction->Key useHostname=true}</dd>
            {/if}
            <dt>Endpoint</dt><dd>{endpoint $Transaction->Endpoint useHostname=true}</dd>
            <dt>Client IP</dt><dd>{$Transaction->ClientIP|long2ip}</dd>
            <dt>HTTP method</dt><dd>{$Transaction->Method}</dd>
            <dt>Path</dt><dd>{$Transaction->Path|escape}</dd>
            <dt>Query</dt><dd>{$Transaction->Query|escape}</dd>
            <dt>Response Time</dt><dd>{$Transaction->ResponseTime|number_format} ms</dd>
            <dt>Response Bytes</dt><dd>{$Transaction->ResponseBytes|number_format} B</dd>
        </dl>

        {if count($Alert->Metadata.response.headers)}
            <h2>Response headers</h2>
            <pre>{foreach key=header item=value from=$Alert->Metadata.response.headers implode="\n"}{$header|escape}: {$value|escape}{/foreach}</pre>
        {/if}

        <h2>Response body</h2>
        <pre>{$Alert->Metadata.response.body|escape}</pre>
    </body>
</html>