<?php

return function($site, $page, $kirby) {
	$session = kirby()->session();

	if($session->get('state') == 'success'){ // an order just went through
		$order = $site->page('orders')->draft($session->get('order'));
		return [ 'state' => 'complete', 'order' => $order ];
	}elseif($session->get('error')){
		$message = $session->get('error');
		$session->remove('error');
		return [ 'state' => 'error', 'message' => $message];
	}else{ // direct page load
		return [ 'state' => 'no session'];
	}
};