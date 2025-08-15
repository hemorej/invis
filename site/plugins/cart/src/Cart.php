<?php

namespace Cart;

use Logger\Logger;
use Kirby\Cms\Page;
use Mailbun\Mailbun;
use Kirby\Uuid\Uuid;
use Kirby\Http\Remote;
use Payments\StripeConnector as Stripe;
use Kirby\Exception\InvalidArgumentException;

class Cart
{
	protected $cache;
	protected $site;
	protected $session;
	protected $txnId;
	protected $logger;

	function __construct()
	{
		$this->cache = kirby()->cache( 'backend' );
		$this->site = kirby()->site();
		$this->session = kirby()->session();
		$instance = new Logger( 'cart' );
		$this->logger = $instance->getLogger();
	}

	/**
	 * @param $total
	 * @return string
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
	 * @param $variant
	 * @return bool|int
	 */
	public static function inStock( $variant )
	{
		if( strstr( $variant->toString(), '::' ) ) {
			$idParts = explode( '::', $variant );
			$uri = $idParts[0];
			$uuid = $idParts[1];

			$variant = page( $uri )->variants()->toStructure()->findBy( 'suuid', $uuid );
			return $variant->stock()->value();
		}

		if( !is_numeric( $variant->stock()->value ) and $variant->stock()->value === '' ) return true;
		if( is_numeric( $variant->stock()->value ) and intval( $variant->stock()->value ) <= 0 ) return false;
		if( is_numeric( $variant->stock()->value ) and intval( $variant->stock()->value ) > 0 ) return intval( $variant->stock()->value );

		return false;
	}

	/**
	 * @param $items
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

	public function subtotal( $items )
	{
		$subtotal = 0;
		foreach( $items as $item ) {
			$itemAmount = $item->amount()->value;
			$subtotal += $itemAmount * intval( $item->quantity()->value );
		}
		return $subtotal;
	}

	public function getCartPage()
	{
		if( empty( $this->session->get( 'txn' ) ) ) {
			return null;
		}

		return page( 'prints/orders' )->draft( $this->session->get( 'txn' ) );
	}

	/**
	 * @param $discount
	 * @param $shipping
	 * @return array
	 */
	public function getLineItems( $discount = 1, $shipping = 0 )
	{
		$lineItems = [];
		$products = $this->getCartPage()->products()->toStructure();
		foreach( $products as $product ) {
			$preview = $this->site->page( $product->uri()->value )->images()->first()->crop( 100 )->url();
			$lineItems[] = [
				'price_external_id' => $product->price_external_id()->value,
				'name' => $product->variant()->value,
				'description' => $product->name()->value,
				'amount' => $product->amount()->value * 100 * $discount,
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
	 * @param $id
	 * @param $quantity
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
					'quantity' => $this->updateQty( $id, $quantityToAdd ),
				];
			} else {
				// Increase the quantity of an existing item
				foreach( $items as $key => $i ) {
					if( $i['id'] == $item->id() ) {
						$newQty = $quantity ? (int)$quantity : (int)$item->quantity()->value + 1;
						$items[$key]['quantity'] = $this->updateQty( $id, $newQty );
						continue;
					}
				}
			}

			// Create the transaction file if we don't have one yet
			if( empty( $this->session->get( 'txn' ) ) || empty( $this->getCartPage() ) ) {
				$this->txnId = $this->session->startTime() . $this->session->expiryTime();
				$timestamp = time();

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
			}
		} catch( Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * @param $id
	 * @param $newQty
	 * @return bool|int|mixed
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
	 * @param $id
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
	}

