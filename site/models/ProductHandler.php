<?php

use Logger\Logger;
use Kirby\Cms\Page;
use Payments\StripeConnector;

class ProductHandler
{
	protected $logger;

	function __construct()
	{
		$this->logger = ( new Logger( 'product' ) )->getLogger();
	}

	/**
	 * @param $page
	 * @param $oldPage
	 * @return void
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function createOrUpdate( $page, $oldPage )
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