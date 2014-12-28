<?php

namespace Gatekeeper;

use Gatekeeper\Alerts\TransactionFailed;
use Gatekeeper\Alerts\ResponseTimeLimitExceeded;


$Endpoint = $_EVENT['request']->getEndpoint();


// send email alert if response code is 500+ and alerts are enabled
if ($_EVENT['responseCode'] >= 500 AND $Endpoint->AlertOnError) {
    TransactionFailed::open($Endpoint, [
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
} elseif ($_EVENT['responseCode'] == 0 && $_EVENT['curlError'] == CURLE_OPERATION_TIMEOUTED) {
    ResponseTimeLimitExceeded::open($Endpoint, [
        'transactionId' => $_EVENT['Transaction']->ID,
        'request' => [
            'uri' => $_EVENT['request']->getUrl()
        ],
        'response' => [
            'time' => round($_EVENT['curlInfo']['total_time'] * 1000)
        ]
    ]);
}