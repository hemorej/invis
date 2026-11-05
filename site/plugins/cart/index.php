<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Cart\Cart;
use Kirby\Cms\App;
use Kirby\Http\Response;
use Logger\Logger;

App::plugin( 'cart/cart', [
	'routes' => [
		[
			'pattern' => 'address',
			'method'  => 'POST',
			'action'  => function () {
				$logger = ( new Logger( 'cart' ) )->getLogger();
				$txn = kirby()->session()->get( 'txn' );

				$fail = fn( string $message, int $code ) => Response::json( ['status' => 'error', 'message' => $message], $code );

				if( csrf( get( 'csrf' ) ) !== true ) {
					$logger->warning( "address submission rejected, invalid CSRF token", ['txn' => $txn] );
					return $fail( 'Invalid CSRF token', 403 );
				}

				// Each call creates a Stripe Checkout session, so cap how often one visitor can hit this.
				if( Cart::rateLimited( 'address', 20, 10 ) ) {
					$logger->warning( "address submission rejected, rate limited", ['txn' => $txn] );
					return $fail( 'Too many requests, please try again later', 429 );
				}

				// Scalar-only, trimmed and length-capped; anything else (e.g. name[]=x) becomes ''.
				$input = function ( string $key, int $max ): string {
					$value = get( $key );
					return is_string( $value ) ? mb_substr( trim( $value ), 0, $max ) : '';
				};

				$name     = $input( 'name', 100 );
				$email    = $input( 'email', 254 );
				$line1    = $input( 'line1', 200 );
				$line2    = $input( 'line2', 200 );
				$city     = $input( 'city', 100 );
				$country  = $input( 'country', 100 );
				$postcode = $input( 'postcode', 20 );
				$province = $input( 'province', 100 );

				if( $name === '' || $line1 === '' || $city === '' || $country === '' || $postcode === ''
					|| !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$logger->warning( "address submission rejected, invalid address data", ['txn' => $txn] );
					return $fail( 'Invalid address data', 422 );
				}

				$customer = [
					'name'    => $name,
					'email'   => $email,
					'address' => [
						'address_line_1' => $line1,
						'address_line_2' => $line2,
						'city'           => $city,
						'country'        => $country,
						'postal_code'    => $postcode,
						'state'          => $province,
					],
				];

				try {
					$cart = new Cart();

					if( empty( $cart->getCartPage() ) || $cart->items()->count() === 0 ) {
						$logger->warning( "address submission rejected, no active cart", ['txn' => $txn] );
						return $fail( 'Your cart is empty', 409 );
					}

					$cart->setCustomer( $customer );
					$shipping = $cart->addShipping( $country, $email );
				} catch( \Throwable $t ) {
					$logger->error( "address submission failed", ['txn' => $txn, 'reason' => $t->getMessage()] );
					return $fail( 'Something went wrong, please try again', 500 );
				}

				return [
					'status'            => 'ok',
					'checkoutSessionId' => $shipping['checkoutSessionId'],
					'shipping'          => $shipping['shipping'],
					'currencies'        => $shipping['currencies'],
					'items'             => $shipping['items'],
					'total'             => $shipping['total'],
				];
			},
		],
		[
			'pattern' => 'order/success/(:alpha)',
			'method'  => 'GET|POST',
			'action'  => function ( string $provider ) {
				$logger = ( new Logger( 'cart' ) )->getLogger();
				$txn = kirby()->session()->get( 'txn' );

				if( $provider === 'stripe' ) {
					$logger->debug( "order success callback received", ['txn' => $txn, 'provider' => 'stripe'] );
					( new Cart() )->processStripe();
					return page( 'prints/order' );
				}

				$logger->warning( "order success callback received with unknown provider", ['txn' => $txn, 'provider' => $provider] );
				return page( 'prints/cart' );
			},
		],
	],
] );
