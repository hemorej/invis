<?php

use Logger\Logger;
use Kirby\Cms\Page;
use Payments\StripeConnector;

/**
 * Handles Stripe product sync when a print page is updated in the CMS.
 */
class ProductHandler
{
	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * @return void
	 */
	function __construct()
	{
		$this->logger = ( new Logger( 'product' ) )->getLogger();
	}

	/**
	 * Creates or updates the corresponding Stripe product when a listed print page changes.
	 *
	 * Only `print` products are sold; books are display-only and never synced.
	 *
	 * @param Page $page    The updated page
	 * @param Page $oldPage The page state before the update
	 * @return void
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function createOrUpdate( Page $page, Page $oldPage )
	{
		if( $page->content()->type()->value === 'print' && $page->isListed() )
		{
			$this->logger->info( "syncing print page to Stripe", ['page' => $page->id(), 'type' => $page->content()->type()->value] );
			$stripe = new StripeConnector();
			$stripe->createOrUpdateProduct($page);
		} else {
			$this->logger->debug( "print page update skipped, not a listed product", ['page' => $page->id()] );
		}
	}
}