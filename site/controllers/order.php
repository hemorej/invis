<?php

return function($site, $pages, $page) {
	$token = json_decode(get('token'), true);
	\Stripe\Stripe::setApiKey(\c::get('stripe_key_prv'));

	// TODO: if csrf and if token
	if(empty($token)){
		s::destroy();
		if(s::get('state')){ // an order just went through
			s::remove('state');
			return [ 'state' => 'thanks for ordering !' ];
		}else{ // direct page load
			return [ 'state' => 'no session' ];
		}
	}else{
		// TODO: check if customer exists
		$customer = \Stripe\Customer::create(array(
		      'email' => $token['email'],
		      'source'  => $token['id'],
		      'shipping' => array(
		      		'name' => $token['card']['name'],
		      		'address' => array(
		      			"line1" => $token['card']['address_line1'],
		      			"city" => $token['card']['address_city'],
		      			"country" => $token['card']['address_country'],
		      			"postal_code" => $token['card']['address_zip'],
		      			"state" => $token['card']['address_state']
		      		)
		      )
		  ));

		$order = \Stripe\Order::create(array(
		  "items" => array(
		    array(
		      "type" => "sku",
		      "parent" => ""
		    )
		  ),
		  "currency" => "cad",
		  "customer" => $customer->id
		));

		$order->pay(array(
		  "customer" => $customer->id
		));

		// TODO: pass sku array
		// TODO: update local inventory page(prints)->products()->toStructure()->findBy('sku', $sku);
		// TODO: update order obj to paid, add order id
		// TODO: change uid of page to order_id

		s::remove('txn');
		s::set('state', 'success');

		return [ 'state' => 'success' ];
	}
};