	/**
	 * @param $discountCode
	 * @return array|int[]
	 * @throws \Throwable
	 */
	public function applyDiscount( $discountCode )
	{
		$discounts = kirby()->site()->page( 'prints' )->discounts()->yaml();
		foreach( $discounts as $discount ) {
			if( $discount['code'] == $discountCode && boolval( $discount['active'] ) == true ) {

				kirby()->impersonate( 'kirby' );
				$this->getCartPage()->update( ['discount' => \Yaml::encode( $discount )] );

				$subtotal = $this->subtotal( $this->items() );
				$total = $subtotal - ( intval( $discount['amount'] ) / 100 ) * $subtotal;
				$currencies = $this->estimateCurrency( $total );

				$lineItems = $this->getLineItems( 1 - ( intval( $discount['amount'] ) / 100 ) );
				$stripeSession = ( new Stripe() )->createSession( $lineItems )->id;

				return ['total' => $total, 'currencies' => $currencies, 'discountAmount' => intval( $discount['amount'] ), 'checkoutSessionId' => $stripeSession];
			}
		}

		return ['total' => 0];
	}

	/**
	 * @param $country
	 * @param $email
	 * @return array
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

		// just in case, technically should never apply
		if( empty( $shipping ) )
			$shipping = 32.32;

		// add to cart/order
		$this->getCartPage()->update( ['shipping' => $shipping] );
		$stripeSession = $this->updateStripeSession( $email );

		// recompute totals for frontend
		$discount = $this->getCartPage()->discount()->value;

		if( empty( $discount ) ) {
			$discountPercentage = 0;
			$discount = 0;
		} else {
			$discountPercentage = $this->getCartPage()->discount()->yaml()['amount'];
			$discount = ( intval( $discountPercentage ) / 100 );
		}


		$subtotal = $this->subtotal( $this->items() );
		$total = ( ( 1 - $discount ) * $subtotal ) + $shipping;
		$currencies = $this->estimateCurrency( $total );
		$lineItems = $this->getLineItems( 1 - $discount, $shipping );

		return ['total' => $total, 'currencies' => $currencies, 'shipping' => $shipping, 'checkoutSessionId' => $stripeSession, 'items' => $lineItems, 'discount' => $discountPercentage];

	}

	/**
	 * @param $customerEmail
	 * @return string
	 * @throws \Exception
	 */
	public function updateStripeSession( $customerEmail )
	{
		if( empty( $this->getCartPage()->discount()->value() ) ) {
			$discount = 1;
		} else {
			$discount = $this->getCartPage()->discount()->yaml();
			$discount = 1 - ( intval( $discount['amount'] ) / 100 );
		}

		if( empty( $this->getCartPage()->shipping() ) ) {
			$shipping = 0;
		} else {
			$shipping = $this->getCartPage()->shipping()->value();
		}

		$lineItems = $this->getLineItems( $discount, $shipping );
		$stripeSession = ( new Stripe() )->createSession( $lineItems, $customerEmail )->id;

		return $stripeSession;
	}

	/**
	 * @return bool
	 */
	public function processStripe()
	{
		try {
			// request must come from stripe with a valid SID
			// following a successful store session
			if( empty( get( 'sid' ) ) || empty( $this->session->get( 'txn' ) ) )
				return false;

			$stripe = new Stripe();
			$sid = $stripe->retrieveSession( get( 'sid' ) );
			$pi = $stripe->retrievePaymentIntent( $sid->payment_intent );

			// stripe checkout went well
			if( $pi->status == 'succeeded' && $pi->latest_charge->data[0]->paid == true ) {
				// order still pending, finalize details
				// check status to avoid repeat processing if client reloads page
				if( $this->getCartPage()->content()->get( 'orderstatus' ) == 'pending' ) {
					$this->updateInventory();
					$this->sendNotifications();
					$this->updateOrder( 'stripe' );
				}
			} else {
				$this->logger->error( $this->session->get( 'txn' ) . ": Stripe checkout returned a non-captured transaction", [$sid->id, $pi->id] );
				$this->session->set( 'error', 'There was an error with the payment processing, I have been notified of the issue.' );
				return false;
			}
		} catch( \Exception $e ) {
			$this->logger->error( $this->session->get( 'txn' ) . ": general error", ['reason' => $e->getMessage()] );
			sendAlert( $this->session->get( 'txn' ), $this->getCartPage()->suuid()->value(), $e->getMessage() );
			$this->session->set( 'error', 'There was an unspecified error with the site, I have been notified of this issue. You may try again later' );
			return false;
		}

		return true;

	}

