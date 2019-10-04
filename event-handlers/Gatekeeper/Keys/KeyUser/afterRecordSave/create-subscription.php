<?php

$KeyUser = $_EVENT['Record'];

if ($KeyUser->isNew && !$KeyUser->Key->AllEndpoints) {
    foreach ($KeyUser->Key->Endpoints AS $Endpoint) {
        try {
            Gatekeeper\Endpoints\Subscription::create([
                'EndpointID' => $Endpoint->ID,
                'PersonID' => $KeyUser->PersonID
            ], true);
        } catch (\DuplicateKeyException $e) {
            continue;
        }
    }
}