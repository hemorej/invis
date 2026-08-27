<?php

/**
 * Controller for the order (post-checkout) template.
 *
 * Reads the session state left by Cart::processStripe() after the Stripe
 * return redirect and maps it to one of three template states:
 *  - 'complete'   — payment succeeded; also exposes the finalized order page
 *  - 'error'      — processing failed; exposes the (one-shot) error message
 *  - 'no session' — the page was opened directly, with no checkout in flight
 *
 * @param \Kirby\Cms\Site $site
 * @param \Kirby\Cms\Page $page
 * @param \Kirby\Cms\App  $kirby
 * @return array{state: string, order?: \Kirby\Cms\Page, message?: string}
 */
return function($site, $page, $kirby) {
	$session = kirby()->session();

	if($session->get('state') == 'success'){ // an order just went through
		$order = $site->page('prints/orders')->draft($session->get('order'));
		return [ 'state' => 'complete', 'order' => $order ];
	}elseif($session->get('error')){
		$message = $session->get('error');
		$session->remove('error'); // flash: show the failure once
		return [ 'state' => 'error', 'message' => $message];
	}else{ // direct page load
		return [ 'state' => 'no session'];
	}
};