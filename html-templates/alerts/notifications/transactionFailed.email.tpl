{$Transaction = Gatekeeper\Transactions\Transaction::getByID($Alert->Metadata.transactionId)}

{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Error status {$Transaction->ResponseCode}
{/capture}

{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <head>
        <meta name="viewport" content="width=device-width">
        <style>
            body {
                background-color: #f7f3ed;
                color: #333333;
            }

            a {
                color: #4771a6;
            }

            .key-string {
                color: #888888;
                font-family: monospace;
                font-size: 100%;
            }

            th {
                padding-right: 1em;
                text-align: right;
            }

            table {
                font-size: 100%;
            }

            h4 {
                margin: 1em 0 1em 0;
            }

            pre {
                font-family: Consolas, 'Lucida Console', monospace;
                font-size: 100%;
                word-wrap: break-word;
            }
        </style>
    </head>
    <body>
        <p>
            The status code {$Transaction->ResponseCode} was received
            from <a href="http://{Site::getConfig('primary_hostname')}{$Alert->Endpoint->getURL()}">{$Alert->Endpoint->getTitle()|escape}</a>
            at {$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}.
        </p>

        <table>
            <tbody>
                {if $Transaction->Key}
                    <tr>
                        <th>Key</th>
                        <td>{apiKey $Transaction->Key useHostname=true}</td>
                    </tr>
                {/if}
                <tr>
                    <th>Endpoint</th>
                    <td>{endpoint $Transaction->Endpoint useHostname=true}</td>
                </tr>
                <tr>
                    <th>Client IP</th>
                    <td>{$Transaction->ClientIP|long2ip}</td>
                </tr>
                <tr>
                    <th>HTTP method</th>
                    <td>{$Transaction->Method}</td>
                </tr>
                <tr>
                    <th>Path</th>
                    <td>{$Transaction->Path|escape}</td>
                </tr>
                <tr>
                    <th>Query</th>
                    <td>{$Transaction->Query|escape}</td>
                </tr>
                <tr>
                    <th>Response Time</th>
                    <td>{$Transaction->ResponseTime|number_format} ms</td>
                </tr>
                <tr>
                    <th>Response Bytes</th>
                    <td>{$Transaction->ResponseBytes|number_format} B</td>
                </tr>
            </tbody>
        </table>

        {if count($Alert->Metadata.response.headers)}
            <hr>
            <h4>Response Headers</h4>
            <pre>{foreach key=header item=value from=$Alert->Metadata.response.headers implode="\n"}{$header|escape}: {$value|escape}{/foreach}</pre>
        {/if}

        {if $Alert->Metadata.response.body|escape}
            <hr>
            <h4>Response Body</h4>
            <pre>{$Alert->Metadata.response.body|escape}</pre>
        {/if}
    </body>
</html>