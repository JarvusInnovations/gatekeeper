<?php

SearchRequestHandler::$searchClasses[Emergence\CMS\Page::class] = [
    'fields' => [
        'Title',
        [
            'field' => 'Handle',
            'method' => 'like'
        ]
    ],
    'conditions' => [
        'Class' => Emergence\CMS\Page::class,
        'Status' => 'Published',
        'Published IS NULL OR Published <= CURRENT_TIMESTAMP'
    ]
];

SearchRequestHandler::$searchClasses[Emergence\CMS\BlogPost::class] = [
    'fields' => [
        'Title',
        [
            'field' => 'Handle',
            'method' => 'like'
        ]
    ]
    ,'conditions' => [
        'Class' => Emergence\CMS\BlogPost::class,
        'Status' => 'Published',
        'Published IS NULL OR Published <= CURRENT_TIMESTAMP'
    ]
];