<?php

return [
    'env' => 'dev',

    'debug' => true,
    'cache' => [
        'pages' => [
            'active'  => true,
            'type'    => 'static',
            'root'    => __DIR__ . '/../cache/',
            'headers' => true,
            'ignore' => [
                'home',
                'feed',
                'prints/*'
            ]
        ]
    ],
    'afbora.blade.minify.enabled' => false,

    'thumbs' => [
        'quality'   => 90,
        'srcsets' => [
            'default' => [600, 800, 1200],
            'vertical' => [ '400w' => ['height' => 600], '600w' => ['height' => 600], '800w' => ['height' => 600]]
        ]
    ],

    'mailgun_domain' => '',
    'mailgun_key' => '',
    'alert_address' => '',
    'from_address' => '',
    'reply-to_address' => '',

    'stripe_key_pub' => '',
    'stripe_key_prv' => '',
    'webhook_secret' => '',
    'paypal_client_id' => '',
    'paypal_client_secret' => '',
    'paypal_environment' => '',
    'fixer_key' => '',
    'ipapi_key' => '',

    'cacheTTL' => '14400',

    'auth' => [
        'trials' => 5,
        'challenges' => ['totp', 'email'],
        'methods' => [
            'password' => ['2fa' => true]
        ]
    ],

    'hooks' => require_once 'hooks.php'
];