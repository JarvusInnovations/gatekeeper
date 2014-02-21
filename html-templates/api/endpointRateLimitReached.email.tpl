{capture assign=subject}{$Endpoint->Title|escape} v{$Endpoint->Version|escape} - Rate Limit Reached at {$.now|date_format:'%Y-%m-%d %H:%M:%S'} for {$bucket.seconds|number_format}s{/capture}
<html>
    <body>
        <p>The global rate limit has been reached for <a href="http://{$.server.HTTP_HOST}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} v{$Endpoint->Version|escape}</a>.

        <p>Access to this API has been temporarily disabled for the next {$bucket.seconds|number_format} seconds</p>
    </body>
</html>