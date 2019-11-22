<?php

return [
	'env' => 'dev',

	'timezone' => 'America/Montreal',
	'debug' => true,
	'cache' => false,
	'cache.driver' => 'file',
	'cache.autoupdate' => true,
	'cache.ignore' => array(
	  'home',
	  'feed',
	  'prints/*'
	),
	'cache_path' => __DIR__ . '/../../cache/',

	'ga_code' => '',

	'mailgun_domain' => '',
	'mailgun_key' => '',
	'alert_address' => '',

	'stripe_key_pub' => '',
	'stripe_key_prv' => '',
	'paypal_client_id' => '',
	'paypal_client_secret' => '',
	'fixer_key' => '',
	'ipstack_key' => ''
];