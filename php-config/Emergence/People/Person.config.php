<?php

Emergence\People\Person::$relationships['Subscriptions'] = [
    'type' => 'one-many',
    'class' => Gatekeeper\Endpoints\Subscription::class
];

Emergence\People\Person::$relationships['Keys'] = [
    'type' => 'many-many',
    'class' => Gatekeeper\Keys\Key::class,
    'linkClass' => Gatekeeper\Keys\KeyUser::class
];