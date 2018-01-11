<?php

/*

---------------------------------------
License Setup
---------------------------------------

Please add your license key, which you've received
via email after purchasing Kirby on http://getkirby.com/buy

It is not permitted to run a public website without a
valid license key. Please read the End User License Agreement
for more information: http://getkirby.com/license

*/

c::set('license', '');

/*

---------------------------------------
Kirby Configuration
---------------------------------------

By default you don't have to configure anything to
make Kirby work. For more fine-grained configuration
of the system, please check out http://getkirby.com/docs/advanced/options

*/

c::set('timezone', 'America/Montreal');
c::set('debug', false);
c::set('cache', true);
c::set('cache.driver', 'file');
c::set('cache.autoupdate',true);
c::set('cache.ignore', array(
  'home',
  'feed'
));
c::set('ssl', true);

c::set('ga_code', '');

/*

---------------------------------------
Stripe Configuration
---------------------------------------

*/

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../site/models/uidHandler.php');

c::set('stripe_key_prv', '');
c::set('stripe_key_pub', '');


kirby()->hook('panel.page.*', function($page, $oldPage = null) {
	$stripeHandler = new \UidHandler();
	$stripeHandler->handle($page, $oldPage, $this->type());
});