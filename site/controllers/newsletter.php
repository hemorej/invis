<?php

/**
 * Controller for the newsletter template.
 *
 * Pulls the one-shot flash notice set by the newsletter/subscribe,
 * /confirm and /unsubscribe routes (see site/plugins/newsletter/index.php),
 * clears it from the session, and hands it to the template so the outcome
 * of the last action is shown exactly once.
 *
 * @param \Kirby\Cms\Site $site
 * @param \Kirby\Cms\Page $page
 * @param \Kirby\Cms\App  $kirby
 * @return array{notice: array{type: string, message: string}|null}
 */
return function ( $site, $page, $kirby ) {
	$session = $kirby->session();
	$notice = null;

	if( $session->get( 'notice' ) ) {
		$notice = $session->get( 'notice' );
		$session->remove( 'notice' ); // flash: consume on first read
	}

	return ['notice' => $notice];
};
