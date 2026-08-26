<?php

require_once __DIR__ . '/src/Crypto.php';
require_once __DIR__ . '/src/Newsletter.php';

use Newsletter\Newsletter;
use Kirby\Exception\PermissionException;
use Kirby\Http\Response;

/**
 * Renders the confirmed-subscriber list as a standalone HTML fragment with a
 * copy-to-clipboard button, for pasting into a manual BCC send. Deliberately
 * plain server-rendered HTML (no Panel Vue component) since the project runs
 * with panel.vue.compiler disabled and has no Panel JS build step.
 *
 * @param string[] $emails
 * @return string
 */
function newsletterEmailsView( array $emails )
{
	$list = implode( ', ', $emails );
	$listAttr = htmlspecialchars( $list, ENT_QUOTES );
	$count = count( $emails );

	return <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Subscriber emails</title>
<style>
	body { font-family: -apple-system, Helvetica, Arial, sans-serif; max-width: 720px; margin: 40px auto; padding: 0 20px; color: #222; }
	textarea { width: 100%; height: 220px; box-sizing: border-box; padding: 12px; font-family: monospace; font-size: 13px; }
	button { margin-top: 12px; padding: 10px 18px; cursor: pointer; }
	p.count { color: #666; font-size: 14px; }
</style>
</head>
<body>
	<h1>Confirmed subscribers</h1>
	<p class="count">{$count} email(s)</p>
	<textarea id="emails" readonly>{$listAttr}</textarea>
	<br>
	<button id="copy">Copy to clipboard</button>
	<script>
		document.getElementById('copy').addEventListener('click', function () {
			var textarea = document.getElementById('emails');
			navigator.clipboard.writeText(textarea.value).then(function () {
				var btn = document.getElementById('copy');
				var original = btn.textContent;
				btn.textContent = 'Copied!';
				setTimeout(function () { btn.textContent = original; }, 1500);
			});
		});
	</script>
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
			'pattern' => 'newsletter/panel/emails',
			'method' => 'GET',
			'action' => function () {
				if( !kirby()->user() )
					throw new PermissionException( 'You must be logged in to view this page' );

				$emails = ( new Newsletter() )->confirmedEmails();
				return new Response( newsletterEmailsView( $emails ), 'text/html' );
			}
		]
	]
] );
