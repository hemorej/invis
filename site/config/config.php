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

c::set('license', 'put your license key here');

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
c::set('ssl',true);

/*

---------------------------------------
Stripe Configuration
---------------------------------------

*/

require_once(__DIR__ . '/../../site/plugins/stripe-php/init.php');
c::set('stripe_key_prv', 'sk_test_qT72b5ux3mRQblppmd9fSNT2');
c::set('stripe_key_pub', 'pk_test_xaHPLT96gz2Eg1xn6hwVCxa8');