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
	 * @param Page $page    The updated page
	 * @param Page $oldPage The page state before the update
	 * @return void
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function createOrUpdate( Page $page, Page $oldPage )
	{
		$this->logger->info( "handler called createOrUpdate" );

		$productTypes = $page->blueprint()->field('type');
		if( in_array($page->content()->type()->value,  $productTypes['options'] ) && $page->isListed() )
		{
			$stripe = new StripeConnector();
			$stripe->createOrUpdateProduct($page);
		}
	}
}