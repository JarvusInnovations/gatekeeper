{capture assign=subject}{$Endpoint->Title|escape} v{$Endpoint->Version|escape} - Rate Limit will be reached in {$bucket.hits} hits in {$bucket.seconds|number_format}s{/capture}
<html>
    <body>
        <p>The global rate limit is being approached for <a href="http://{$.server.HTTP_HOST}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} v{$Endpoint->Version|escape}</a>.

        <p>Access to this API will be temporarily disabled if another {$bucket.hits} requests are made within {$bucket.seconds|number_format} seconds</p>
    </body>
</html>