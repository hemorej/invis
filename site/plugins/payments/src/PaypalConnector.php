<?php

namespace Payments;
use \Logger\Logger;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PaypalConnector
{
    protected $paypal;
    protected $logger;

    function __construct()
    {
        $instance = new Logger('paypal');
        $this->logger = $instance->getLogger();
        $this->paypal = new PayPalHttpClient(self::environment());

        return $this->paypal;
    }

    public static function environment()
    {
        $clientId = kirby()->option('paypal_client_id');
        $clientSecret = kirby()->option('paypal_client_secret');
        return new SandboxEnvironment($clientId, $clientSecret);
    }

    public function getOrder(string $orderId)
    {
        $response = $this->paypal->execute(new OrdersGetRequest($orderId));

        return $response;
    }
}