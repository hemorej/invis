<?php

/*

---------------------------------------
License Setup
---------------------------------------

*/

c::set('license', '');

// environment
c::set('env', 'dev');

/*

---------------------------------------
Kirby Configuration
---------------------------------------

*/

c::set('timezone', 'America/Montreal');
c::set('debug', true);
c::set('cache', false);
c::set('cache.driver', 'file');
c::set('cache.autoupdate', true);
c::set('cache.ignore', array(
  'home',
  'feed',
  'prints/*'
));
c::set('ssl', false);


// google analytics
c::set('ga_code', '');

// mailgun
c::set('mailgun_domain', '');
c::set('mailgun_key', '');
c::set('alert_address', '');

/*

---------------------------------------
Stripe Configuration
---------------------------------------

*/

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../site/models/uidHandler.php');

c::set('stripe_key_prv', '');
c::set('stripe_key_pub', '');
c::set('paypal_key', '');


kirby()->hook('panel.page.*', function($page, $oldPage = null) {
	$stripeHandler = new \UidHandler();
	$stripeHandler->handle($page, $oldPage, $this->type());
});