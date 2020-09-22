<?php

if ($GLOBALS['Session']->hasAccountLevel('Staff')) {
    SearchRequestHandler::$searchClasses[Gatekeeper\Endpoints\Endpoint::class] = [
        'fields' => [
            [
                'field' => 'Title',
                'method' => 'like'
            ],
            [
                'field' => 'Handle',
                'method' => 'like'
            ],
            [
                'field' => 'AdminName',
                'method' => 'like'
            ],
            [
                'field' => 'AdminEmail',
                'method' => 'like'
            ]
        ]
    ];

    SearchRequestHandler::$searchClasses[Gatekeeper\Keys\Key::class] = [
        'fields' => [
            [
                'field' => 'OwnerName',
                'method' => 'like'
            ],
            [
                'field' => 'Key',
                'method' => 'ContactName'
            ],
            [
                'field' => 'ContactEmail',
                'method' => 'like'
            ],
            [
                'field' => 'Key',
                'method' => 'like'
            ]
        ]
    ];

    SearchRequestHandler::$searchClasses[Gatekeeper\Bans\Ban::class] = [
        'fields' => [
            'Notes',
            [
                'field' => 'Notes',
                'method' => 'like'
            ],
            [
                'field' => 'IPPattern',
                'method' => 'like'
            ]
        ]
    ];
}