{$bucket = $Alert->Metadata.bucket}
{capture assign=subject}
    Alert {tif $Alert->Status == 'open' ? opened : $Alert->Status} for {$Alert->Endpoint->getTitle()}
    at {tif($Alert->Status == 'open' ? $Alert->Opened : $Alert->Closed)|date_format:'%Y-%m-%d %H:%M:%S'}
    -- Bandwidth limit exceeded
    {if $Alert->Status == 'open'}
        for {$bucket.seconds|number_format}s
    {/if}
{/capture}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body>
        <p>
            The global bandwidth limit has been exceeded
            for {endpoint $Alert->Endpoint useHostname=true}.
        </p>

        <p>
            Access to this API has been temporarily disabled for
            the next {$bucket.seconds|number_format} seconds
        </p>
    </body>
</html>