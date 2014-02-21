{capture assign=subject}{$Endpoint->Title|escape} v{$Endpoint->Version|escape} - Error status {$LoggedRequest->ResponseCode} at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}{/capture}
<html>
    <body>
        <p>The status code {$LoggedRequest->ResponseCode} was received from <a href="http://{$.server.HTTP_HOST}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} v{$Endpoint->Version|escape}</a> at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}.

        {if count($responseHeaders)}
            <h2>Response headers</h2>
            <pre>{foreach key=header item=value from=$responseHeaders implode="\n"}{$header|escape}: {$value|escape}{/foreach}</pre>
        {/if}

        <h2>Response body</h2>
        <pre>{$responseBody|escape}</pre>
    </body>
</html>