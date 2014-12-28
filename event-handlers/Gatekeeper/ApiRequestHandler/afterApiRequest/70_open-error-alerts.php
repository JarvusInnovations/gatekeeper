<?php

namespace Gatekeeper;


$Endpoint = $_EVENT['request']->getEndpoint();


// send email alert if response code is 500+ and alerts are enabled
if ($_EVENT['responseCode'] >= 500 AND $Endpoint->AlertOnError) {
    Alerts\TransactionFailed::open($Endpoint, [
        'transactionId' => $_EVENT['Transaction']->ID,
        'request' => [
            'uri' => $_EVENT['request']->getUrl()
        ],
        'response' => [
            'status' => $_EVENT['responseCode'],
            'headers' => $_EVENT['responseHeaders'],
            'body' => $_EVENT['responseBody'],
            'bytes' => $_EVENT['Transaction']->ResponseBytes,
            'time' => $_EVENT['Transaction']->ResponseTime
        ]
    ]);
}