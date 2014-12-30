{capture assign=subject}
    {if $Alert->Status == 'open'}
        New alert opened
    {else}
        Alert {$Alert->Status}
    {/if}

    {if $Alert->Endpoint}
        for {$Alert->Endpoint->getTitle()}
    {/if}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
{/capture}
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
        <table>
            <tbody>
                <tr><th>ID</th><td>{$Alert->ID}</td></tr>
                <tr><th>Class</th><td>{$Alert->Class|regex_replace:'/^(.+\\\\)([^\\\\]+)\$/':'<small>\$1</small><strong>\$2</strong>'}</td></tr>
                <tr><th>Details URL</th><td><a href="http://{Site::getConfig(primary_hostname)}{$Alert->getUrl()}">{$Alert->getUrl()}</a></td></tr>
                <tr><th>Status</th><td>{$Alert->Status}</td></tr>
                <tr><th>Opened</th><td>{$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}</td></tr>
                <tr><th>Closed</th><td>{$Alert->Closed|date_format:'%Y-%m-%d %H:%M:%S'|default:'&mdash;'}</td></tr>
    
                {if $Alert->Repetitions}
                    <tr><th>Repetitions</th><td>{$Alert->Repetitions|number_format}</td></tr>
                {/if}
    
                <tr><th>Acknowledger</th><td>{tif $Alert->Acknowledger ? $Alert->Acknowledger->FullName : '&mdash;'}</td></tr>
    
                {if $Alert->Endpoint}
                    <tr><th>Endpoint</th><td>{endpoint $Alert->Endpoint useHostname=true}</td></tr>
                {/if}
    
                {if $Alert->Metadata}
                    <tr><th>Alert Metadata</th>
                    <td><pre>{$Alert->Metadata|print_r:true|escape}</pre></td></tr>
                {/if}
            </tbody>
        </table>
    </body>
</html>