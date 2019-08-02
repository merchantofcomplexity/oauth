<?php

return [

    'authorization_server' => [

        'private_key' => __DIR__ . '/private.key',

        'private_key_passphrase' => null,

        'encryption_key' => 'M/KNBn94/qTFVT+2DaS8VZHoTowgtainkgzVW4dcgM8=',

        'encryption_key_type' => 'plain', // defuse

        'access_token_ttl' => 'PT5H',

        'refresh_token_ttl' => 'P1M',

        'enable_client_credentials_grant' => true,

        'enable_password_grant' => true,

        'auth_code_ttl' => 'PT10M'
    ],

    'resource_server' => [

        'public_key' => __DIR__ . '/public.key'
    ],

    'scopes' => [

    ]
];