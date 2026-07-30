<?php

namespace Logger;

use \Monolog\Logger as Monolog;
use \Monolog\Handler\RotatingFileHandler;
use \Monolog\Formatter\JsonFormatter;
use \Monolog\Processor\UidProcessor;

/**
 * Thin wrapper around Monolog that creates a named, rotating-file logger
 * writing structured (JSON) entries to logs/invis.log relative to the site root.
 *
 * Every entry carries a request-scoped `extra.uid` (constant for the lifetime
 * of the PHP process/request) so log lines from different channels emitted
 * while handling the same request can be correlated.
 */
class Logger
{
	/** @var Monolog */
	protected $logger;

	/** @var UidProcessor Shared across channels so request_id matches within a request */
	protected static $requestIdProcessor;

	/**
	 * @param string $channel Channel name used to identify log entries (e.g. 'cart', 'stripe')
	 */
	function __construct($channel = 'log')
	{
		$this->logger = new Monolog($channel);
		$this->logger->setTimezone(new \DateTimeZone('America/Montreal'));

		$level = kirby()->option('debug') ? Monolog::DEBUG : Monolog::INFO;

		$handler = new RotatingFileHandler(kirby()->site()->root().'/../logs/invis.log', 30, $level);
		$handler->setFormatter(new JsonFormatter());

		$this->logger->pushHandler($handler);
		$this->logger->pushProcessor(self::requestIdProcessor());

		return $this->logger;
	}

	/**
	 * @return UidProcessor
	 */
	protected static function requestIdProcessor()
	{
		if (self::$requestIdProcessor === null) {
			self::$requestIdProcessor = new UidProcessor(8);
		}

		return self::$requestIdProcessor;
	}

	/**
	 * @return Monolog
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}
