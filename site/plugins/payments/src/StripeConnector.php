<?php

namespace Payments;

use \Stripe\Stripe;
use \Stripe\Product;
use \Stripe\Price;
use \Logger\Logger;
use Stripe\Customer;
use Stripe\Collection;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

class StripeConnector
{
	protected $stripe;
	protected $logger;

	function __construct()
	{
		$instance = new Logger( 'stripe' );
		$this->logger = $instance->getLogger();
		Stripe::setApiKey( kirby()->option( 'stripe_key_prv' ) );
		$this->stripe = new StripeClient( kirby()->option( 'stripe_key_prv' ) );

		return $this->stripe;
	}

	/**
	 * @param string $productID
	 * @return Product|null
	 * @throws \Exception
	 */
	public function retrieveProduct( string $productID )
	{
		try {
			if( !empty( $productID ) )
				return $this->stripe->products->retrieve( $productID );

			return null;
		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'Stripe error retrieving product', [$se->getMessage()] );
			throw new \Exception( 'Stripe error retrieving product' );
		} catch( \Exception $e ) {
			$this->logger->error( 'Stripe general error', [$e->getMessage()] );
			throw new \Exception( 'Stripe general error' );
		}
	}

	/**
	 * @param string $customerID
	 * @return Customer|null
	 * @throws \Exception
	 */
	public function retrieveCustomer( string $customerID )
	{
		try {
			if( !empty( $customerID ) )
				return $this->stripe->customers->retrieve( $customerID );

			return null;
		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'Stripe error retrieving customer', [$se->getMessage()] );
			throw new \Exception( 'Stripe error retrieving customer' );
		} catch( \Exception $e ) {
			$this->logger->error( 'Stripe general error', [$e->getMessage()] );
			throw new \Exception( 'Stripe general error' );
		}
	}

	/**
	 * @param string $email
	 * @return Collection|null
	 * @throws ApiErrorException
	 */
	public function findCustomer( string $email )
	{
		if( !empty( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL ) )
			return $this->stripe->customers->all( ['email' => $email] );

		return null;
	}

	/**
	 * @param string $customerID
	 * @return string
	 * @throws ApiErrorException
	 */
	public function redirectToPortal( string $customerID )
	{
		$billingPortal = $this->stripe->billingPortal->sessions->create( [
			'customer' => $customerID,
			'return_url' => 'https://the-invisible-cities.com/prints/subscriptions',
		] );

		return $billingPortal->url;
	}

	/**
	 * @param array $lineItems
	 * @param string|null $customerEmail
	 * @return Session
	 * @throws \Exception
	 */
	public function createSession( array $lineItems, string $customerEmail = null )
	{
		try {
			$sessionLineItems = [];

			foreach( $lineItems as $lineItem ) {
				$createPrice = false; // assuming price exists and we don't need to create a new one

				// direct search by price id first
				$priceUuid = $lineItem['variant_uuid'];
				$prices = $this->stripe->prices->search( ['query' => 'active:"true" AND lookup_key:"' . $priceUuid . '"'] );

				if( !empty( $prices->data ) ) {
					$priceId = $prices->data[0]->id;
				} else {
					// price not found, does product exist ?
					$products = $this->stripe->products->search( ['query' => 'active:"true" AND metadata["uuid"]:"' . $lineItem['product_uuid'] . '"'] );

					if( empty( $products->data ) ) {
						// create product
						$productName = $lineItem['description'] . $lineItem['name'];
						$product = Product::create( [
							'name' => $productName,
							'description' => $lineItem['description'],
							'images' => empty( $lineItem['images'] ) ? null : $lineItem['images'],
							'metadata' => ['uuid' => $lineItem['product_uuid']],
						] );
						$productId = $product->id;
						$createPrice = true;
					} else {
						$productId = $products->data[0]->id;
						$prices = $this->stripe->prices->search( ['query' => 'active:"true" AND product:"' . $productId . '"'] );
						if( empty( $prices->data ) ) {
							$createPrice = true;
						} else {
							$priceId = $prices->data[0]->id;
						}
					}
				}

				if( $createPrice ) {
					$price = Price::create( [
						'product' => $productId,
						'unit_amount' => $lineItem['amount'],
						'currency' => $lineItem['currency'],
						'lookup_key' => $priceUuid,
					] );
					$priceId = $price->id;
				}

				$sessionLineItems[] = ['price' => $priceId, 'quantity' => $lineItem['quantity']];
			}

			$sessionObject = [
				'payment_method_types' => ['card'],
				'mode' => 'payment',
				'line_items' => $sessionLineItems,
				'success_url' => kirby()->site()->url() . '/order/success/stripe?sid={CHECKOUT_SESSION_ID}',
				'cancel_url' => kirby()->site()->url() . '/prints/cart',
			];

			if( !empty( $customerEmail ) )
				$sessionObject['customer_email'] = $customerEmail;

			return $this->stripe->checkout->sessions->create( $sessionObject );
		} catch( InvalidRequestException $se ) {
			error_log( $se->getMessage() );
			$this->logger->error( 'Stripe error creating session', [$se->getMessage()] );
			throw new \Exception( 'Stripe error creating session' );
		} catch( \Exception $e ) {
			error_log( $e->getMessage() );
			$this->logger->error( 'Stripe general error', [$e->getMessage()] );
			throw new \Exception( 'Stripe general error' );
		}
	}

	/**
	 * @param string $sid
	 * @return Session
	 * @throws \Exception
	 */
	public function retrieveSession( string $sid )
	{
		try {
			return $this->stripe->checkout->sessions->retrieve( $sid, ['latest_charge'] );

		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'Stripe error getting session', [$se->getMessage()] );
			throw new \Exception( 'Stripe error getting session' );
		} catch( \Exception $e ) {
			$this->logger->error( 'Stripe general error', [$e->getMessage()] );
			throw new \Exception( 'Stripe general error' );
		}
	}

	/**
	 * @param string $pid
	 * @return PaymentIntent
	 * @throws \Exception
	 */
	public function retrievePaymentIntent( string $pid )
	{
		try {
			return $this->stripe->paymentIntents->retrieve( $pid );

		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'Stripe error getting payment intent', [$se->getMessage()] );
			throw new \Exception( "Stripe error getting payment intent" );
		} catch( \Exception $e ) {
			$this->logger->error( 'Stripe general error', [$e->getMessage()] );
			throw new \Exception( "Stripe general error" );
		}
	}
}