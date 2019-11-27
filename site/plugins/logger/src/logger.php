<?php

namespace Logger;

use \Monolog\Logger as Monolog;
use \Monolog\Handler\RotatingFileHandler;

class Logger
{
	protected $logger;

	function __construct($type = 'log')
	{
		$this->logger = new Monolog($type);
	    $this->logger->pushHandler(new RotatingFileHandler(kirby()->site()->root().'/../logs/invis.log', Monolog::DEBUG));

	    return $this->logger;
	}

	public function getLogger()
	{
		return $this->logger;
	}
}