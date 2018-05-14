<?php

SearchRequestHandler::$searchClasses[Tag::class] = [
    'fields' => [
        'Title',
        [
            'field' => 'Handle',
            'method' => 'like'
        ]
    ]
];