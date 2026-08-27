<?php

namespace Payments;

use Exception;
use Stripe\Price;
use Stripe\Stripe;
use Logger\Logger;
use Stripe\Product;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;

/**
 * Wraps the Stripe SDK for session creation, product/price sync, and payment-intent retrieval.
 */
class StripeConnector
{
	/** @var StripeClient */
	protected $stripe;

	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * Initialises the Stripe SDK with the private key from Kirby config.
	 *
	 * @return void
	 */
	function __construct()
	{
		$instance = new Logger( 'stripe' );
		$this->logger = $instance->getLogger();
		Stripe::setApiKey( kirby()->option( 'stripe_key_prv' ) );
		$this->stripe = new StripeClient( kirby()->option( 'stripe_key_prv' ) );

		return $this->stripe;
	}

	/**
	 * Creates a Stripe Checkout session for the given line items.
	 *
	 * Each line item either references an existing Stripe Price
	 * (`price_external_id`) or carries inline `price_data` (name, description,
	 * amount in cents). On success/cancel Stripe redirects back to the
	 * /order/success/stripe and /prints/cart routes.
	 *
	 * @param array               $lineItems     Output of Cart::getLineItems()
	 * @param string|null         $customerEmail Prefills the checkout email field
	 * @return Session
	 * @throws Exception  Wrapped Stripe error (details are logged, not exposed)
	 */
	public function createSession( array $lineItems, ?string $customerEmail = null )
	{
		try {
			$sessionLineItems = [];

			foreach( $lineItems as $lineItem ) {
				if( !empty( $lineItem['price_external_id'] ) ) {
					$sessionLineItems[] = ['price' => $lineItem['price_external_id'], 'quantity' => $lineItem['quantity']];
				} else {
					$sessionLineItems[] = [
						'price_data' => [
							'currency' => 'CAD',
							'unit_amount' => $lineItem['amount'],
							'product_data' => [
								'name' => $lineItem['name'],
								'description' => $lineItem['description'],
						  	],
						],
						'quantity' => 1
					];
				}
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

			$session = $this->stripe->checkout->sessions->create( $sessionObject );
			$this->logger->info( 'stripe checkout session created', ['session_id' => $session->id, 'line_items' => count( $sessionLineItems ), 'customer_email' => empty( $customerEmail ) ? null : maskEmail( $customerEmail )] );

			return $session;
		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'stripe error creating session', ['reason' => $se->getMessage()] );
			throw new Exception( 'Stripe error creating session' );
		} catch( Exception $e ) {
			$this->logger->error( 'stripe general error creating session', ['reason' => $e->getMessage()] );
			throw new Exception( 'Stripe general error' );
		}
	}

	/**
	 * Fetches a Checkout session by id (used on the Stripe return redirect).
	 *
	 * @param string $sid Stripe Checkout session id
	 * @return Session
	 * @throws Exception  Wrapped Stripe error
	 */
	public function retrieveSession( string $sid )
	{
		try {
			return $this->stripe->checkout->sessions->retrieve( $sid, [] );
		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'stripe error getting session', ['session_id' => $sid, 'reason' => $se->getMessage()] );
			throw new Exception( 'Stripe error getting session' );
		} catch( Exception $e ) {
			$this->logger->error( 'stripe general error getting session', ['session_id' => $sid, 'reason' => $e->getMessage()] );
			throw new Exception( 'Stripe general error' );
		}
	}

	/**
	 * Fetches a PaymentIntent with its latest charge expanded, so the caller
	 * can check both `status` and `latest_charge->paid`.
	 *
	 * @param string $pid Stripe PaymentIntent id
	 * @return PaymentIntent
	 * @throws Exception  Wrapped Stripe error
	 */
	public function retrievePaymentIntent( string $pid )
	{
		try {
			return $this->stripe->paymentIntents->retrieve( $pid, ['expand' => ['latest_charge']] );

		} catch( InvalidRequestException $se ) {
			$this->logger->error( 'stripe error getting payment intent', ['payment_intent_id' => $pid, 'reason' => $se->getMessage()] );
			throw new Exception( "Stripe error getting payment intent" );
		} catch( Exception $e ) {
			$this->logger->error( 'stripe general error getting payment intent', ['payment_intent_id' => $pid, 'reason' => $e->getMessage()] );
			throw new Exception( "Stripe general error" );
		}
	}

	/**
	 * Creates or updates a Stripe Product and its Prices to match the given Kirby page.
	 * Deactivates stale prices when the unit amount changes (Stripe prices are immutable).
	 *
	 * @param \Kirby\Cms\Page $page The print page whose variants drive Stripe product/price data
	 * @return void
	 * @throws ApiErrorException
	 * @throws \Throwable
	 */
	public function createOrUpdateProduct( $page )
	{
		try {
			$product = $page->content();
			$fieldsToUpdate = [];

			$productAttributes = [
				'name' => $product->title()->value,
				'description' => $product->meta()->value,
				'images' => empty( $product->images()->value ) ? null : $product->images()->value,
				'metadata' => ['uuid' => $product->uuid()->value],
			];

			if( empty( $product->external_id()->value ) ) {
				$stripeProduct = Product::create( $productAttributes );
				$productStripeId = $stripeProduct->id;

				$fieldsToUpdate['external_id'] = $productStripeId;
			} else {
				Product::update(
					$product->external_id()->value,
					$productAttributes
				);
				$productStripeId = $product->external_id()->value;
			}

			$variants = $page->variants()->toStructure();
			foreach( $variants as $variant ) {
				if( empty( $variant->external_id()->value ) ) {
					$createPrice = true;
				} else {
					// unit amount cannot be changed
					// diff changes before we take destructive action
					$price = Price::retrieve( $variant->external_id()->value );
					if( $price->unit_amount != $variant->price()->value * 100 ) {
						Price::update( $variant->external_id()->value, ['active' => false] );
						$createPrice = true;
					} else {
						$createPrice = false;
					}
				}

				$storedVariant = $variants->findBy( 'suuid', $variant->suuid()->value() );
				$updatedVariant = [];
				if( empty( $storedVariant ) ) {
					$storedVariant = $variant->content();
				}

				if( $createPrice ) {
					$priceAttributes = [
						'product' => $productStripeId,
						'unit_amount' => intval( $variant->price()->value ) * 100,
						'currency' => 'CAD',
						'lookup_key' => $variant->suuid()->value,
						'transfer_lookup_key' => true,
					];
					$price = Price::create( $priceAttributes );
					$updatedVariant['external_id'] = $price->id;
				} else {
					$updatedVariant['external_id'] = $storedVariant->external_id()->value();
				}

				$updatedVariant['suuid'] = $storedVariant->suuid()->value();
				$updatedVariant['name'] = $storedVariant->name()->value();
				$updatedVariant['price'] = $storedVariant->price()->value();
				$updatedVariant['stock'] = $storedVariant->stock()->value();

				$fieldsToUpdate['variants'][] = $updatedVariant;
			}

			if( !empty( $fieldsToUpdate ) ) {
				$page->update( $fieldsToUpdate );
			}

			$this->logger->info( "stripe product synced", ['page' => $page->id(), 'product_id' => $productStripeId, 'variants' => count( $variants )] );
		} catch( Exception $e ) {
			$this->logger->error( "could not sync stripe product or prices", ['page' => $page->id(), 'reason' => $e->getMessage()] );
		}
	}
}