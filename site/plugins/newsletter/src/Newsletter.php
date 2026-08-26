<?php

namespace Newsletter;

use Logger\Logger;
use Mailbun\Mailbun;
use Kirby\Cms\Page;
use Kirby\Uuid\Uuid;

/**
 * Manages newsletter subscribers backed by Kirby draft pages under
 * newsletter/subscribers. Handles signup with double opt-in, duplicate
 * detection via a keyed digest, confirmation, unsubscribe and export of
 * the confirmed list for manual sends.
 */
class Newsletter
{
	/** @var \Kirby\Cache\Cache */
	protected $cache;

	/** @var \Monolog\Logger */
	protected $logger;

	/**
	 * @return void
	 */
	function __construct()
	{
		$this->cache = kirby()->cache( 'newsletter.newsletter.backend' );
		$instance = new Logger( 'newsletter' );
		$this->logger = $instance->getLogger();
	}

	/**
	 * @return Page
	 */
	protected function subscribersPage()
	{
		return page( 'newsletter/subscribers' );
	}

	/**
	 * @param string $email
	 * @return Page|null
	 * @throws \Exception
	 */
	public function findByEmail( string $email )
	{
		$digest = Crypto::emailDigest( $email );
		return $this->subscribersPage()->childrenAndDrafts()->findBy( 'email_hmac', $digest );
	}

	/**
	 * Fixed-window rate limit, e.g. 5 requests per 60 seconds per key.
	 *
	 * @param string $key
	 * @param int $limit
	 * @param int $window Seconds
	 * @return bool True if the request should be rejected
	 */
	public function isRateLimited( string $key, int $limit = 5, int $window = 60 )
	{
		$cacheKey = 'throttle-' . $key;
		$count = (int) $this->cache->get( $cacheKey, 0 );

		if( $count >= $limit )
			return true;

		$this->cache->set( $cacheKey, $count + 1, ceil( $window / 60 ) );
		return false;
	}

	/**
	 * @param string $email
	 * @return array ['status' => 'pending'|'already_subscribed'|'resent']
	 * @throws \Throwable
	 */
	public function subscribe( string $email )
	{
		$existing = $this->findByEmail( $email );

		if( $existing && $existing->substatus()->value() === 'confirmed' ) {
			$this->logger->info( 'signup attempt for already-confirmed email', ['email' => maskEmail( $email )] );
			return ['status' => 'already_subscribed'];
		}

		if( $existing ) {
			try {
				$this->sendConfirmation( $existing, $email );
			} catch( \Throwable $t ) {
				$this->logger->error( 'failed to resend confirmation email', ['email' => maskEmail( $email ), 'reason' => $t->getMessage()] );
				return ['status' => 'mail_error'];
			}

			$this->logger->info( 'confirmation email resent', ['email' => maskEmail( $email )] );
			return ['status' => 'resent'];
		}

		kirby()->impersonate( 'kirby' );
		$subscriber = Page::create( [
			'parent' => $this->subscribersPage(),
			'slug' => bin2hex( random_bytes( 12 ) ),
			'template' => 'newsletter-subscriber',
			'draft' => true,
			'content' => [
				'email_enc' => Crypto::encrypt( Crypto::normalizeEmail( $email ) ),
				'email_hmac' => Crypto::emailDigest( $email ),
				'substatus' => 'pending',
				'created' => date( 'Y-m-d H:i:s' ),
			],
		] );

		try {
			$this->sendConfirmation( $subscriber, $email );
		} catch( \Throwable $t ) {
			$this->logger->error( 'failed to send confirmation email, subscriber left pending', ['email' => maskEmail( $email ), 'reason' => $t->getMessage()] );
			return ['status' => 'mail_error'];
		}

		$this->logger->info( 'new subscriber created, confirmation email sent', ['email' => maskEmail( $email )] );

		return ['status' => 'pending'];
	}

	/**
	 * @param Page $subscriber
	 * @param string $email
	 * @return void
	 */
	protected function sendConfirmation( Page $subscriber, string $email )
	{
		$token = Crypto::token( $subscriber->uuid()->id(), 'confirm' );
		$confirmUrl = url( 'newsletter/confirm/' . $token );

		( new Mailbun() )->send(
			$email,
			'Confirm your subscription to The Invisible Cities newsletter',
			'newsletter-confirm',
			[
				'title' => 'Confirm your subscription',
				'subtitle' => 'Confirm your subscription',
				'preview' => 'One more step to confirm your subscription to the newsletter.',
				'headline' => 'Thanks for signing up! Click the button below to confirm your email address and start receiving the newsletter.',
				'confirmUrl' => $confirmUrl,
			]
		);
	}

	/**
	 * @param string $token
	 * @return bool
	 * @throws \Throwable
	 */
	public function confirm( string $token )
	{
		$uuid = Crypto::verifyToken( $token, 'confirm' );
		if( $uuid === false )
			return false;

		$subscriber = Uuid::for( 'page://' . $uuid )->model();
		if( empty( $subscriber ) || $subscriber->substatus()->value() !== 'pending' )
			return false;

		kirby()->impersonate( 'kirby' );
		$subscriber->update( ['substatus' => 'confirmed'] );

		$this->logger->info( 'subscriber confirmed', ['uuid' => $uuid] );
		return true;
	}

	/**
	 * @param string $email
	 * @return bool True if a matching subscriber was found and removed
	 * @throws \Throwable
	 */
	public function unsubscribe( string $email )
	{
		$subscriber = $this->findByEmail( $email );
		if( empty( $subscriber ) )
			return false;

		kirby()->impersonate( 'kirby' );
		$subscriber->delete( true );

		$this->logger->info( 'subscriber unsubscribed and removed', ['email' => maskEmail( $email )] );
		return true;
	}

	/**
	 * Decrypts and returns every confirmed subscriber's email.
	 *
	 * @return string[]
	 * @throws \Exception
	 */
	public function confirmedEmails()
	{
		$emails = [];
		foreach( $this->subscribersPage()->drafts()->filterBy( 'substatus', 'confirmed' ) as $subscriber ) {
			$emails[] = Crypto::decrypt( $subscriber->email_enc()->value() );
		}

		return $emails;
	}

	/**
	 * Sends a newsletter edition's content to every confirmed subscriber.
	 *
	 * @param Page $edition
	 * @return array ['status' => 'sent'|'no_subscribers'|'mail_error', 'count' => int]
	 */
	public function sendEdition( Page $edition )
	{
		$emails = $this->confirmedEmails();

		if( empty( $emails ) ) {
			$this->logger->info( 'newsletter edition send skipped, no confirmed subscribers', ['edition' => $edition->id()] );
			return ['status' => 'no_subscribers', 'count' => 0];
		}

		try {
			$sent = ( new Mailbun() )->sendBulk(
				$emails,
				$edition->title()->value(),
				'newsletter',
				[
					'title' => $edition->title()->value(),
					'content' => $edition->text()->value(),
				]
			);
		} catch( \Throwable $t ) {
			$this->logger->error( 'newsletter edition send failed', ['edition' => $edition->id(), 'reason' => $t->getMessage()] );
			return ['status' => 'mail_error', 'count' => 0];
		}

		$this->logger->info( 'newsletter edition sent', ['edition' => $edition->id(), 'count' => $sent] );
		return ['status' => 'sent', 'count' => $sent];
	}
}
