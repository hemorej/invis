<?php
use \Monolog\Logger;
use \Monolog\Handler\RotatingFileHandler;

return function($site, $pages, $page) {
	$token = json_decode(get('token'), true);
	$items = json_decode(get('items'), true);
	$csrf = get('csrf');
	$total = intval(get('total'));
	$logger = new Logger('order');
    $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));

	\Stripe\Stripe::setApiKey(\c::get('stripe_key_prv'));

	if(empty($token) || empty($total) || empty($items) || csrf($csrf) !== true){
		if(s::get('state')){ // an order just went through
			$order = page(s::get('order'));
			s::destroy();
			return [ 'state' => 'complete', 'order' => $order ];
		}elseif(s::get('error')){
			s::remove('error');
			return [ 'state' => 'error'];
		}else{ // direct page load
			return [ 'state' => 'no session'];
		}
	}else{
		$logger->info(s::id() . ":order processing start");

		$orderId = getUniqueId('order');
		$logger->info(s::id() . ":order created with id " . $orderId);

		try{
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

			foreach($items as $item){
				$idParts = explode('::', $item['id']);
	  			$uri = $idParts[0];

				$variant = page($uri)->variants()->toStructure()->findBy('sku', (string)$item['sku']);

		        $updatedVariant = array();
		        $updatedVariant['sku'] = $variant->sku->value();
		        $updatedVariant['name'] = $variant->name->value();
		        $updatedVariant['price'] = $variant->price->value();
		        $updatedVariant['stock'] = intval($variant->stock->value()) - intval($item['quantity']);

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
			s::set('order', 'prints/orders/' . str_replace('_', '-', $orderId));

			$email = email(array(
			  'to'      => $token['email'],
			  'from'    => 'The Invisible Cities <info@the-invisible-cities.com>',
			  'subject' => 'Your order from The Invisible Cities has been received',
			  'service' => 'mailgun',
			  'options' => array(
			    'key'    => \c::get('mailgun_key'),
			    'domain' => \c::get('mailgun_domain')
			  ),
			  'body'    => snippet('order-confirm', 
			  					array(
			  						 'order' => $orderId,
			  						 'items' => $items,
									 'fullName' => $token['card']['name'],
									 'street' => $token['card']['address_line1'],
									 'city' => $token['card']['address_city'],
									 'province' => $token['card']['address_state'],
									 'country' => $token['card']['address_country'],
									 'postcode' => $token['card']['address_zip'],
									 'email' => $token['email'],
									 'total' => $total,
	               		             'title' => 'Your order from The Invisible Cities has been received',
	               		             'subtitle' => 'Order confirmation',
	                                 'preview' => 'Order confirmation. We received your order and will prepare it for shipping soon. Below is your order information.',
	                                 'headline' => 'Thanks for ordering! We received your order and will prepare it for shipping soon. Below is your order information.'
			  						), true)
			));

			try{
				$email->send();
			  	$logger->info(s::id() . ":email confirmation sent for order id " . $orderId);
			}catch(Error $err){
				$logger->error(s::id() . ":email confirmation error for order id " . $orderId . ": " . $e->getMessage());
			}

			$logger->info(s::id() . ":order processing done");
			
		}catch(\Stripe\Error\Card $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			$logger->error(s::id() . ": charge declined, type: " . $err['type'] . ", code: " . $err['code'] . ", status: " . $e->getHttpStatus());
			sendAlert(s::id(), $orderId);
			s::set('error', 'error');
		} catch (\Stripe\Error\RateLimit $e) {
		  	$logger->error(s::id() . ": stripe rate limit error");
		  	s::set('error', 'error');
		} catch (\Stripe\Error\InvalidRequest $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			$logger->error(s::id() . ": invalid request, status: " . $e->getHttpStatus());
		  	s::set('error', 'error');
		} catch (\Stripe\Error\Authentication $e) {
		  	$logger->error(s::id() . ": stripe auth error, check keys");
		  	s::set('error', 'error');
		} catch (\Stripe\Error\ApiConnection $e) {
			$logger->error(s::id() . ": network communication error");
		  	s::set('error', 'error');
		} catch (\Stripe\Error\Base $e) {
			$logger->error(s::id() . ": stripe general error");
			sendAlert(s::id(), $orderId);
		  	s::set('error', 'error');
		} catch (Exception $e) {
			$logger->error(s::id() . ": general error");
			sendAlert(s::id(), $orderId);
		  	s::set('error', 'error');
		}

		return [ 'state' => 'success'];
	}
};

function sendAlert($sid, $orderId)
{
	$email = email(array(
	  'to'      => \c::get('alert_address'),
	  'from'    => 'The Invisible Cities Store <info@the-invisible-cities.com>',
	  'subject' => 'Order exception alert',
	  'service' => 'mailgun',
	  'options' => array(
	    'key'    => \c::get('mailgun_key'),
	    'domain' => \c::get('mailgun_domain')
	  ),
	  'body'    => "A problem occurred while processing order " . $orderId . " during session " . $sid
	));

	$email->send();

	$logger = new Logger('order');
    $logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../logs/invis.log', Logger::DEBUG));
	$logger->info("Alert sent for " . $orderId);
}