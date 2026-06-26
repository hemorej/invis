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
		if( !empty( $page->parent() ) && $page->parent()->uid() == 'orders' )
			$this->notify( $page, $oldPage );

		if( !empty( $page->parent() ) && $page->parent()->uid() == 'prints')
			$this->createOrUpdate( $page, $oldPage );

		$this->logger->info( "handler didn't match expected, nothing to do" );
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
		$this->logger->info( "handler called createOrUpdate, delegating to product handler" );

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
		$this->logger->info( "handler called notify, delegating to shipping handler" );

		$shippingHandler = new ShippingHandler();
		$shippingHandler->notify( $page, $oldPage );
	}
}