<?php

use League\OAuth2\Server\RequestEvent;
use MerchantOfComplexity\Oauth\Firewall\OauthScopesExpressionProvider;

return [

    'auth' => [
        /**
         * Fqcn Identity model related to Client and Access|Refresh token models
         * Will be booted on model in Service Provider
         * required
         */
        'identity_model' => '',

        /**
         * Register expression providers to the Default Expression Voter
         * @see \MerchantOfComplexity\Authters\Guard\Authorization\Voter\DefaultExpressionVoter
         *
         * the default one just provide as simple has_scope expression
         */
        'expression_providers' => [
            OauthScopesExpressionProvider::class
        ]
    ],

    'authorization_server' => [

        'private_key' => __DIR__ . '/private.key',

        'private_key_passphrase' => null,

        'encryption_key' => 'M/KNBn94/qTFVT+2DaS8VZHoTowgtainkgzVW4dcgM8=',

        'encryption_key_type' => 'plain', // defuse

        'access_token_ttl' => 'PT1H',

        'refresh_token_ttl' => 'P1M',

        'auth_code_ttl' => 'PT10M',

        'enable_grants' => [
            'client_credentials' => true,
            'password' => true,
            'refresh_token' => true,
        ],
    ],

    'resource_server' => [

        'public_key' => __DIR__ . '/public.key'
    ],

    'scopes' => [

    ],

    'listeners' => [

        RequestEvent::ACCESS_TOKEN_ISSUED => [],

        RequestEvent::REFRESH_TOKEN_ISSUED => [],

        RequestEvent::USER_AUTHENTICATION_FAILED => [],

        RequestEvent::CLIENT_AUTHENTICATION_FAILED => [],

        RequestEvent::REFRESH_TOKEN_CLIENT_FAILED => [],
    ]
];