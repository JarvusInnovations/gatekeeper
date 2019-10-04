# Strip OAuth

This example beforeApiRequest handler could be saved to a path like
`event-handlers/Gatekeeper/ApiRequestHandler/beforeApiRequest/99-myendpoint-oauth.php` to strip
oAuth from an endpoint right before the request is sent.

Given a client id, client secret, and refresh token, it will handle getting a new access token
as often as needed and caching it in shared memory while it's valid.

```
<?php

// only run this handler for a specific endpoint
if ($_EVENT['request']->getEndpoint()->Handle != 'myendpoint') {
    return;
}


// get oauth access token
if (false === ($accessToken = \Cache::fetch('wrike-access-token'))) {
    $ch = curl_init('https://www.example.com/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'client_id' => 'CLIENT_ID_HERE',
        'client_secret' => 'CLIENT_SECRET_HERE',
        'grant_type' => 'refresh_token',
        'refresh_token' => 'REFRESH_TOKEN_HERE'
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);

    if (!$accessToken = $response['access_token']) {
        throw new \Exception('Failed to retrieve access token');
    }

    \Cache::store('wrike-access-token', $accessToken, $response['expires_in'] - 100);
}


// append access token to request
$_EVENT['request']->addHeader('Authorization: bearer ' . $accessToken);
```

Be sure to replace these placeholders with your own values:
- **myendpoint**
- **CLIENT_ID_HERE**
- **CLIENT_SECRET_HERE**
- **REFRESH_TOKEN_HERE**