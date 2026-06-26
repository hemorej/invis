<?php

namespace Mailbun;
use \Logger\Logger;
use Mailgun\Mailgun;
use Mailgun\HttpClient\HttpClientConfigurator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Buzz\Client\Curl;
use \Kirby\Cms\App;

/**
 * Sends transactional emails via the Mailgun API using Kirby templates for the HTML body.
 */
class Mailbun
{
	/** @var Mailgun */
	protected $mailgun;

	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * @return void
	 */
	function __construct()
	{
		$psr17Factory = new Psr17Factory();
		$httpClient = new Curl($psr17Factory);
		$configurator = (new HttpClientConfigurator())
			->setApiKey(kirby()->option('mailgun_key'))
			->setUriFactory($psr17Factory)
			->setHttpClient($httpClient);
		$this->mailgun = new Mailgun($configurator);
		$instance = new Logger('mailer');
		$this->logger = $instance->getLogger();
	}

	/**
	 * Sends an HTML email rendered from a Kirby email template.
	 *
	 * @param string $recipient Recipient email address
	 * @param string $subject   Email subject line
	 * @param string $template  Template name under site/templates/emails/
	 * @param array  $data      Variables passed to the template
	 * @return void
	 */
	public function send($recipient, $subject, $template, $data)
	{
		$body = App::instance()->template('emails/' . $template);
		
		$data['kirby'] = kirby();
		$data['site'] = kirby()->site();
		$data['pages'] = [];
		$data['page'] = kirby()->page();

		$this->mailgun->messages()->send(kirby()->option('mailgun_domain'), [
	      'to'      => $recipient,
	      'from'    => kirby()->option('from_address'),
	      'subject' => $subject,
	      'h:Reply-To' => kirby()->option('reply-to_address'),
	      'o:require-tls' => 'true',
	      'text' => $subject,
	      'html' => $body->render($data)
		]);

		$this->logger->info('email message successfully sent');
	}
}