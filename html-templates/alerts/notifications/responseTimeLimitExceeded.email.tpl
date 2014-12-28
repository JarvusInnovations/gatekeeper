{$Transaction = Gatekeeper\Transactions\Transaction::getByID($Alert->Metadata.transactionId)}

{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Request timed out
{/capture}

{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body>
        <p>
            A request to
            <a href="http://{Site::getConfig('primary_hostname')}{$Alert->Endpoint->getURL()}">{$Alert->Endpoint->getTitle()|escape}</a>
            timed out at {$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}.
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
            <dt>Wait Time</dt><dd>{$Alert->Metadata.response.time|number_format} ms</dd>
        </dl>
    </body>
</html>