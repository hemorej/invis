<?php

use Logger\Logger;
use Kirby\Cms\Page;
use Payments\StripeConnector;

/**
 * Routes page.update:after hook events to the appropriate domain handler
 * (ShippingHandler for orders, ProductHandler for prints).
 */
class PageUpdateHandler
{
	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * @return void
	 */
	function __construct()
	{
		$this->logger = ( new Logger( 'pageUpdateHandler' ) )->getLogger();
	}

	/**
	 * Dispatches the page update to the correct handler based on the parent page.
	 *
	 * @param Page $page    The updated page
	 * @param Page $oldPage The page state before the update
	 * @return void
	 */
	public function handle( Page $page, Page $oldPage )
	{
		$parentUid = $page->parent()?->uid();

		if( $parentUid == 'orders' ) {
			$this->notify( $page, $oldPage );
		} elseif( $parentUid == 'prints' ) {
			$this->createOrUpdate( $page, $oldPage );
		} else {
			$this->logger->debug( "page update didn't match a known handler, nothing to do", ['page' => $page->id(), 'parent' => $parentUid] );
		}
	}

	/**
	 * Delegates product creation/update to ProductHandler.
	 *
	 * @param Page $page    The updated page
	 * @param Page $oldPage The page state before the update
	 * @return void
	 */
	public function createOrUpdate( $page, $oldPage )
	{
		$this->logger->debug( "delegating page update to product handler", ['page' => $page->id()] );

		$productHandler = new ProductHandler();
		$productHandler->createOrUpdate( $page, $oldPage );
	}

	/**
	 * Delegates shipping notification to ShippingHandler.
	 *
	 * @param Page $page    The updated order page
	 * @param Page $oldPage The order page state before the update
	 * @return void
	 */
	public function notify( $page, $oldPage )
	{
		$this->logger->debug( "delegating page update to shipping handler", ['page' => $page->id()] );

		$shippingHandler = new ShippingHandler();
		$shippingHandler->notify( $page, $oldPage );
	}
}