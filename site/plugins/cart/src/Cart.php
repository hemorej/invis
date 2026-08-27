<?php

namespace Cart;

use Logger\Logger;
use Kirby\Cms\Page;
use Mailbun\Mailbun;
use Kirby\Uuid\Uuid;
use Kirby\Http\Remote;
use Payments\StripeConnector as Stripe;
use Kirby\Exception\InvalidArgumentException;

/**
 * Manages the shopping cart backed by Kirby draft pages under prints/orders.
 * Handles item add/delete, shipping calculation, Stripe session management,
 * payment processing, inventory deduction, and order notifications.
 */
class Cart
{
	/** @var \Kirby\Cache\Cache */
	protected $cache;

	/** @var \Kirby\Cms\Site */
	protected $site;

	/** @var \Kirby\Session\Session */
	protected $session;

	/** @var string|null */
	protected $txnId;

	/** @var \Monolog\Logger */
	protected $logger;

	/** @var \Kirby\Cms\Page|null */
	protected $cartPage = null;

	/**
	 * @return void
	 */
	function __construct()
	{
		$this->cache = kirby()->cache( 'helpers.helpers.backend' );
		$this->site = kirby()->site();
		$this->session = kirby()->session();
		$instance = new Logger( 'cart' );
		$this->logger = $instance->getLogger();
	}

