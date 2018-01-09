<?php

return function($site, $pages, $page) {
	$token = json_decode(get('token'), true);
	$items = json_decode(get('items'), true);
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

		$orderItems = array();
		foreach($items as $item){
			$orderItems[] = array(
		      "type" => "sku",
		      "parent" => $item['sku'],
		      "description" => $item['variant'],
		      "quantity" => $item['quantity']
		    );
		}

		$order = \Stripe\Order::create(array(
			"items" => $orderItems,
			"currency" => "cad",
			"shipping" => array(
					'name' => $token['card']['name'],
					'address' => array(
						"line1" => $token['card']['address_line1'],
						"city" => $token['card']['address_city'],
						"country" => $token['card']['address_country'],
						"postal_code" => $token['card']['address_zip'],
						"state" => $token['card']['address_state']
					)
			),
			"email" => $token['email']
		));

		$order->pay(array("source" => $token['id']));

		$email = email(array(
		  'to'      => $token['email'],
		  'from'    => 'info@the-invisible-cities.com',
		  'subject' => 'Your order from The Invisible Cities has been received',
		  'body'    => snippet('order-confirm', array('name' => $token['card']['name'], 'order' => $order), true)
		));
		$email->send();

		foreach($items as $item){
			$idParts = explode('::', $item['variant']);
  			$uri = $idParts[0];

			$variant = page($uri)->variants()->toStructure()->findBy('sku', $item['sku']);

	        $updatedVariant = array();
	        $updatedVariant['sku'] = $item['sku'];
	        $updatedVariant['name'] = $variant->name->value();
	        $updatedVariant['price'] = $variant->price->value();
	        $updatedVariant['stock'] = $variant->stock->value() - $item['quantity'];

	        addToStructure(page($uri), 'variants', $updatedVariant);
		}

		page(s::get('txn'))->update(['status' => 'paid', 'order_id' => $order->id]);
		page(s::get('txn'))->move($order->id);
		s::remove('txn');
		s::set('state', 'success');

		return [ 'state' => 'success' ];
	}
};