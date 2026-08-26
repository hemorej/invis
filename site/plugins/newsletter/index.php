<?php

require_once __DIR__ . '/src/Crypto.php';
require_once __DIR__ . '/src/Design.php';
require_once __DIR__ . '/src/Newsletter.php';

use Newsletter\Newsletter;
use Kirby\Exception\PermissionException;
use Kirby\Http\Response;
use Kirby\Uuid\Uuid;

/**
 * Looks up a newsletter edition by uuid, throwing if it doesn't exist or
 * isn't actually an edition page.
 *
 * @param string $uuid
 * @return \Kirby\Cms\Page
 * @throws PermissionException
 */
function newsletterEditionForUuid( string $uuid )
{
	$edition = Uuid::for( 'page://' . $uuid )->model();
	if( empty( $edition ) || $edition->intendedTemplate()->name() !== 'newsletter-edition' )
		throw new PermissionException( 'Not a newsletter edition' );

	return $edition;
}

/**
 * Renders a plain confirmation page before actually sending an edition to
 * every confirmed subscriber. Deliberately plain server-rendered HTML (no
 * Panel Vue component) since the project runs with panel.vue.compiler
 * disabled and has no Panel JS build step.
 *
 * @param \Kirby\Cms\Page $edition
 * @param int $count
 * @return string
 */
