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
	'afbora.blade.minify.enabled' => true,

	'ga_code' => '',

	'mailgun_domain' => '',
	'mailgun_key' => '',
	'alert_address' => '',
	'from_address' => '',

	'stripe_key_pub' => '',
	'stripe_key_prv' => '',
	'paypal_client_id' => '',
	'paypal_client_secret' => '',
	'paypal_environment' => '',
	'fixer_key' => '',
	'ipstack_key' => '',

	'cacheTTL' => '14400',

	'bnomei.autoid.generator' => function(){ return Bnomei\AutoID::getToken(15, true, true, true); },

	'hooks' => require_once 'hooks.php'
];