	/**
	 * @return void
	 * @throws \Throwable
	 */
	private function updateInventory()
	{
		$orderId = $this->getCartPage()->suuid()->value();

		foreach( $this->items() as $item ) {
			$uri = $item->uri()->value;
			$variantStructure = $this->site->page( $uri )->variants()->findBy( 'suuid', $item->suuid()->value() )->yaml();
			$variant = $variantStructure[0];

			$updatedVariant = [];
			$updatedVariant['suuid'] = $variant['suuid'];
			$updatedVariant['name'] = $variant['name'];
			$updatedVariant['price'] = $variant['price'];

			$remainingStock = intval( $variant['stock'] ) - intval( $item->quantity()->value );
			if( $remainingStock < 0 )
				throw new \Exception( "Insufficient stock for product " . page( $uri )->title()->value() . " (uuid: " . $variant['suuid'] . ")" );

			$updatedVariant['stock'] = $remainingStock;

			addToStructure( page( $uri ), 'variants', $updatedVariant );
		}
		$this->logger->info( "inventory updated after order " . $orderId );
	}

	private function updateOrder( $paymentMethod )
	{
		try {
			$orderId = $this->getCartPage()->suuid()->value();

			kirby()->impersonate( 'kirby' );
			$this->getCartPage()->update( ['title' => "ord-$orderId", 'orderstatus' => 'paid', 'payment' => $paymentMethod] );
			$this->getCartPage()->changeSlug( "ord-$orderId" );
			$this->logger->info( $this->session->get( 'txn' ) . ": order status updated" );

			$this->session->set( 'state', 'success' );
			$this->session->set( 'order', str_replace( '_', '-', "ord-$orderId" ) );
			$this->session->remove( 'txn' );
		} catch( \Exception $e ) {
			$this->logger->error( $this->session->get( 'txn' ) . ": general error", ['reason' => $e->getMessage()] );
			sendAlert( $this->session->get( 'txn' ), $orderId, $e->getMessage() );
			return false;
		}
	}

	/**
	 * @return void
	 */
	private function sendNotifications()
	{
		$orderId = $this->getCartPage()->suuid()->value();
		$customer = $this->getCartPage()->customer()->yaml();
		$products = $this->getCartPage()->products();
		$discount = $this->getCartPage()->discount()->yaml();
		$shipping = $this->getCartPage()->shipping()->yaml();
		$subtotal = $this->subtotal( $this->items() );

		if( !empty( $discount ) ) {
			$total = $subtotal - ( intval( $discount['amount'] ) / 100 ) * $subtotal;
		} else {
			$total = $subtotal;
		}

		$total += $shipping[0];

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
			'discount' => empty( $discount['code'] ) ? null : $discount['code'],
			'discountAmount' => empty( $discount['amount'] ) ? null : $discount['amount'],
			'shipping' => $shipping[0],
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

			$this->logger->info( $this->session->get( 'txn' ) . ":email confirmation sent for order id " . $orderId );

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

			$this->logger->info( $this->session->get( 'txn' ) . ":admin notification sent for order id " . $orderId );
		} catch( \Error $err ) {
			$description = "email confirmation error for order id " . $orderId . ": " . $err->getMessage();
			$this->logger->error( $this->session->get( 'txn' ) . ":" . $description );
			sendAlert( $this->session->get( 'txn' ), $orderId, $description );
		} catch( \Exception $e ) {
			$description = "email confirmation error for order id " . $orderId . ": " . $e->getMessage();
			$this->logger->error( $this->session->get( 'txn' ) . ":" . $description );
			sendAlert( $this->session->get( 'txn' ), $orderId, $description );
		}
	}

}