	/**
	 * Converts a CAD subtotal into a formatted USD/EUR/GBP estimate using live Fixer.io rates.
	 *
	 * @param int|float $total Amount in CAD
	 * @return string Formatted string, e.g. "42$/38€/33£"
	 * @throws \Exception
	 */
	public function estimateCurrency( $total )
	{
		if( $data = $this->cache->get( 'rates' ) ) {
			$rates = json_decode( $data );
		} else {
			$access_key = kirby()->option( 'fixer_key' );
			$data = Remote::get( 'http://data.fixer.io/api/latest?access_key=' . $access_key . '&symbols=USD,CAD,GBP' );
			$this->cache->set( 'rates', $data->content(), 1440 );
			$rates = json_decode( $data->content() );
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
		if( strstr( $variant->toString(), '::' ) ) {
			$idParts = explode( '::', $variant );
			$uri = $idParts[0];
			$uuid = $idParts[1];

			$variant = page( $uri )->variants()->toStructure()->findBy( 'suuid', $uuid );
			return $variant->stock()->value();
		}

		if( !is_numeric( $variant->stock()->value ) and $variant->stock()->value === '' ) return true;  // blank = untracked / unlimited
		if( is_numeric( $variant->stock()->value ) and intval( $variant->stock()->value ) <= 0 ) return false; // 0 or negative = sold out
		if( is_numeric( $variant->stock()->value ) and intval( $variant->stock()->value ) > 0 ) return intval( $variant->stock()->value );

		return false;
	}

	/**
	 * Returns a human-readable summary of cart item types and quantities (e.g. "2 prints, 1 poster").
	 *
	 * @param \Collection $items Cart items structure
	 * @return string
	 */
	public function contents( $items )
	{
		$types = [];
		$content = "";

		foreach( $items as $item ) {
			if( array_key_exists( $item->type()->value(), $types ) ) {
				$types[$item->type()->value()] = $types[$item->type()->value()] + $item->quantity()->value();
			} else {
				$types[$item->type()->value()] = 1 * $item->quantity()->value();
			}
		}

		foreach( $types as $type => $quantity ) {
			$line = join( ' ', [$quantity, $type] );
			if( $quantity > 1 )
				$line .= 's';

			$content = $content . ', ' . $line;
		}

		return ltrim( $content, ', ' );
	}

	/**
	 * Sums amount x quantity across cart items (CAD, pre-shipping).
	 *
	 * @param \Collection $items Cart items structure
	 * @return int|float
	 */
	public function subtotal( $items )
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
	public function getCartPage()
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
	public function getLineItems( $shipping = 0 )
	{
		$lineItems = [];
		$products = $this->getCartPage()?->products()?->toStructure();
		if(empty($products)) {
			return $lineItems;
		}

		foreach( $products as $product ) {
			$preview = $this->site->page( $product->uri()->value )->images()->first()->crop( 100 )->url();
			$lineItems[] = [
				'price_external_id' => $product->price_external_id()->value,
				'name' => $product->variant()->value,
				'description' => $product->name()->value,
				'amount' => $product->amount()->value * 100,
				'images' => [$preview],
				'currency' => 'CAD',
				'quantity' => $product->quantity()->value];
		}

		if( !empty( $shipping ) && $shipping >= 0 ) {
			$lineItems[] = [
				'name' => 'Shipping',
				'description' => 'Standard shipping by Canada Post',
				'amount' => $shipping * 100,
				'currency' => 'CAD',
				'quantity' => 1];
		}

		return $lineItems;
	}

	/**
	 * Returns the cart's product rows as a Collection (empty if no active cart).
	 *
	 * @return \Collection
	 */
	public function items()
	{
		$return = new \Collection();

		if( empty( $this->getCartPage() ) )
			return $return;

		$items = $this->getCartPage()->products()->toStructure();

		// Return the empty collection if there are no items
		if( empty( $items ) ) return $return;

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
	public function add( $id, $quantity )
	{
		if( !empty( $quantity ) && $quantity <= 0 )
			return;

		try {
			$quantityToAdd = $quantity ? intval( $quantity ) : 1;
			$idParts = explode( '::', $id ); // $id is formatted uri::uuid
			$uri = $idParts[0];
			$uuid = $idParts[1];
			$item = empty( $this->getCartPage() ) ? null : $this->getCartPage()->products()->toStructure()->findBy( 'suuid', $uuid );
			$items = empty( $this->getCartPage() ) ? [] : $this->getCartPage()->products()->yaml();
			$product = $this->site->page( $uri );
			$variant = $this->site->page( $uri )->variants()->toStructure()->findBy( 'suuid', $uuid );

			if( empty( $item ) ) {
				// Add a new item
				$grantedQty = $this->updateQty( $id, $quantityToAdd );
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
				// Increase the quantity of an existing item
				$newQty = $quantity ? (int)$quantity : (int)$item->quantity()->value + 1;
				$grantedQty = $this->updateQty( $id, $newQty );
				foreach( $items as $key => $i ) {
					if( $i['id'] == $item->id() ) {
						$items[$key]['quantity'] = $grantedQty;
						continue;
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
				Page::create( [
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
						'products' => \Yaml::encode( $items ),
					],
				] );

				$this->session->set( 'txn', $this->txnId );
			} else {
				kirby()->impersonate( 'kirby' );
				$this->getCartPage()->update( ['products' => \Yaml::encode( $items )] );
				$this->cartPage = null;
			}

			if( $grantedQty < $quantityToAdd ) {
				$this->logger->warning( "requested quantity reduced due to stock", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'requested' => $quantityToAdd, 'granted' => $grantedQty] );
			} else {
				$this->logger->info( "item added to cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'quantity' => $grantedQty] );
			}
		} catch( \Exception $e ) {
			$this->logger->error( "failed to add item to cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id, 'reason' => $e->getMessage()] );
		}
	}

	/**
	 * Clamps a requested quantity to what stock allows, accounting for the
	 * quantity of the same variant's option siblings already in the cart.
	 *
	 * @param string $id     Variant id, formatted "uri::variantslug::optionslug"
	 * @param int    $newQty Requested quantity for this option
	 * @return int The grantable quantity (may be less than requested, or 0)
	 */
	public function updateQty( $id, $newQty )
	{
		// $id is formatted uri::variantslug::optionslug
		$idParts = explode( '::', $id );
		$uri = $idParts[0];
		$variantSlug = $idParts[1];

		// Get combined quantity of this option's siblings
		$siblingsQty = 0;
		if( !empty( $this->getCartPage() ) ) {
			foreach( $this->getCartPage()->products()->toStructure() as $item ) {
				if( strpos( $item->id(), $uri . '::' . $variantSlug ) === 0 ) {
					$siblingsQty += $item->quantity()->value;
				}
			}
		}

		foreach( $this->site->page( $uri )->variants()->toStructure() as $variant ) {
			// Store the stock in a variable for quicker processing
			if( !$this->inStock( $variant ) )
				continue;

			$stock = self::inStock( $variant );

			if( $siblingsQty === 0 ) {
				// If there are no siblings
				if( $stock === true or $stock >= $newQty ) {
					// If there is enough stock
					return $newQty;
				} else if( $stock === false ) {
					// If there is no stock
					return 0;
				} else {
					// If there is insufficient stock
					return $stock;
				}
			} else {
				// If there are siblings
				if( $stock === true or $stock >= $newQty ) {
					// If the siblings plus $newQty won't exceed the max stock, go ahead
					return $newQty;
				} else if( $stock === false or $stock <= $siblingsQty ) {
					// If the siblings have already maxed out the stock, return 0
					return $siblingsQty;
				} else if( $stock > $siblingsQty and $stock <= $siblingsQty + $newQty ) {
					// If the siblings don't exceed max stock, but the newQty will, reduce newQty to the appropriate level
					return $siblingsQty;
				}
			}
		}

		// The script should never get to this point
		return 0;
	}

	/**
	 * Removes a line from the cart by its "uri::suuid" id.
	 *
	 * @param string $id Variant id to remove
	 * @return void
	 * @throws \Throwable
	 */
	public function delete( $id )
	{
		$items = $this->getCartPage()->products()->yaml();
		foreach( $items as $key => $i ) {
			if( $i['id'] == $id ) {
				unset( $items[$key] );
			}
		}

		kirby()->impersonate( 'kirby' );
		$this->getCartPage()->update( ['products' => \Yaml::encode( $items )] );
		$this->cartPage = null;

		$this->logger->info( "item removed from cart", ['txn' => $this->session->get( 'txn' ), 'item' => $id] );
	}

	/**
	 * Persists the checkout contact/address block onto the draft order page.
	 *
	 * @param array $customer ['name', 'email', 'address' => [...]] as built by the /address route
	 * @return void
	 */
	public function setCustomer( array $customer )
	{
		kirby()->impersonate( 'kirby' );
		$this->getCartPage()->update( ['customer' => \Yaml::encode( $customer )] );
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
	public function addShipping( $country, $email )
	{
		$region = page( 'prints' )->regions()->toStructure()->findBy( 'country', $country );

		if( empty( $region ) ) {
			$region = 'rest';
		} else {
			$region = $region->name()->value();
		}

		$shippingRegion = page( 'prints' )->shipping()->toStructure()->findBy( 'region', $region );

		if( empty( $shippingRegion ) ) {
			$shippingRegion = page( 'prints' )->shipping()->toStructure()->findBy( 'region', 'rest' );
			$shipping = $shippingRegion->amount()->value();
		} else {
			$shipping = $shippingRegion->amount()->value();
		}

		if( empty( $shipping ) )
			$shipping = 32.32;

		$this->getCartPage()->update( ['shipping' => $shipping] );
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
	public function updateStripeSession( $customerEmail )
	{
		$shipping = empty( $this->getCartPage()->shipping()->value() )
			? 0
			: (float) $this->getCartPage()->shipping()->value();

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
	 * order is still 'pending', so a page reload can't double-process — deducts
	 * inventory, sends notifications and marks the order paid. Failures are
	 * logged, alerted, and surfaced to the visitor via a session `error`.
	 *
	 * @return bool True if the order was finalized (or already was), false on any failure
	 */
	public function processStripe()
	{
		$txn = $this->session->get( 'txn' );

		try {
			if( empty( get( 'sid' ) ) || empty( $txn ) ) {
				$this->logger->debug( "processStripe called with no session id or txn, nothing to do", ['txn' => $txn] );
				return false;
			}

			$storedSid = $this->getCartPage()?->stripe_session_id()->value();
			if( get( 'sid' ) !== $storedSid ) {
				$this->logger->error( "SID mismatch, possible replay attempt", ['txn' => $txn, 'sid' => get( 'sid' ), 'stored_sid' => $storedSid] );
				$this->session->set( 'error', 'Invalid payment session.' );
				return false;
			}

			$stripe = new Stripe();
			$sid = $stripe->retrieveSession( get( 'sid' ) );
			$pi = $stripe->retrievePaymentIntent( $sid->payment_intent );

			// stripe checkout went well
			if( $pi->status == 'succeeded' && $pi->latest_charge->paid ) {
				// order still pending, finalize details
				// check status to avoid repeat processing if client reloads page
				if( $this->getCartPage()->content()->get( 'orderstatus' ) == 'pending' ) {
					$this->logger->info( "payment captured, finalizing order", ['txn' => $txn, 'session_id' => $sid->id, 'payment_intent_id' => $pi->id] );
					$this->updateInventory();
					$this->sendNotifications();
					$this->updateOrder( 'stripe' );
				} else {
					$this->logger->info( "order already finalized, skipping repeat processing", ['txn' => $txn, 'session_id' => $sid->id] );
				}
			} else {
				$this->logger->error( "stripe checkout returned a non-captured transaction", ['txn' => $txn, 'session_id' => $sid->id, 'payment_intent_id' => $pi->id, 'payment_intent_status' => $pi->status] );
				$this->session->set( 'error', 'There was an error with the payment processing, I have been notified of the issue.' );
				return false;
			}
		} catch( \Exception $e ) {
			$this->logger->error( "general error processing stripe payment", ['txn' => $txn, 'reason' => $e->getMessage()] );
			sendAlert( $txn, $this->getCartPage()?->suuid()->value(), $e->getMessage() );
			$this->session->set( 'error', 'There was an unspecified error with the site, I have been notified of this issue. You may try again later' );
			return false;
		}

		return true;

	}

	/**
	 * Deducts each ordered quantity from its variant's stock field.
	 * Throws (aborting order finalization) if a variant is missing or would
	 * go negative.
	 *
	 * @return void
	 * @throws \Throwable
	 */
	private function updateInventory()
	{
		$orderId = $this->getCartPage()->suuid()->value();

		foreach( $this->getCartPage()->products()->yaml() as $item ) {
			$uri = $item['uri'];
			$suuid = !empty( $item['suuid'] ) ? $item['suuid'] : ( explode( '::', $item['id'] )[1] ?? '' );

			$allVariants = $this->site->page( $uri )->variants()->yaml();
			$key = array_search( $suuid, array_column( $allVariants, 'suuid' ) );

			if( $key === false )
				throw new \Exception( "Variant not found for suuid: " . $suuid );

			$variant = $allVariants[$key];

			$remainingStock = intval( $variant['stock'] ) - intval( $item['quantity'] );
			if( $remainingStock < 0 )
				throw new \Exception( "Insufficient stock for product " . page( $uri )->title()->value() . " (uuid: " . $variant['suuid'] . ")" );

			$variant['stock'] = $remainingStock;
			addToStructure( page( $uri ), 'variants', $variant );
		}
		$this->logger->info( "inventory updated after order", ['txn' => $this->session->get( 'txn' ), 'order' => $orderId] );
	}

	/**
	 * Marks the order as paid, renames the draft page to ord-{uuid}, and updates the session state.
	 *
	 * @param string $paymentMethod Payment provider identifier (e.g. 'stripe')
	 * @return void|false Returns false on exception; otherwise void
	 */
	private function updateOrder( $paymentMethod )
	{
		$orderId = null;
		$txn = $this->session->get( 'txn' );

		try {
			$orderId = $this->getCartPage()->suuid()->value();

			kirby()->impersonate( 'kirby' );
			$this->getCartPage()->update( ['title' => "ord-$orderId", 'orderstatus' => 'paid', 'payment' => $paymentMethod] );
			$this->cartPage = null;
			$this->getCartPage()->changeSlug( "ord-$orderId" );
			$this->cartPage = null;
			$this->logger->info( "order status updated to paid", ['txn' => $txn, 'order' => $orderId, 'payment_method' => $paymentMethod] );

			$this->session->set( 'state', 'success' );
			$this->session->set( 'order', str_replace( '_', '-', "ord-$orderId" ) );
			$this->session->remove( 'txn' );
		} catch( \Exception $e ) {
			$this->logger->error( "failed to finalize order", ['txn' => $txn, 'order' => $orderId, 'reason' => $e->getMessage()] );
			sendAlert( $txn, $orderId, $e->getMessage() );
			return false;
		}
	}

	/**
	 * Sends the order-confirmation email to the customer and a copy to the
	 * site owner's alert address. Mail failures are logged and alerted but
	 * don't abort order finalization.
	 *
	 * @return void
	 */
	private function sendNotifications()
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
				\A::merge( $order, [
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
				\A::merge( $order, [
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