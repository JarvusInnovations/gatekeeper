{capture assign=subject}{$Endpoint->Title} v{$Endpoint->Version} - Error status {$Transaction->ResponseCode} at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}{/capture}
{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body>
        <p>
            The status code {$Transaction->ResponseCode} was received
            from <a href="http://{Site::getConfig('primary_hostname')}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} v{$Endpoint->Version|escape}</a>
            at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}.
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

        {if count($responseHeaders)}
            <h2>Response headers</h2>
            <pre>{foreach key=header item=value from=$responseHeaders implode="\n"}{$header|escape}: {$value|escape}{/foreach}</pre>
        {/if}

        <h2>Response body</h2>
        <pre>{$responseBody|escape}</pre>
    </body>
</html>