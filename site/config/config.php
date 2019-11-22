<?php

return [
	'env' => function(){ return env('APP_ENV');},

	'timezone' => 'America/Montreal',
	'debug' => true,
	'cache' => function(){ return env('APP_CACHE');},
	'cache.driver' => 'file',
	'cache.autoupdate' => true,
	'cache.ignore' => array(
	  'home',
	  'feed',
	  'prints/*'
	),
	'cache_path' => function(){ return env('CACHE_PATH');},

	'ga_code' => function(){ return env('GA_CODE');},

	'mailgun_domain' => function(){ return env('MAILGUN_DOMAIN');},
	'mailgun_key' => function(){ return env('MAILGUN_KEY');},
	'alert_address' => function(){ return env('NOTIF_ADDRESS');},

	'stripe_key_pub' => function(){ return env('STRIPE_PUB_KEY');},
	'stripe_key_prv' => function(){ return env('STRIPE_PRV_KEY');},
	'paypal_client_id' => function(){ return env('PAYPAL_CLIENT_ID');},
	'paypal_client_secret' => function(){ return env('PAYPAL_CLIENT_SECRET');},
	'fixer_key' => function(){ return env('FIXER_KEY');},
	'ipstack_key' => function(){ return env('IPSTACK_KEY'); }
];