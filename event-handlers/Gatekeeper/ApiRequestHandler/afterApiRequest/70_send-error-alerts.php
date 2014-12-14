<?php

namespace Gatekeeper;


$Endpoint = $_EVENT['request']->getEndpoint();


// send email alert if response code is 500+ and alerts are enabled
if ($_EVENT['responseCode'] >= 500 AND $Endpoint->AlertOnError) {
    ApiRequestHandler::sendAdminNotification($Endpoint, 'endpointError', array(
        'Transaction' => $_EVENT['Transaction']
        ,'responseHeaders' => $_EVENT['responseHeaders']
        ,'responseBody' => $_EVENT['responseBody']
    ), "endpoints/$Endpoint->ID/error-notification-sent");
}