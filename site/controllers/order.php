<?php

return function($site, $page, $kirby) {
	$token = json_decode(get('token'), true);
	$args = json_decode(get('args'), true);
	$items = json_decode(get('items'), true);
	$csrf = get('csrf');
	$total = intval(get('total'));
	$session = $kirby->session();
	// // $logger = new Logger('order');
 //    // $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));


	if(empty($token) || empty($total) || empty($items) || empty($args) || csrf($csrf) !== true){
		if($session->get('state')){ // an order just went through
			$order = page($session->get('order'));
			$session->destroy();
			return [ 'state' => 'complete', 'order' => $order ];
		}elseif($session->get('error')){
			$message = $session->get('error');
			$session->remove('error');
			return [ 'state' => 'error', 'message' => $message];
		}else{ // direct page load
			return [ 'state' => 'no session'];
		}
	}else{
		// $logger->info($session->token() . ":order processing start");

		$orderId = getUniqueId('order');
		// // $logger->info($s->token() . ":order created with id " . $orderId);

		try{
			if(preg_match('/^PAY-/', $token['id']) == 1){

				$apiContext = new \PayPal\Rest\ApiContext(
					new \PayPal\Auth\OAuthTokenCredential(
						$kirby->option('paypal_client_id'),
						$kirby->option('paypal_client_secret')
					)
				);

				$payment = PayPal\Api\Payment::get($token['id'], $apiContext);

				if($payment->getState() != 'approved' || (time() - strtotime($payment->getCreateTime()) > 300))
					throw new Exception("Paypal transaction not approved");

				// $logger->info($s->token() . ":paypal captured with id " . $payment->getId());

			}else{
				\Stripe\Stripe::setApiKey($kirby->option('stripe_key_prv'));
				$charge = \Stripe\Charge::create(array(
					"amount" => $total,
					"currency" => "cad",
					"source" => $token['id'],
					"description" => "Order ". $orderId ." for ". $token['email'],
					"shipping" => array(
						"name" => $args['shipping_name'],
						"address" => array(
							"line1" => $args['shipping_address_line1'],
							"city" => $args['shipping_address_city'],
							"country" => $args['shipping_address_country'],
							"postal_code" => $args['shipping_address_zip'],
							"state" => $args['shipping_address_state']
						)
					),
					"receipt_email" => $token['email']
				));

				// $logger->info($s->token() . ":charge captured with id " . $charge->id);
			}

			// $logger->info($s->token() . ":order billing info: " . $args['billing_name'] . ", " . $args['billing_address_line1'] . ", " . $args['billing_address_zip']);
			// $logger->info($s->token() . ":order shipping info: " . $args['shipping_name'] . ", " . $args['shipping_address_line1'] . ", " . $args['shipping_address_zip']);

			foreach($items as $item){
				$idParts = explode('::', $item['id']);
	  			$uri = $idParts[0];

				$variant = page($uri)->variants()->toStructure()->findBy('sku', (string)$item['sku']);

		        $updatedVariant = array();
		        $updatedVariant['sku'] = $variant->sku->value();
		        $updatedVariant['name'] = $variant->name->value();
		        $updatedVariant['price'] = $variant->price->value();

		        $remainingStock = intval($variant->stock->value()) - intval($item['quantity']);
		        if($remainingStock < 0)
		        	throw new Exception("Insufficient stock for product " . page($uri)->title()->value() . " (sku: " . $variant->sku->value() . ")");

		        $updatedVariant['stock'] = $remainingStock;

		        addToStructure(page($uri), 'variants', $updatedVariant);
			}
			// $logger->info("inventory updated after order ". $orderId);

			$customer = array('name' => $args['shipping_name'],
							'email' => $token['email'],
							'address' => array(
								"street" => $args['shipping_address_line1'],
								"city" => $args['shipping_address_city'],
								"country" => $args['shipping_address_country'],
								"postal_code" => $args['shipping_address_zip'],
								"state" => $args['shipping_address_state'])
						);
			// $logger->info("customer information added to order ". $orderId);


			page($session->get('txn'))->update(['status' => 'paid', 'order_id' => $orderId, 'customer' => yaml::encode($customer)]);
			page($session->get('txn'))->move($orderId);
			$session->remove('txn');
			$session->set('state', 'success');
			$session->set('order', 'prints/orders/' . str_replace('_', '-', $orderId));
			$order = array(	'order' => $orderId,
	  						'items' => $items,
							'fullName' => $args['shipping_name'],
							'street' => $args['shipping_address_line1'],
							'city' => $args['shipping_address_city'],
							'country' => $args['shipping_address_country'],
							'postcode' => $args['shipping_address_zip'],
							'province' => $args['shipping_address_state'],
							'email' => $token['email'],
							'total' => $total);

			$userNotification = email(array(
			  'to'      => $token['email'],
			  'from'    => 'The Invisible Cities <jerome@the-invisible-cities.com>',
			  'subject' => 'Your order from The Invisible Cities has been received',
			  'service' => 'mailgun',
			  'options' => array(
			    'key'    => $kirby->option('mailgun_key'),
			    'domain' => $kirby->option('mailgun_domain')
			  ),
			  'body'    => snippet('order-confirm', 
			  					a::merge($order,
			  						array(
	               		             'title' => 'Your order from The Invisible Cities has been received',
	               		             'subtitle' => 'Order confirmation',
	                                 'preview' => 'Order confirmation. We received your order and will prepare it for shipping soon. Below is your order information.',
	                                 'headline' => 'Thanks for ordering! We received your order and will prepare it for shipping soon. Below is your order information.'
			  						)), true)
			));

			$selfNotification = email(array(
			  'to'      => $kirby->option('alert_address'),
			  'from'    => 'The Invisible Cities <jerome@the-invisible-cities.com>',
			  'subject' => 'New order at The Invisible Cities!',
			  'service' => 'mailgun',
			  'options' => array(
			    'key'    => $kirby->option('mailgun_key'),
			    'domain' => $kirby->option('mailgun_domain')
			  ),
			  'body'    => snippet('order-confirm', 
			  					a::merge($order,
			  						array(
	               		             'title' => 'A new order at the Invisible Cities has been received',
	               		             'subtitle' => 'Order summary',
	                                 'preview' => 'Order summary',
	                                 'headline' => 'Below is the order information.'
			  						)), true)
			));

			try{
				$userNotification->send();
			  	// $logger->info($s->token() . ":email confirmation sent for order id " . $orderId);

			  	$selfNotification->send();
			  	// $logger->info($s->token() . ":admin notification sent for order id " . $orderId);
			}catch(Error $err){
				$description = "email confirmation error for order id " . $orderId . ": " . $err->getMessage();
				// $logger->error($s->token() . ":" . $description);
				sendAlert($s->token(), $orderId, $description);
			}

			// $logger->info($s->token() . ":order processing done");

		}catch(\Stripe\Error\Card $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			// $logger->error($s->token() . ": charge declined, type: " . $err['type'] . ", code: " . $err['code'] . ", status: " . $e->getHttpStatus());
			sendAlert($s->token(), $orderId, "charge declined " . $e->getHttpStatus());
			$session->set('error', 'Unfortunately your card was declined, contact your financial institution.');
		} catch (\Stripe\Error\RateLimit $e) {
		  	// $logger->error($s->token() . ": stripe rate limit error");
		  	sendAlert($s->token(), $orderId, "stripe rate limit error");
		  	$session->set('error', 'There was an error with the payment processing, you may try again later.');
		} catch (\Stripe\Error\InvalidRequest $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			// $logger->error($s->token() . ": invalid request, status: " . $e->getHttpStatus());
			$session->set('error', 'There was an error with the payment processing, you may try again later.');
		} catch (\Stripe\Error\Authentication $e) {
		  	// $logger->error($s->token() . ": stripe auth error, check keys", array('reason' => $e->getMessage()));
		  	$session->set('error', 'There was an error with the payment processing, I have been notified of the issue.');
		} catch (\Stripe\Error\ApiConnection $e) {
			// $logger->error($s->token() . ": network communication error", array('reason' => $e->getMessage()));
			$session->set('error', 'There was an error with the payment processing, I have been notified of the issue.');
		} catch (\Stripe\Error\Base $e) {
			// $logger->error($s->token() . ": stripe general error", array('reason' => $e->getMessage()));
			sendAlert($s->token(), $orderId, $e->getMessage());
			$session->set('error', 'There was an unspecified error with the payment processing, I have been notified of this issue. You may try again later.');
		} catch (Exception $e) {
			// $logger->error($s->token() . ": general error", array('reason' => $e->getMessage()));
			sendAlert($s->token(), $orderId, $e->getMessage());
			$session->set('error', 'There was an unspecified error with the site, I have been notified of this issue. You may try again later');
		}

		return [ 'state' => 'success'];
	}
};

function sendAlert($sid, $orderId, $error = "Unknown reason")
{
	$email = email(array(
	  'to'      => $kirby->option('alert_address'),
	  'from'    => 'The Invisible Cities Store <jerome@the-invisible-cities.com>',
	  'subject' => 'Order exception alert',
	  'service' => 'mailgun',
	  'options' => array(
	    'key'    => $kirby->option('mailgun_key'),
	    'domain' => $kirby->option('mailgun_domain')
	  ),
	  'body'    => "A problem occurred while processing order " . $orderId . " during session " . $sid . "<br />" .
	  	"Error: " . $error
	));

	$email->send();

	// // $logger = new Logger('order');
 //    // $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));
	// // $logger->info("Alert sent for " . $orderId);
}