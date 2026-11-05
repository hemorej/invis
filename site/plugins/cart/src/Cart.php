<?php

namespace Cart;

use Kirby\Cache\Cache;
use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Data\Yaml;
use Kirby\Http\Remote;
use Kirby\Session\Session;
use Kirby\Toolkit\A;
use Kirby\Uuid\Uuid;
use Logger\Logger;
use Mailbun\Mailbun;
use Monolog\Logger as Monolog;
use Payments\StripeConnector as Stripe;

/**
 * Manages the shopping cart backed by Kirby draft pages under prints/orders.
 * Handles item add/delete, shipping calculation, Stripe session management,
 * payment processing, inventory deduction, and order notifications.
 */
class Cart
{
	/** Shipping charged (CAD) when no rate is configured for the destination or the 'rest' region. */
	private const DEFAULT_SHIPPING = 32.32;

	protected Cache $cache;
	protected Site $site;
	protected Session $session;
	protected Monolog $logger;
	protected ?string $txnId = null;
	protected ?Page $cartPage = null;

	public function __construct()
	{
		$this->cache = kirby()->cache( 'helpers.helpers.backend' );
		$this->site = kirby()->site();
		$this->session = kirby()->session();
		$this->logger = ( new Logger( 'cart' ) )->getLogger();
	}

	/**
	 * Fixed-window, per-IP rate limit backed by the plugin cache.
	 * Counts this call, and reports whether the caller is now over the limit.
	 *
	 * @param string $bucket        Name of the limited action
	 * @param int    $max           Allowed calls per window
	 * @param int    $windowMinutes Window length in minutes
	 * @return bool True when the request should be rejected
	 */
	public static function rateLimited( string $bucket, int $max, int $windowMinutes ): bool
	{
		$cache = kirby()->cache( 'helpers.helpers.backend' );
		$key = 'rate-' . $bucket . '-' . hash( 'sha256', (string) kirby()->visitor()->ip() );
		$now = time();

		$state = $cache->get( $key );
		if( !is_array( $state ) || $now - ( $state['start'] ?? 0 ) >= $windowMinutes * 60 )
			$state = ['start' => $now, 'count' => 0];

		if( $state['count'] >= $max )
			return true;

		$state['count']++;
		$cache->set( $key, $state, $windowMinutes );

		return false;
	}

	/**
	 * Converts a CAD subtotal into a formatted USD/EUR/GBP estimate using live Fixer.io rates.
	 *
	 * @param int|float $total Amount in CAD
	 * @return string Formatted string, e.g. "42$/38€/33£"
	 * @throws \Exception
	 */
	public function estimateCurrency( int|float $total ): string
	{
		try {
			$rates = $this->getRates();
		} catch( \Throwable $t ) {
			// The estimate is cosmetic: never let a rates failure break the cart or checkout.
			$this->logger->warning( "could not fetch currency rates", ['reason' => $t->getMessage()] );
			return '';
		}

		$eurBase = $total / $rates->rates->CAD;
		$usd = $eurBase * $rates->rates->USD;
		$gbp = $eurBase * $rates->rates->GBP;

		$estimate = round( $usd, 0 ) . '$/'
			. round( $eurBase, 0 ) . '€/'
			. round( $gbp, 0 ) . '£';

		return $estimate;
	}

	/**
	 * Returns the Fixer.io rates (EUR base), cached for 24h. Only well-formed,
	 * successful responses are cached, so an API error can't poison the cache.
	 *
	 * @return object
	 * @throws \Exception When the API call fails or returns an unexpected payload
	 */
	private function getRates(): object
	{
		if( $data = $this->cache->get( 'rates' ) ) {
			return json_decode( $data );
		}

		$access_key = kirby()->option( 'fixer_key' );
		$response = Remote::get( 'http://data.fixer.io/api/latest?access_key=' . $access_key . '&symbols=USD,CAD,GBP', ['timeout' => 5] );
		$rates = json_decode( $response->content() );

		if( empty( $rates->success ) || !isset( $rates->rates->CAD, $rates->rates->USD, $rates->rates->GBP ) ) {
			throw new \Exception( 'Unexpected response from the rates API' );
		}

		$this->cache->set( 'rates', $response->content(), 1440 );

		return $rates;
	}

