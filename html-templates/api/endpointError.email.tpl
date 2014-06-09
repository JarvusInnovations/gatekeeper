{capture assign=subject}{$Endpoint->Title} v{$Endpoint->Version} - Error status {$LoggedRequest->ResponseCode} at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}{/capture}
{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body
        <p>The status code {$LoggedRequest->ResponseCode} was received from <a href="http://{Site::getConfig('primary_hostname')}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} v{$Endpoint->Version|escape}</a> at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}.

        <dl>
            {if $LoggedRequest->Key}
                <dt>Key</dt><dd>{apiKey $LoggedRequest->Key useHostname=true}</dd>
            {/if}
            <dt>Endpoint</dt><dd>{endpoint $LoggedRequest->Endpoint useHostname=true}</dd>
            <dt>Client IP</dt><dd>{$LoggedRequest->ClientIP|long2ip}</dd>
            <dt>HTTP method</dt><dd>{$LoggedRequest->Method}</dd>
            <dt>Path</dt><dd>{$LoggedRequest->Path|escape}</dd>
            <dt>Query</dt><dd>{$LoggedRequest->Query|escape}</dd>
            <dt>Response Time</dt><dd>{$LoggedRequest->ResponseTime|number_format} ms</dd>
            <dt>Response Bytes</dt><dd>{$LoggedRequest->ResponseBytes|number_format} B</dd>
        </dl>

        {if count($responseHeaders)}
            <h2>Response headers</h2>
            <pre>{foreach key=header item=value from=$responseHeaders implode="\n"}{$header|escape}: {$value|escape}{/foreach}</pre>
        {/if}

        <h2>Response body</h2>
        <pre>{$responseBody|escape}</pre>
    </body>
</html>