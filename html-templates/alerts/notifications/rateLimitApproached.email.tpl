{$bucket = $Alert->Metadata.bucket}
{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    (Approaching rate limit, #{$Alert->ID})
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
        <h2>Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status}:</h2>
        <blockquote>
            <p>
                The global rate limit is being <strong>approached</strong>
                for <strong>{endpoint $Alert->Endpoint useHostname=true smallify=no}</strong>.
            </p>
    
            <p>
                Access to this API will be temporarily disabled if another <strong>{$bucket.hits}
                requests</strong> are made within <strong>{$bucket.seconds|number_format} seconds</strong>.
            </p>
        </blockquote>
    </body>
</html>