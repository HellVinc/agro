<?php
return [
    'client_new' => [
        'type' => 1,
        'ruleName' => 'userRole',
    ],
    'client' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'client_new',
        ],
    ],
    'client_blocked' => [
        'type' => 1,
        'ruleName' => 'userRole',
    ],
    'admin' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'client',
        ],
    ],
];
