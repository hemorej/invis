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
		$this->stripe = \Stripe\Stripe::setApiKey(kirby()->option('stripe_key_prv'));

		return $this->stripe;
	}

	public function createSession(array $lineItems)
	{
		$session = \Stripe\Checkout\Session::create([
		  'payment_method_types' => ['card'],
		  'line_items' => [ $lineItems ],
		  'success_url' => 'https://invis.app/order/success/stripe?sid={CHECKOUT_SESSION_ID}',
		  'cancel_url' => 'https://invis.app/prints/cart',
		]);

		return $session;
	}

	public function retrieveSession(String $sid)
	{
		try{
			$session = \Stripe\Checkout\Session::retrieve($sid);

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
			$pi = \Stripe\PaymentIntent::retrieve($pid);

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