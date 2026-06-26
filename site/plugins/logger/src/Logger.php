<?php

namespace Logger;

use \Monolog\Logger as Monolog;
use \Monolog\Handler\RotatingFileHandler;

/**
 * Thin wrapper around Monolog that creates a named, rotating-file logger
 * writing to logs/invis.log relative to the site root.
 */
class Logger
{
	/** @var Monolog */
	protected $logger;

	/**
	 * @param string $type Channel name used to identify log entries (e.g. 'cart', 'stripe')
	 */
	function __construct($type = 'log')
	{
		$this->logger = new Monolog($type);
		$this->logger->setTimezone(new \DateTimeZone('America/Montreal'));
	    $this->logger->pushHandler(new RotatingFileHandler(kirby()->site()->root().'/../logs/invis.log', Monolog::DEBUG));

	    return $this->logger;
	}

	/**
	 * @return Monolog
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}