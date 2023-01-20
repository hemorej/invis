<?php

use Kirby\Cms\App as Kirby;
use Kirby\Uuid\Uuid;

Kirby::plugin('autoranduuid/autoranduuid', [
    'fields' => [
        'autoranduuid' => [
            'props' => [
                'autoranduuid' => function ($autoranduuid) {
                    return Uuid::generate();
                }
            ]
        ]
    ]
]);