	/**
	 * Reports stock availability for a variant.
	 *
	 * Accepts either a variant structure object or a "uri::suuid" id string
	 * (which is resolved to the variant first).
	 *
	 * @param mixed $variant Variant structure object, or a "uri::suuid" id
	 * @return bool|int  true = unlimited (blank stock field), false = out of
	 *                   stock, or the positive integer quantity on hand
	 */
	public static function inStock( $variant )
	{
		// Id string form: split "uri::suuid" and look the variant up.
		if( str_contains( $variant->toString(), '::' ) ) {
			$idParts = explode( '::', $variant );
			$uri = $idParts[0];
			$uuid = $idParts[1];

			$variant = page( $uri )->variants()->toStructure()->findBy( 'suuid', $uuid );
			return $variant->stock()->value();
		}

		$stock = $variant->stock()->value;

		if( $stock === '' ) return true;               // blank = untracked / unlimited
		if( !is_numeric( $stock ) ) return false;
		if( (int) $stock <= 0 ) return false;          // 0 or negative = sold out

		return (int) $stock;
	}

	/**
	 * Returns a human-readable summary of cart item types and quantities (e.g. "2 prints, 1 poster").
	 *
	 * @param \Kirby\Cms\Collection|\Kirby\Cms\Structure $items Cart items structure
	 * @return string
	 */
	public function contents( $items ): string
	{
		$types = [];

		foreach( $items as $item ) {
			$type = $item->type()->value();
			$types[$type] = ( $types[$type] ?? 0 ) + (int) $item->quantity()->value();
		}

		$lines = [];
		foreach( $types as $type => $quantity ) {
			$lines[] = $quantity . ' ' . $type . ( $quantity > 1 ? 's' : '' );
		}

		return implode( ', ', $lines );
	}

	/**
	 * Sums amount x quantity across cart items (CAD, pre-shipping).
	 *
	 * @param \Kirby\Cms\Collection|\Kirby\Cms\Structure $items Cart items structure
	 * @return int|float
	 */
	public function subtotal( $items ): int|float
	{
		$subtotal = 0;
		foreach( $items as $item ) {
			$itemAmount = $item->amount()->value;
			$subtotal += $itemAmount * intval( $item->quantity()->value );
		}
		return $subtotal;
	}

	/**
	 * Returns the current cart's draft order page, or null if no active session exists.
	 *
	 * @return Page|null
	 */
	public function getCartPage(): ?Page
	{
		if( $this->cartPage !== null ) return $this->cartPage;
		if( empty( $this->session->get( 'txn' ) ) ) return null;
		$this->cartPage = page( 'prints/orders' )->draft( $this->session->get( 'txn' ) );
		return $this->cartPage;
	}

	/**
	 * Builds the Stripe line-item array from the current cart page, optionally
	 * appending a "Shipping" line. Amounts are converted to cents.
	 *
	 * @param int|float $shipping Shipping amount in CAD; omit / 0 for no line
	 * @return array<int, array<string, mixed>> Stripe-shaped line items
	 */
	public function getLineItems( int|float $shipping = 0 ): array
	{
		$lineItems = [];
		$products = $this->getCartPage()?->products()->toStructure();
		if( $products === null || $products->isEmpty() ) {
			return $lineItems;
		}

		foreach( $products as $product ) {
			$preview = $this->site->page( $product->uri()->value )->images()->first()->crop( 100 )->url();
			$lineItems[] = [
				'price_external_id' => $product->price_external_id()->value,
				'name' => $product->variant()->value,
				'description' => $product->name()->value,
				'amount' => (int) round( (float) $product->amount()->value * 100 ),
				'images' => [$preview],
				'currency' => 'CAD',
				'quantity' => $product->quantity()->value];
		}

		if( !empty( $shipping ) && $shipping >= 0 ) {
			$lineItems[] = [
				'name' => 'Shipping',
				'description' => 'Standard shipping by Canada Post',
				'amount' => (int) round( (float) $shipping * 100 ),
				'currency' => 'CAD',
				'quantity' => 1];
		}

		return $lineItems;
	}

	/**
	 * Returns the cart's product rows as a Collection (empty if no active cart).
	 *
	 * @return Collection
	 */
	public function items(): Collection
	{
		$return = new Collection();

		if( empty( $this->getCartPage() ) )
			return $return;

		$items = $this->getCartPage()->products()->toStructure();

		// Return the empty collection if there are no items
		if( $items->isEmpty() ) return $return;

		foreach( $items as $key => $item )
			$return->append( $key, $item );

		return $return;
	}