function newsletterSendConfirmView( $edition, int $count )
{
	$title = htmlspecialchars( $edition->title()->value(), ENT_QUOTES );
	$action = htmlspecialchars( url( 'newsletter/panel/send/' . $edition->uuid()->id() ), ENT_QUOTES );
	$csrf = htmlspecialchars( csrf(), ENT_QUOTES );

	return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Send newsletter edition</title>
<style>
	body { font-family: -apple-system, Helvetica, Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 20px; color: #222; }
	button { margin-top: 12px; padding: 10px 18px; cursor: pointer; }
	p.count { color: #666; font-size: 14px; }
</style>
</head>
<body>
	<h1>Send "{$title}"</h1>
	<p class="count">This will email {$count} confirmed subscriber(s). This can't be undone.</p>
	<form method="post" action="{$action}">
		<input type="hidden" name="csrf" value="{$csrf}">
		<button type="submit">Send now</button>
	</form>
</body>
</html>
HTML;
}

/**
 * Renders the result of a send attempt.
 *
 * @param string $message
 * @return string
 */
function newsletterSendResultView( string $message )
{
	$messageAttr = htmlspecialchars( $message, ENT_QUOTES );

	return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Newsletter send</title>
<style>
	body { font-family: -apple-system, Helvetica, Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 20px; color: #222; }
</style>
</head>
<body>
	<p>{$messageAttr}</p>
</body>
</html>
HTML;
}

Kirby::plugin( 'newsletter/newsletter', [
	'options' => [
		'cache.backend' => true,
	],
	'routes' => [
		[
			'pattern' => 'newsletter/subscribe',
			'method' => 'POST',
			'action' => function () {
				$logger = ( new \Logger\Logger( 'newsletter' ) )->getLogger();
				$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

				if( csrf( get( 'csrf' ) ) !== true ) {
					$logger->warning( 'newsletter subscribe rejected, invalid CSRF token', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'Something went wrong, please try again.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				// Honeypot: bots fill hidden fields, humans don't. Pretend success either way.
				if( !empty( get( 'website' ) ) ) {
					$logger->info( 'newsletter subscribe honeypot triggered', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'success', 'message' => 'Thanks! Please check your inbox to confirm your subscription.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				$newsletter = new Newsletter();
				if( $newsletter->isRateLimited( 'subscribe-' . $ip ) ) {
					$logger->warning( 'newsletter subscribe rate limited', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'Too many requests, please try again in a minute.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				$email = substr( trim( get( 'email' ) ?? '' ), 0, 254 );
				if( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'Please enter a valid email address.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				$result = $newsletter->subscribe( $email );

				$messages = [
					'pending' => ['type' => 'success', 'message' => 'Thanks! Please check your inbox to confirm your subscription.'],
					'resent' => ['type' => 'success', 'message' => 'You already have a pending confirmation, we just sent you another one.'],
					'already_subscribed' => ['type' => 'success', 'message' => 'This email is already subscribed to the newsletter.'],
					'mail_error' => ['type' => 'error', 'message' => 'Something went wrong sending the confirmation email, please try again shortly.'],
				];

				kirby()->session()->set( 'notice', $messages[$result['status']] );
				return Response::redirect( url( 'newsletter' ) );
			}
		],
		[
			'pattern' => 'newsletter/confirm/(:any)',
			'method' => 'GET',
			'action' => function ( $token ) {
				$logger = ( new \Logger\Logger( 'newsletter' ) )->getLogger();

				$confirmed = ( new Newsletter() )->confirm( $token );

				if( $confirmed ) {
					kirby()->session()->set( 'notice', ['type' => 'success', 'message' => "You're confirmed! Thanks for subscribing to the newsletter."] );
				} else {
					$logger->warning( 'newsletter confirm rejected, invalid or expired token' );
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'This confirmation link is invalid or has already been used.'] );
				}

				return Response::redirect( url( 'newsletter' ) );
			}
		],
		[
			'pattern' => 'newsletter/unsubscribe',
			'method' => 'POST',
			'action' => function () {
				$logger = ( new \Logger\Logger( 'newsletter' ) )->getLogger();
				$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

				if( csrf( get( 'csrf' ) ) !== true ) {
					$logger->warning( 'newsletter unsubscribe rejected, invalid CSRF token', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'Something went wrong, please try again.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				if( !empty( get( 'website' ) ) ) {
					$logger->info( 'newsletter unsubscribe honeypot triggered', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'success', 'message' => "You've been unsubscribed."] );
					return Response::redirect( url( 'newsletter' ) );
				}

				$newsletter = new Newsletter();
				if( $newsletter->isRateLimited( 'unsubscribe-' . $ip ) ) {
					$logger->warning( 'newsletter unsubscribe rate limited', ['ip' => $ip] );
					kirby()->session()->set( 'notice', ['type' => 'error', 'message' => 'Too many requests, please try again in a minute.'] );
					return Response::redirect( url( 'newsletter' ) );
				}

				$email = substr( trim( get( 'email' ) ?? '' ), 0, 254 );
				if( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$newsletter->unsubscribe( $email );
				}

				// Always show the same message, whether or not the email was found, to avoid leaking subscriber status.
				kirby()->session()->set( 'notice', ['type' => 'success', 'message' => "You've been unsubscribed."] );
				return Response::redirect( url( 'newsletter' ) );
			}
		],
		[
			'pattern' => 'newsletter/panel/preview/(:any)',
			'method' => 'GET',
			'action' => function ( $uuid ) {
				if( !kirby()->user() )
					throw new PermissionException( 'You must be logged in to view this page' );

				$edition = newsletterEditionForUuid( $uuid );

				$html = \Kirby\Cms\App::instance()
					->template( 'emails/newsletter' )
					->render( [
						'edition' => $edition,
						'kirby' => kirby(),
						'site' => kirby()->site(),
						'pages' => [],
						'page' => $edition,
					] );

				return new Response( $html, 'text/html' );
			}
		],
		[
			'pattern' => 'newsletter/panel/send/(:any)',
			'method' => 'GET',
			'action' => function ( $uuid ) {
				if( !kirby()->user() )
					throw new PermissionException( 'You must be logged in to view this page' );

				$edition = newsletterEditionForUuid( $uuid );
				$count = count( ( new Newsletter() )->confirmedEmails() );
				return new Response( newsletterSendConfirmView( $edition, $count ), 'text/html' );
			}
		],
		[
			'pattern' => 'newsletter/panel/send/(:any)',
			'method' => 'POST',
			'action' => function ( $uuid ) {
				if( !kirby()->user() )
					throw new PermissionException( 'You must be logged in to do this' );

				if( csrf( get( 'csrf' ) ) !== true )
					throw new PermissionException( 'Invalid CSRF token' );

				$edition = newsletterEditionForUuid( $uuid );
				$result = ( new Newsletter() )->sendEdition( $edition );

				$messages = [
					'sent' => "Sent to {$result['count']} confirmed subscriber(s).",
					'no_subscribers' => 'Nothing sent, there are no confirmed subscribers.',
					'mail_error' => 'Something went wrong sending the newsletter. Check the logs for details.',
				];

				return new Response( newsletterSendResultView( $messages[$result['status']] ), 'text/html' );
			}
		]
	]
] );
