<?php

namespace Logger;
use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;

class Logger
{
	function __construct($type = 'log')
	{
		$logger = new Logger($type);
	    $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));

	    return $logger;
	}
}