<?php

Emergence\People\Person::$relationships['Subscriptions'] = [
    'type' => 'one-many',
    'class' => Gatekeeper\Endpoints\Subscription::class
];