<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('cart/cart', [
  'options' => [
    'cache.backend' => true
  ],
  	'routes' => [
	  [
		'pattern' => 'address',
		'method' => 'POST',
		'action'  => function () {
			if(csrf(get('csrf')) === true){
				$customer = array(
					'name' => get('name'),
					'email' => get('email'),
						'address' => array(
							"address_line_1" => get('line1'),
							"address_line_2" => get('line2'),
							"city" => get('city'),
							"country" => get('country'),
							"postal_code" => get('postcode'),
							"state" => get('province')
						)
					);

				kirby()->impersonate('kirby');
				page(kirby()->session()->get('txn'))->update(['customer' => Yaml::encode($customer)]);

				return [
			      'status' => 'ok'
			    ];
			}
		}
	  ],[
		'pattern' => 'order/success/(:alpha)',
		'method' => 'GET|POST',
		'action'  => function ($alpha) {
			if($alpha == 'stripe'){
				(new \Cart\Cart())->processStripe();
				return page('prints/order');
			}elseif($alpha == 'paypal'){
				(new \Cart\Cart())->processPaypal();
				return page('prints/order');
			}

			return page('prints/cart');
		}]
	]
]);