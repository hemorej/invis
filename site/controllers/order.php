<?php
use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;

return function($site, $pages, $page) {
	$token = json_decode(get('token'), true);
	$items = json_decode(get('items'), true);
	$total = intval(get('total'));
	$logger = new Logger('order');
    $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));

	\Stripe\Stripe::setApiKey(\c::get('stripe_key_prv'));

	// TODO: check if any of the get vars is empty (token, items, total, csrf)
	if(empty($token)){
		if(s::get('state')){ // an order just went through
			s::remove('state');
			return [ 'state' => 'complete' ];
		}else{ // direct page load
			s::destroy();
			return [ 'state' => 'no session' ];
		}
	}else{
		$logger->info(s::id() . ":order processing start");

		$orderId = getUniqueId('order');
		$logger->info(s::id() . ":order created with id " . $orderId);

		// TODO: try/catch with proper error handling
		$charge = \Stripe\Charge::create(array(
			"amount" => $total,
			"currency" => "cad",
			"source" => $token['id'],
			"description" => "Order ". $orderId ." for ". $token['email'],
			"shipping" => array(
				"name" => $token['card']['name'],
				"address" => array(
					"line1" => $token['card']['address_line1'],
					"city" => $token['card']['address_city'],
					"country" => $token['card']['address_country'],
					"postal_code" => $token['card']['address_zip'],
					"state" => $token['card']['address_state']
				)
			),
			"receipt_email" => $token['email']
		));
		$logger->info(s::id() . ":charge captured with id " . $charge->id);

		$email = email(array(
		  'to'      => $token['email'],
		  'from'    => 'info@the-invisible-cities.com',
		  'subject' => 'Your order from The Invisible Cities has been received',
		  'body'    => snippet('order-confirm', array('name' => $token['card']['name'], 'order' => $orderId), true)
		));
		$email->send();
		$logger->info(s::id() . ":email confirmation sent for order id " . $orderId);

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
		$logger->info("inventory updated after order ". $orderId);

		$customer = array('name' => $token['card']['name'],
						'email' => $token['email'],
						'address' => array(
							"street" => $token['card']['address_line1'],
							"city" => $token['card']['address_city'],
							"country" => $token['card']['address_country'],
							"postal_code" => $token['card']['address_zip'],
							"state" => $token['card']['address_state']),
					);
		$logger->info("customer information added to order ". $orderId);

		page(s::get('txn'))->update(['status' => 'paid', 'order_id' => $orderId, 'customer' => yaml::encode($customer)]);
		page(s::get('txn'))->move($orderId);
		s::remove('txn');
		s::set('state', 'success');

		$logger->info(s::id() . ":order processing done");

		return [ 'state' => 'success' ];
	}
};