<?php

return [
    'env' => 'dev',

    'debug' => true,
    'cache' => false,
    'cache.driver' => 'file',
    'cache.autoupdate' => true,
    'cache.ignore' => array(
        'home',
        'feed',
        'prints/*'
    ),
    'cache.backend' => true,
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
    'ipstack_key' => '',

    'cacheTTL' => '14400',

    'bnomei.autoid.generator' => function(){ return (new \Bnomei\NanoGenerator())->generate(); },
    'auth' => [
        'trials' => 5,
        'challenges' => ['totp', 'email'],
        'methods' => [
            'password' => ['2fa' => true]
        ]
    ],

    'hooks' => require_once 'hooks.php'
];