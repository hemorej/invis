<?php

namespace Mailbun;
use \Logger\Logger;
use Mailgun\Mailgun;
use \Kirby\Cms\App;

class Mailbun
{
	protected $mailgun;

	function __construct()
	{
		$this->mailgun = Mailgun::create(kirby()->option('mailgun_key'));
		$instance = new Logger('mailer');
		$this->logger = $instance->getLogger();
	}

	public function send($recipient, $subject, $template, $data)
	{
		$body = App::instance()->template('emails/' . $template);

		$this->mailgun->messages()->send(kirby()->option('mailgun_domain'), [
	      'to'      => $recipient,
	      'from'    => kirby()->option('from_address'),
	      'subject' => $subject,
	      'o:require-tls' => 'true',
	      'text' => $subject,
	      'html' => $body->render($data)
		]);

		$this->logger->info('email message successfully sent');
	}
}