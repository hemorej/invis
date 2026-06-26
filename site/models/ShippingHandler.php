<?php

use Logger\Logger;
use Mailbun\Mailbun;

/**
 * Sends a shipping confirmation email to the customer when an order status changes to "shipped".
 */
class ShippingHandler
{
	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * @return void
	 */
	function __construct()
	{
		$this->logger = ( new Logger( 'shipping' ) )->getLogger();
	}

	/**
	 * Sends a shipping confirmation email if the order status transitions to "shipped".
	 *
	 * @param \Kirby\Cms\Page $page    The updated order page
	 * @param \Kirby\Cms\Page $oldPage The order page state before the update
	 * @return void
	 */
	public function notify( $page, $oldPage )
	{
		$this->logger->info( "handler called notify" );
		$status = (string)$page->content()->get( 'orderstatus' );
		$oldStatus = (string)$oldPage->content()->get( 'orderstatus' );

		if( $status == 'shipped' && $oldStatus != $status ) {
			$customer = $page->customer()->yaml();

			$collection = new \Collection();
			$subtotal = 0;

			$items = $page->products()->toStructure();
			foreach( $items as $key => $item ) {
				$collection->append( $key, $item );
				$subtotal += intval( $item->quantity()->value * $item->amount()->value );
			}

			$shipping = (float) $page->shipping()->value;
			$total = $subtotal + $shipping;

			try {
				$mailbun = new Mailbun();
				$mailbun->send( $customer['email'], 'Your order from The Invisible Cities has been shipped', 'confirm', [
					'order' => $page->suuid(),
					'items' => $items,
					'fullName' => $customer['name'],
					'street1' => $customer['address']['address_line_1'],
					'street2' => $customer['address']['address_line_2'],
					'city' => $customer['address']['city'],
					'province' => $customer['address']['state'],
					'country' => $customer['address']['country'],
					'postcode' => $customer['address']['postal_code'],
					'email' => $customer['email'],
					'shipping' => $shipping,
					'total' => $total,
					'title' => 'Your order from The Invisible Cities has been shipped',
					'subtitle' => 'Shipping confirmation',
					'preview' => 'Order shipping confirmation. Your order has been shipped.',
					'headline' => 'Your order is on the way! Delivery is normally 5-10 business days to the US and Europe, but shipping times may vary.',
					'type' => 'order',
				] );

				$this->logger->info( "email shipping confirmation sent for order id " . $page->suuid() );
			} catch( \Throwable $t ) {
				$this->logger->error( "email shipping confirmation error for order id " . $page->suuid() . ": " . $t->getMessage() );
			}

			$page->update( [
				'shipping_date' => date( 'm/d/Y H:i:s', time() ),
			] );
		}
	}
}