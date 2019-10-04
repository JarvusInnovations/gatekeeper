<?php

use Gatekeeper\ApiRequestHandler;
use Gatekeeper\Endpoints\Endpoint;


$GLOBALS['Session']->requireAccountLevel('Staff');
Site::$debug = true;
Site::$production = false;

?>

<style>
    tr.bad {
        background-color: #FDD;
    }
    tr.good {
        background-color: #DFD;
    }
    tr.neutral {
        background-color: #DDD;
    }
    td {
        font-family: monospace;
        white-space: pre-wrap;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<h1>Testable Endpoints</h1>

<table width="100%" border="1">
    <tr>
        <th>Path</th>
        <th>PingURI</th>
        <th>PingTestPattern</th>
        <th>Request URL</th>
        <th>Response Status</th>
        <th>Response Body</th>
        <th>Pattern Matched?</th>
    </tr>


    <?php foreach (Endpoint::getAll() as $Endpoint): ?>

        <?php
        if ($Endpoint->PingURI) {
            $url = rtrim($Endpoint->InternalEndpoint, '/') . '/' . ltrim($Endpoint->PingURI, '/');

            $response = HttpProxy::relayRequest([
                'autoAppend' => false,
                'autoQuery' => false,
                'url' => $url,
                'interface' => ApiRequestHandler::$sourceInterface,
                'timeout' => 15,
                'timeoutConnect' => 5,
                'returnResponse' => true
            ]);

            // evaluate success
            $testPassed =
                $response['info']['http_code'] == 200
                && (
                    !$Endpoint->PingTestPattern ||
                    preg_match($Endpoint->PingTestPattern, $response['body'])
                );
        } else {
            $url = null;
            $response = null;
            $testPassed = null;
        }
        ?>

        <tr class="<?=( $testPassed === null ? 'neutral' : ( $testPassed ? 'good' : 'bad' ) )?>">
            <td><?=$Endpoint->Path?></td>
            <td><?=$Endpoint->PingURI?></td>
            <td><?=$Endpoint->PingTestPattern?></td>
            <td><?=($url || '')?></td>
            <td><?=($response ? $response['info']['http_code'] : '')?></td>
            <td><?=($response ? (strlen($response['body']) . ' bytes') : '')?></td>
            <td><?=($testPassed === null ? '' : ($testPassed ? 'Y' : 'N'))?></td>
        </tr>
    <?php endforeach; ?>

</table>