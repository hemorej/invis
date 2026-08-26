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
	 * @param string      $recipient Recipient email address
	 * @param string      $subject   Email subject line
	 * @param string      $template  Template name under site/templates/emails/
	 * @param array       $data      Variables passed to the template
	 * @param string|null $from      From header override; defaults to the from_address option
	 * @return void
	 */
	public function send($recipient, $subject, $template, $data, $from = null)
	{
		$body = App::instance()->template('emails/' . $template);

		$data['kirby'] = kirby();
		$data['site'] = kirby()->site();
		$data['pages'] = [];
		$data['page'] = kirby()->page();

		try {
			$this->mailgun->messages()->send(kirby()->option('mailgun_domain'), [
		      'to'      => $recipient,
		      'from'    => $from ?? kirby()->option('from_address'),
		      'subject' => $subject,
		      'h:Reply-To' => kirby()->option('reply-to_address'),
		      'o:require-tls' => 'true',
		      'text' => $subject,
		      'html' => $body->render($data)
			]);
		} catch( \Throwable $t ) {
			$this->logger->error('email send failed', ['recipient' => maskEmail($recipient), 'subject' => $subject, 'template' => $template, 'reason' => $t->getMessage()]);
			throw $t;
		}

		$this->logger->info('email sent', ['recipient' => maskEmail($recipient), 'subject' => $subject, 'template' => $template]);
	}

	/**
	 * Sends the same HTML email to many recipients via Mailgun's batch
	 * sending (recipient-variables), so each recipient gets their own copy
	 * and never sees the other addresses on the send — no BCC needed.
	 * Chunks the list into groups of 1000, Mailgun's limit per API call.
	 *
	 * @param string[]    $recipients
	 * @param string      $subject
	 * @param string      $template Template name under site/templates/emails/
	 * @param array       $data     Variables passed to the template
	 * @param string|null $from     From header override; defaults to the from_address option
	 * @return int Number of recipients the email was sent to
	 */
	public function sendBulk($recipients, $subject, $template, $data, $from = null)
	{
		$body = App::instance()->template('emails/' . $template);

		$data['kirby'] = kirby();
		$data['site'] = kirby()->site();
		$data['pages'] = [];
		$data['page'] = kirby()->page();

		$html = $body->render($data);
		$domain = kirby()->option('mailgun_domain');
		$sent = 0;

		foreach (array_chunk($recipients, 1000) as $chunk) {
			try {
				$this->mailgun->messages()->send($domain, [
			      'to'      => $chunk,
			      'from'    => $from ?? kirby()->option('from_address'),
			      'subject' => $subject,
			      'h:Reply-To' => kirby()->option('reply-to_address'),
			      'o:require-tls' => 'true',
			      'text' => $subject,
			      'html' => $html,
			      'recipient-variables' => json_encode(array_fill_keys($chunk, new \stdClass()), JSON_FORCE_OBJECT),
				]);
			} catch( \Throwable $t ) {
				$this->logger->error('bulk email send failed', ['recipients' => count($chunk), 'subject' => $subject, 'template' => $template, 'reason' => $t->getMessage()]);
				throw $t;
			}

			$sent += count($chunk);
		}

		$this->logger->info('bulk email sent', ['recipients' => $sent, 'subject' => $subject, 'template' => $template]);

		return $sent;
	}
}