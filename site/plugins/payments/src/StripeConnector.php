<?php

namespace Payments;

use Stripe\Price;
use Stripe\Stripe;
use Logger\Logger;
use Stripe\Product;
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
				$sessionLineItems[] = ['price' => $lineItem['price_external_id'], 'quantity' => $lineItem['quantity']];
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

	/**
	 * @param $product
	 * @param $variants
	 * @return void
	 * @throws ApiErrorException
	 * @throws \Throwable
	 */
	public function createOrUpdateProduct( $product, $variants )
	{
		try {
			$productAttributes = [
				'name' => $product->title()->value,
				'description' => $product->meta()->value,
				'images' => empty( $product->images()->value ) ? null : $product->images()->value,
				'metadata' => ['uuid' => $product->uuid()->value],
			];

			if( empty( $product->external_id()->value ) ) {
				$stripeProduct = Product::create( $productAttributes );
				$productStripeId = $stripeProduct->id;

				kirby()->impersonate( kirby()->user() );
				$product->parent()->update(['external_id' => $productStripeId]);
			} else {
				Product::update(
					$product->external_id()->value,
					$productAttributes
				);
				$productStripeId = $product->external_id()->value;
			}

			foreach( $variants as $variant ) {

				if( empty( $variant->external_id()->value ) ) {
					$createPrice = true;
				} else {
					// unit amount cannot be changed
					// diff changes before we take destructive action
					$price = Price::retrieve($variant->external_id()->value);
					if($price->unit_amount != $variant->price()->value*100){
						Price::update($variant->external_id()->value, ['active' => false]);
						$createPrice = true;
					} else {
						$createPrice = false;
					}
				}

				if($createPrice) {
					$priceAttributes = [
						'product' => $productStripeId,
						'unit_amount' => intval( $variant->price()->value ) * 100,
						'currency' => 'CAD',
						'lookup_key' => $variant->suuid()->value,
						'transfer_lookup_key' => true
					];
					$price = Price::create( $priceAttributes );
					$priceStripeId = $price->id;

					$variantStructure = $product->parent()->variants()->findBy( 'suuid', $variant->suuid()->value() )->yaml();
					$storedVariant = $variantStructure[0];

					$updatedVariant = [];
					$updatedVariant['suuid'] = $storedVariant['suuid'];
					$updatedVariant['name'] = $storedVariant['name'];
					$updatedVariant['price'] = $storedVariant['price'];
					$updatedVariant['external_id'] = $priceStripeId;
					$updatedVariant['stock'] = $storedVariant['stock'];

					addToStructure( $product->parent(), 'variants', $updatedVariant );
				}
			}
		} catch( \Exception $e ) {
			$this->logger->error( "Could not update products or prices" . $e->getMessage() );
		}
	}
}