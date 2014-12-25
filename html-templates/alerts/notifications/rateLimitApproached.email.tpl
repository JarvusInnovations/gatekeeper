{$bucket = $Alert->Metadata.bucket}
{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Rate limit will be reached
    {if $Alert->Status == 'open'}
        with {$bucket.hits} hits in {$bucket.seconds|number_format}s
    {/if}
{/capture}
<html>
    <body>
        <p>
            The global rate limit is being approached for
            <a href="http://{Site::getConfig('primary_hostname')}{$Alert->Endpoint->getURL()}">{$Alert->Endpoint->getTitle()|escape}</a>.
        </p>

        <p>
            Access to this API will be temporarily disabled if another {$bucket.hits}
            requests are made within {$bucket.seconds|number_format} seconds.
        </p>
    </body>
</html>