<?php

use Logger\Logger;
use Kirby\Cms\Page;
use Payments\StripeConnector;

class PageUpdateHandler
{
	protected $logger;

	function __construct()
	{
		$this->logger = ( new Logger( 'pageUpdateHandler' ) )->getLogger();
	}

	/**
	 * @param Page $page
	 * @param Page $oldPage
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
	 * @param $page
	 * @param $oldPage
	 * @return void
	 */
	public function createOrUpdate( $page, $oldPage )
	{
		$this->logger->info( "handler called createOrUpdate, delegating to product handler" );

		$productHandler = new ProductHandler();
		$productHandler->createOrUpdate( $page, $oldPage );
	}

	/**
	 * @param $page
	 * @param $oldPage
	 * @return void
	 */
	public function notify( $page, $oldPage )
	{
		$this->logger->info( "handler called notify, delegating to shipping handler" );

		$shippingHandler = new ShippingHandler();
		$shippingHandler->notify( $page, $oldPage );
	}
}