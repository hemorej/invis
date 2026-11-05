<?php

use Kirby\Cms\App;
use Kirby\Uuid\Uuid;

App::plugin( 'autoranduuid/autoranduuid', [
	'fields' => [
		'autoranduuid' => [
			'props' => [
				// Generates a fresh UUID for the field; the incoming blueprint value is unused.
				'autoranduuid' => fn( mixed $autoranduuid = null ): string => Uuid::generate(),
			],
		],
	],
] );
