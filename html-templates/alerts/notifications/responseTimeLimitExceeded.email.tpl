{$Transaction = Gatekeeper\Transactions\Transaction::getByID($Alert->Metadata.transactionId)}

{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Request timed out
{/capture}

{load_templates "subtemplates/keys.tpl"}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <head>
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
        </style>
    </head>
    <body>
        <p>
            A request to
            <a href="http://{Site::getConfig('primary_hostname')}{$Alert->Endpoint->getURL()}">{$Alert->Endpoint->getTitle()|escape}</a>
            timed out at {$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}:
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
                    <td>{endpoint $Transaction->Endpoint useHostname=true smallify=no}</td>
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
                    <th>Wait Time</th>
                    <td>{$Alert->Metadata.response.time|number_format} ms</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>