	/**
	 * Adds a variant to the cart, or bumps its quantity if already present,
	 * clamping to available stock (see updateQty()). Creates the draft order
	 * page and the session `txn` on the first add.
	 *
	 * @param string   $id       Variant id, formatted "uri::suuid"
	 * @param int|null $quantity Desired quantity; null/0 means "add one more"
	 * @return void
	 * @throws InvalidArgumentException
	 * @throws \Throwable
	 */
	public function add( string $id, int|string|null $quantity ): void
	{
		if( !empty( $quantity ) && $quantity <= 0 )
			return;

		try {
			$quantityToAdd = $quantity ? intval( $quantity ) : 1;
			$idParts = explode( '::', (string) $id ); // $id is formatted uri::suuid
			if( count( $idParts ) !== 2 || $idParts[0] === '' || $idParts[1] === '' ) {
				$this->logger->warning( "add to cart rejected, malformed item id", ['txn' => $this->session->get( 'txn' ), 'item' => $id] );
				return;
			}
			[$uri, $uuid] = $idParts;

			$product = $this->site->page( $uri );
			$variant = $product?->variants()->toStructure()->findBy( 'suuid', $uuid );
			if( empty( $product ) || empty( $variant ) ) {
				$this->logger->warning( "add to cart rejected, unknown product or variant", ['txn' => $this->session->get( 'txn' ), 'item' => $id] );
				return;
			}

			$item = empty( $this->getCartPage() ) ? null : $this->getCartPage()->products()->toStructure()->findBy( 'suuid', $uuid );
			$items = empty( $this->getCartPage() ) ? [] : $this->getCartPage()->products()->yaml();

			if( empty( $item ) ) {
				// Add a new item
				$grantedQty = $this->updateQty( $id, $quantityToAdd );
				if( $grantedQty <= 0 ) {
					$this->logger->warning( "item not added, out of stock", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'requested' => $quantityToAdd] );
					return;
				}

				$items[] = [
					'id' => $id,
					'uri' => $uri,
					'variant' => $variant->name()->value(),
					'name' => $product->title()->value(),
					'amount' => $variant->price()->value(),
					'type' => $product->type()->value(),
					'uuid' => $product->uuid()->id(),
					'suuid' => $uuid,
					'price_external_id' => $variant->external_id()->value,
					'quantity' => $grantedQty,
				];
			} else {
				// Update the quantity of an existing item: an explicit quantity
				// replaces the current one, otherwise add one more.
				$newQty = $quantity ? (int) $quantity : (int) $item->quantity()->value + 1;
				$grantedQty = $this->updateQty( $id, $newQty );
				foreach( $items as $key => $i ) {
					if( $i['id'] === (string) $item->id() ) {
						$items[$key]['quantity'] = $grantedQty;
						break;
					}
				}
				$quantityToAdd = $newQty;
			}

			// Create the transaction file if we don't have one yet
			if( empty( $this->session->get( 'txn' ) ) || empty( $this->getCartPage() ) ) {
				// 24 random hex chars + session start time: unguessable, and
				// scoped to this session so a stale cookie can't resurrect a cart.
				$this->txnId = bin2hex( random_bytes( 12 ) ) . $this->session->startTime();
				$timestamp = time();

				// Draft order pages live outside the visitor's permission scope.
				kirby()->impersonate( 'kirby' );
				$this->cartPage = Page::create( [
					'parent' => page( 'prints/orders' ),
					'slug' => $this->txnId,
					'template' => 'order',
					'draft' => true,
					'content' => [
						'txn-id' => $this->txnId,
						'txn-date' => date( 'm/d/Y H:i:s', $timestamp ),
						'suuid' => Uuid::generate(),
						'orderstatus' => 'pending',
						'session-start' => $timestamp,
						'session-end' => $timestamp,
						'products' => Yaml::encode( $items ),
					],
				] );

				$this->session->set( 'txn', $this->txnId );
			} else {
				kirby()->impersonate( 'kirby' );
				$this->getCartPage()->update( ['products' => Yaml::encode( $items )] + $this->expireStaleStripeSession() );
				$this->cartPage = null;
			}

			if( $grantedQty < $quantityToAdd ) {
				$this->logger->warning( "requested quantity reduced due to stock", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'requested' => $quantityToAdd, 'granted' => $grantedQty] );
			} else {
				$this->logger->info( "item added to cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'quantity' => $grantedQty] );
			}
		} catch( \Throwable $e ) {
			$this->logger->error( "failed to add item to cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'reason' => $e->getMessage()] );
		}
	}

