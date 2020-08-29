<?php

namespace Payments;
use \Stripe\Stripe;
use \Logger\Logger;

class StripeConnector
{
	protected $stripe;
	protected $logger;

	function __construct()
	{
		$instance = new Logger('stripe');
		$this->logger = $instance->getLogger();
		$this->stripe = new \Stripe\StripeClient(kirby()->option('stripe_key_prv'));

		return $this->stripe;
	}

	public function retrieveProduct(string $productID)
	{		
		try{
			if(!empty($productID))
				return $this->stripe->products->retrieve($productID);

			return null;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error retrieving product', [$se->getMessage()]);
			throw new \Exception('Stripe error retrieving product');
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception('Stripe general error');
		}
	}

	public function retrieveSubscription(string $subscriptionID)
	{		
		try{
			if(!empty($subscriptionID))
				return $this->stripe->subscriptions->retrieve($subscriptionID);

			return null;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error retrieving subscription', [$se->getMessage()]);
			throw new \Exception('Stripe error retrieving subscription');
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception('Stripe general error');
		}
	}

	public function retrieveCustomer(string $customerID)
	{		
		try{
			if(!empty($customerID))
				return $this->stripe->customers->retrieve($customerID);

			return null;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error retrieving customer', [$se->getMessage()]);
			throw new \Exception('Stripe error retrieving customer');
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception('Stripe general error');
		}
	}

	public function findCustomer(string $email)
	{		
		if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
			return $this->stripe->customers->all(['email' => $email]);

		return null;
	}

	public function redirectToPortal(string $customerID)
	{
		$billingPortal = $this->stripe->billingPortal->sessions->create([
        	'customer' => $customerID,
        	'return_url' => 'https://the-invisible-cities.com/prints/subscriptions',
      	]);

      	return $billingPortal->url;
	}

	public function createSession(array $lineItems, string $customerEmail = null)
	{
		
		try{
			$sessionObject = [
			  'payment_method_types' => ['card'],
			  'line_items' => [ $lineItems ],
			  'success_url' => kirby()->site()->url() . '/order/success/stripe?sid={CHECKOUT_SESSION_ID}',
			  'cancel_url' => kirby()->site()->url() . '/prints/cart'
			];

			if(!empty($customerEmail))
				$sessionObject['customer_email'] = $customerEmail;

			$session = $this->stripe->checkout->sessions->create($sessionObject);

			return $session;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error creating session', [$se->getMessage()]);
			throw new \Exception('Stripe error creating session');
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception('Stripe general error');
		}
	}

	public function retrieveSession(String $sid)
	{
		try{
			$session = $this->stripe->checkout->sessions->retrieve($sid);

			return $session;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error getting session', [$se->getMessage()]);
			throw new \Exception('Stripe error getting session');
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception('Stripe general error');
		}
	}

	public function retrievePaymentIntent(String $pid)
	{
		try{
			$pi = $this->stripe->paymentIntents->retrieve($pid);

			return $pi;
		}catch(\Stripe\Exception\InvalidRequestException $se){
			$this->logger->error('Stripe error getting payment intent', [$se->getMessage()]);
			throw new \Exception("Stripe error getting payment intent");
		}catch(\Exception $e){
			$this->logger->error('Stripe general error', [$e->getMessage()]);
			throw new \Exception("Stripe general error");
		}
	}
}