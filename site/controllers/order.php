<?php

return function($site, $page, $kirby) {
	$session = kirby()->session();

	if($session->get('state')){ // an order just went through
		$order = $site->page($session->get('order'));
		$session->destroy();
		return [ 'state' => 'complete', 'order' => $order ];
	}elseif($session->get('error')){
		$message = $session->get('error');
		$session->remove('error');
		return [ 'state' => 'error', 'message' => $message];
	}else{ // direct page load
		return [ 'state' => 'no session'];
	}
};