	/**
	 * Clamps a requested line quantity to the stock of that exact variant.
	 *
	 * @param string $id     Variant id, formatted "uri::suuid"
	 * @param int    $newQty Requested absolute quantity for the line
	 * @return int The grantable quantity (may be less than requested, or 0)
	 */
	public function updateQty( string $id, int $newQty ): int
	{
		[$uri, $suuid] = array_pad( explode( '::', (string) $id ), 2, '' );

		$variant = $this->site->page( $uri )?->variants()->toStructure()->findBy( 'suuid', $suuid );
		if( empty( $variant ) )
			return 0;

		$stock = self::inStock( $variant ); // true = unlimited, false = sold out, int = on hand

		if( $stock === true )
			return max( 0, (int) $newQty );

		if( $stock === false )
			return 0;

		return max( 0, min( (int) $newQty, $stock ) );
	}

	/**
	 * Expires the order's open Stripe Checkout session, if any, so a cart that
	 * changed after checkout started can't be paid at the old price/contents.
	 * Returns the field changes to merge into the next cart-page update.
	 * If Stripe won't expire it (already paid), the id is kept so the return
	 * redirect still matches.
	 *
	 * @return array<string, string>
	 */
	private function expireStaleStripeSession(): array
	{
		$sid = $this->getCartPage()?->stripe_session_id()->value();
		if( empty( $sid ) )
			return [];

		return ( new Stripe() )->expireSession( $sid ) ? ['stripe_session_id' => ''] : [];
	}

