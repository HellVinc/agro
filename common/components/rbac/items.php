<?php
return [
    'client' => [
        'type' => 1,
        'description' => 'Client',
        'ruleName' => 'userRole',
    ],
    'admin' => [
        'type' => 1,
        'description' => 'Admin',
        'ruleName' => 'userRole',
        'children' => [
            'client',
        ],
    ],
];
