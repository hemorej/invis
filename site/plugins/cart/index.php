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
			$logger = ( new \Logger\Logger( 'cart' ) )->getLogger();

			if( csrf( get( 'csrf' ) ) !== true ) {
				$logger->warning( "address submission rejected, invalid CSRF token", ['txn' => kirby()->session()->get( 'txn' )] );
				return ['status' => 'error', 'message' => 'Invalid CSRF token'];
			}

			$name     = substr( trim( get( 'name' ) ?? '' ), 0, 100 );
			$email    = substr( trim( get( 'email' ) ?? '' ), 0, 254 );
			$line1    = substr( trim( get( 'line1' ) ?? '' ), 0, 200 );
			$line2    = substr( trim( get( 'line2' ) ?? '' ), 0, 200 );
			$city     = substr( trim( get( 'city' ) ?? '' ), 0, 100 );
			$country  = substr( trim( get( 'country' ) ?? '' ), 0, 100 );
			$postcode = substr( trim( get( 'postcode' ) ?? '' ), 0, 20 );
			$province = substr( trim( get( 'province' ) ?? '' ), 0, 100 );

			if( empty( $name ) || empty( $line1 ) || empty( $city ) || empty( $country ) || empty( $postcode )
				|| !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$logger->warning( "address submission rejected, invalid address data", ['txn' => kirby()->session()->get( 'txn' )] );
				return ['status' => 'error', 'message' => 'Invalid address data'];
			}

			$customer = [
				'name'  => $name,
				'email' => $email,
				'address' => [
					'address_line_1' => $line1,
					'address_line_2' => $line2,
					'city'           => $city,
					'country'        => $country,
					'postal_code'    => $postcode,
					'state'          => $province,
				]
			];

			$cart = new \Cart\Cart();
			$cart->setCustomer( $customer );
			$shipping = $cart->addShipping( $country, $email );

			return [
				'status'            => 'ok',
				'checkoutSessionId' => $shipping['checkoutSessionId'],
				'shipping'          => $shipping['shipping'],
				'currencies'        => $shipping['currencies'],
				'items'             => $shipping['items'],
				'total'             => $shipping['total'],
			];
		}
	  ],[
		'pattern' => 'order/success/(:alpha)',
		'method' => 'GET|POST',
		'action'  => function ($alpha) {
			$logger = ( new \Logger\Logger( 'cart' ) )->getLogger();

			if($alpha == 'stripe'){
				$logger->debug( "order success callback received", ['txn' => kirby()->session()->get( 'txn' ), 'provider' => 'stripe'] );
				(new \Cart\Cart())->processStripe();
				return page('prints/order');
			}

			$logger->warning( "order success callback received with unknown provider", ['txn' => kirby()->session()->get( 'txn' ), 'provider' => $alpha] );
			return page('prints/cart');
		}]
	]
]);