	/**
	 * Removes a line from the cart by its "uri::suuid" id.
	 *
	 * @param string $id Variant id to remove
	 * @return void
	 * @throws \Throwable
	 */
	public function delete( string $id ): void
	{
		$cartPage = $this->getCartPage();
		if( empty( $cartPage ) ) return;

		$items = array_values( array_filter(
			$cartPage->products()->yaml(),
			fn( $i ) => ( $i['id'] ?? null ) !== $id
		) );

		kirby()->impersonate( 'kirby' );
		$cartPage->update( ['products' => Yaml::encode( $items )] + $this->expireStaleStripeSession() );
		$this->cartPage = null;

		$this->logger->info( "item removed from cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id] );
	}

	/**
	 * Persists the checkout contact/address block onto the draft order page.
	 *
	 * @param array $customer ['name', 'email', 'address' => [...]] as built by the /address route
	 * @return void
	 */
	public function setCustomer( array $customer ): void
	{
		kirby()->impersonate( 'kirby' );
		$this->getCartPage()->update( ['customer' => Yaml::encode( $customer )] );
		$this->cartPage = null;

		$this->logger->info( "customer details set for order", ['txn' => $this->session->get( 'txn' ), 'email' => maskEmail( $customer['email'] ), 'country' => $customer['address']['country'] ?? null] );
	}

	/**
	 * Looks up the shipping cost for the destination country (falling back to
	 * the 'rest' region, then a hard-coded default), stores it on the order,
	 * refreshes the Stripe session, and returns the recomputed totals for the
	 * checkout UI.
	 *
	 * @param string $country Destination country name
	 * @param string $email   Customer email, forwarded to the Stripe session
	 * @return array{total: float, currencies: string, shipping: float|string, checkoutSessionId: string, items: array}
	 * @throws \Exception
	 */
	public function addShipping( string $country, string $email ): array
	{
		$cartPage = $this->getCartPage();
		if( empty( $cartPage ) )
			throw new \Exception( 'No active cart to calculate shipping for' );

		$prints = page( 'prints' );
		$regionRow = $prints->regions()->toStructure()->findBy( 'country', $country );
		$region = $regionRow ? $regionRow->name()->value() : 'rest';

		$rates = $prints->shipping()->toStructure();
		$rateRow = $rates->findBy( 'region', $region ) ?? $rates->findBy( 'region', 'rest' );
		$amount = $rateRow?->amount()->value();

		// A configured 0 means free shipping; only a missing/blank rate falls back to the default.
		$shipping = is_numeric( $amount ) ? (float) $amount : self::DEFAULT_SHIPPING;

		kirby()->impersonate( 'kirby' );
		$cartPage->update( ['shipping' => $shipping] );
		$this->cartPage = null;
		$stripeSession = $this->updateStripeSession( $email );

		$subtotal = $this->subtotal( $this->items() );
		$total = $subtotal + (float) $shipping;
		$currencies = $this->estimateCurrency( $total );
		$lineItems = $this->getLineItems( (float) $shipping );

		$this->logger->info( "shipping calculated for order", ['txn' => $this->session->get( 'txn' ), 'country' => $country, 'region' => $region, 'shipping' => $shipping, 'total' => $total] );

		return ['total' => $total, 'currencies' => $currencies, 'shipping' => $shipping, 'checkoutSessionId' => $stripeSession, 'items' => $lineItems];
	}

	/**
	 * Creates a fresh Stripe Checkout session for the current line items and
	 * stores its id on the order page (overwriting any previous session).
	 *
	 * @param string $customerEmail Prefills the Stripe checkout email field
	 * @return string The new Stripe session id
	 * @throws \Exception
	 */
	public function updateStripeSession( string $customerEmail ): string
	{
		$shipping = empty( $this->getCartPage()->shipping()->value() )
			? 0
			: (float) $this->getCartPage()->shipping()->value();

		// A re-submitted address supersedes any earlier session; keep only one payable.
		$this->expireStaleStripeSession();

		$lineItems = $this->getLineItems( $shipping );
		$session = ( new Stripe() )->createSession( $lineItems, $customerEmail );

		kirby()->impersonate( 'kirby' );
		$this->getCartPage()->update( ['stripe_session_id' => $session->id] );
		$this->cartPage = null;

		return $session->id;
	}

	/**
	 * Finalizes an order after the visitor returns from Stripe Checkout.
	 *
	 * Verifies the returned `sid` matches the one stored on the order (replay
	 * guard), confirms the PaymentIntent actually succeeded, then — only if the
	 * order is still 'pending' — deducts inventory, marks the order paid and
	 * sends notifications. The whole section runs under a per-order file lock
	 * and re-reads the order once the lock is held, so concurrent requests
	 * (reloads, double clicks) can't process the same order twice. Failures are
	 * logged, alerted, and surfaced to the visitor via a session `error`.
	 *
	 * @return bool True if the order was finalized (or already was), false on any failure
	 */
	public function processStripe(): bool
	{
		$txn = $this->session->get( 'txn' );
		$returnedSid = get( 'sid' );
		$lock = null;

		try {
			if( empty( $returnedSid ) || !is_string( $returnedSid ) || empty( $txn ) ) {
				$this->logger->debug( "processStripe called with no session id or txn, nothing to do", ['txn' => $txn] );
				return false;
			}

			// Serialize processing per order; take the lock before reading any order content.
			$lock = fopen( kirby()->root( 'cache' ) . '/order-' . preg_replace( '/[^a-z0-9]/i', '', $txn ) . '.lock', 'c' );
			if( $lock === false || !flock( $lock, LOCK_EX ) )
				throw new \Exception( 'Could not lock order for processing' );

			$cartPage = $this->getCartPage();
			if( empty( $cartPage ) ) {
				// A concurrent request already finalized (and renamed) this order.
				$this->logger->info( "order already finalized by another request, skipping", ['txn' => $txn] );
				return true;
			}

			$storedSid = $cartPage->stripe_session_id()->value();
			if( $returnedSid !== $storedSid ) {
				$this->logger->error( "SID mismatch, possible replay attempt", ['txn' => $txn, 'sid' => $returnedSid, 'stored_sid' => $storedSid] );
				$this->session->set( 'error', 'Invalid payment session.' );
				return false;
			}

			$stripe = new Stripe();
			$sid = $stripe->retrieveSession( $returnedSid );
			$pi = $stripe->retrievePaymentIntent( $sid->payment_intent );

			// stripe checkout went well
			if( $pi->status === 'succeeded' && $pi->latest_charge->paid ) {
				// check status to avoid repeat processing if client reloads page
				if( $cartPage->content()->get( 'orderstatus' )->value() === 'pending' ) {
					$this->logger->info( "payment captured, finalizing order", ['txn' => $txn, 'session_id' => $sid->id, 'payment_intent_id' => $pi->id] );
					$this->checkPaidAmount( $sid, $txn );
					$this->updateInventory();
					$this->markPaid( 'stripe' ); // persisted before emails so a reload can't repeat them
					$this->sendNotifications();
					$this->updateOrder();
				} else {
					$this->logger->info( "order already finalized, skipping repeat processing", ['txn' => $txn, 'session_id' => $sid->id] );
				}
			} else {
				$this->logger->error( "stripe checkout returned a non-captured transaction", ['txn' => $txn, 'session_id' => $sid->id, 'payment_intent_id' => $pi->id, 'payment_intent_status' => $pi->status] );
				$this->session->set( 'error', 'There was an error with the payment processing, I have been notified of the issue.' );
				return false;
			}
		} catch( \Throwable $e ) {
			$this->logger->error( "general error processing stripe payment", ['txn' => $txn, 'reason' => $e->getMessage()] );
			sendAlert( $txn, $this->getCartPage()?->suuid()->value(), $e->getMessage() );
			$this->session->set( 'error', 'There was an unspecified error with the site, I have been notified of this issue. You may try again later' );
			return false;
		} finally {
			if( is_resource( $lock ) ) {
				flock( $lock, LOCK_UN );
				fclose( $lock );
			}
		}

		return true;
	}

	/**
	 * Safety net: compares what Stripe actually charged with the cart total and
	 * alerts the site owner on a mismatch. It never blocks finalization — the
	 * customer has already paid — it just makes a discrepancy visible.
	 *
	 * @param \Stripe\Checkout\Session $session Retrieved Checkout session
	 * @param string                    $txn     Transaction id (for logging)
	 * @return void
	 */
	private function checkPaidAmount( $session, $txn ): void
	{
		$expected = (int) round( ( $this->subtotal( $this->items() ) + (float) $this->getCartPage()->shipping()->value() ) * 100 );

		if( (int) $session->amount_total !== $expected ) {
			$message = "Charged amount {$session->amount_total} differs from cart total {$expected} (cents)";
			$this->logger->error( "paid amount does not match cart total", ['txn' => $txn, 'session_id' => $session->id, 'charged' => $session->amount_total, 'expected' => $expected] );
			sendAlert( $txn, $this->getCartPage()->suuid()->value(), $message );
		}
	}

	/**
	 * Deducts each ordered quantity from its variant's stock field.
	 * Throws (aborting order finalization) if a variant is missing or would
	 * go negative.
	 *
	 * @return void
	 * @throws \Throwable
	 */
	private function updateInventory(): void
	{
		$orderId = $this->getCartPage()->suuid()->value();
		$cartItems = $this->getCartPage()->products()->yaml();

		// Draft/order flows run as an anonymous visitor; stock writes need elevated rights.
		kirby()->impersonate( 'kirby' );

		// Phase 1: validate every line before writing anything, so a late failure
		// can't leave the stock half-deducted for an order that never finalizes.
		$variantsByUri = [];
		$changed = [];
		foreach( $cartItems as $item ) {
			$uri = $item['uri'];
			$suuid = (string) ( !empty( $item['suuid'] ) ? $item['suuid'] : ( explode( '::', $item['id'] )[1] ?? '' ) );

			$variantsByUri[$uri] ??= $this->site->page( $uri )->variants()->yaml();
			$key = array_search( $suuid, array_map( 'strval', array_column( $variantsByUri[$uri], 'suuid' ) ), true );

			if( $key === false )
				throw new \Exception( "Variant not found for suuid: " . $suuid );

			$stock = $variantsByUri[$uri][$key]['stock'] ?? '';
			if( !is_numeric( $stock ) )
				continue; // blank = untracked / unlimited, nothing to deduct

			$remainingStock = intval( $stock ) - intval( $item['quantity'] );
			if( $remainingStock < 0 )
				throw new \Exception( "Insufficient stock for product " . page( $uri )->title()->value() . " (uuid: " . $suuid . ")" );

			$variantsByUri[$uri][$key]['stock'] = $remainingStock;
			$changed[$uri] = true;
		}

		// Phase 2: one write per product.
		foreach( array_keys( $changed ) as $uri ) {
			$this->site->page( $uri )->update( ['variants' => Yaml::encode( array_values( $variantsByUri[$uri] ) )] );
		}

		$this->logger->info( "inventory updated after order", ['txn' => $this->session->get( 'txn' ), 'order' => $orderId] );
	}

	/**
	 * Persists the 'paid' status in a single update. This is what stops a reload
	 * from re-running inventory/notifications, so a failure is thrown to the caller.
	 *
	 * @param string $paymentMethod Payment provider identifier (e.g. 'stripe')
	 * @return void
	 * @throws \Throwable When the paid status could not be persisted
	 */
	private function markPaid( string $paymentMethod ): void
	{
		$orderId = $this->getCartPage()->suuid()->value();

		kirby()->impersonate( 'kirby' );
		$this->getCartPage()->update( ['title' => "ord-$orderId", 'orderstatus' => 'paid', 'payment' => $paymentMethod] );
		$this->cartPage = null;

		$this->logger->info( "order status updated to paid", ['txn' => $this->session->get( 'txn' ), 'order' => $orderId, 'payment_method' => $paymentMethod] );
	}

	/**
	 * Final bookkeeping for a paid order: renames the draft page to ord-{uuid}
	 * and updates the session state. The order is already paid by now, so a
	 * failed rename is only logged and alerted.
	 *
	 * @return void
	 */
	private function updateOrder(): void
	{
		$txn = $this->session->get( 'txn' );
		$orderId = $this->getCartPage()->suuid()->value();

		kirby()->impersonate( 'kirby' );
		try {
			$this->getCartPage()->changeSlug( "ord-$orderId" );
		} catch( \Throwable $t ) {
			$this->logger->error( "order paid but could not be renamed", ['txn' => $txn, 'order' => $orderId, 'reason' => $t->getMessage()] );
			sendAlert( $txn, $orderId, $t->getMessage() );
		}
		$this->cartPage = null;

		$this->session->set( 'state', 'success' );
		$this->session->set( 'order', str_replace( '_', '-', "ord-$orderId" ) );
		$this->session->remove( 'txn' );
	}

	/**
	 * Sends the order-confirmation email to the customer and a copy to the
	 * site owner's alert address. Mail failures are logged and alerted but
	 * don't abort order finalization.
	 *
	 * @return void
	 */
	private function sendNotifications(): void
	{
		$orderId = $this->getCartPage()->suuid()->value();
		$customer = $this->getCartPage()->customer()->yaml();
		$shipping = (float) $this->getCartPage()->shipping()->value();
		$subtotal = $this->subtotal( $this->items() );
		$total = $subtotal + $shipping;

		$order = [
			'order' => $orderId,
			'items' => $this->items(),
			'fullName' => $customer['name'],
			'street1' => $customer['address']['address_line_1'],
			'street2' => $customer['address']['address_line_2'],
			'city' => $customer['address']['city'],
			'country' => $customer['address']['country'],
			'postcode' => $customer['address']['postal_code'],
			'province' => $customer['address']['state'],
			'email' => $customer['email'],
			'shipping' => $shipping,
			'type' => 'order',
			'total' => $total,
		];

		try {
			$mailbun = new Mailbun();
			$mailbun->send(
				$customer['email'],
				'Your order from The Invisible Cities has been received',
				'confirm',
				A::merge( $order, [
					'title' => 'Your order from The Invisible Cities has been received',
					'subtitle' => 'Order confirmation',
					'preview' => 'Order confirmation. We received your order and will prepare it for shipping soon. Below is your order information.',
					'headline' => 'Thank you for your purchase! We received your order and will prepare it for sending soon. You will receive another email once the package has shipped. Below is your order information.',
				] ) );

			$this->logger->info( "order confirmation email sent", ['txn' => $this->session->get( 'txn' ), 'order' => $orderId] );

			$mailbun->send(
				kirby()->option( 'alert_address' ),
				'New order at The Invisible Cities!',
				'confirm',
				A::merge( $order, [
					'title' => 'A new order at the Invisible Cities has been received',
					'subtitle' => 'Order summary',
					'preview' => 'Order summary',
					'headline' => 'Below is the order information.',
				] ) );

			$this->logger->info( "admin order notification sent", ['txn' => $this->session->get( 'txn' ), 'order' => $orderId] );
		} catch( \Throwable $t ) {
			$description = "order notification email failed for order " . $orderId;
			$this->logger->error( $description, ['txn' => $this->session->get( 'txn' ), 'order' => $orderId, 'reason' => $t->getMessage()] );
			sendAlert( $this->session->get( 'txn' ), $orderId, $t->getMessage() );
		}
	}

}