<?php

use \Cart\Cart;
use \Payments\StripeConnector as Stripe;

return function($site, $page, $kirby)
{
  $session = $kirby->session(['long' => true]);
  $cart = new Cart();

  if (!$session->get('txn') && get('action') != 'add')
    return true;

  $token = get('csrf');
  if(csrf($token) === true)
  {
    if ($action = get('action')) {
      $id = get('id', implode('::', array(get('uri', ''), get('variant', ''))));
      $quantity = intval(get('quantity'));
      $variant = get('variant');
      if ($action == 'add') $cart->add($id, $quantity);
      if ($action == 'delete') $cart->delete($id);
    }
  }
  // Set txn object
  $txn = $cart->getCartPage();
  $total = $cart->subtotal($cart->items());
  $currencies = $cart->estimateCurrency($total);

  $lineItems = $cart->getLineItems();
  $stripeSession = null;
  if(!empty($lineItems))
    $stripeSession = (new Stripe())->createSession($lineItems)->id;


  return [
      'items' => $cart->items()->count(),
      'total' => $total,
      'currencies' => $currencies,
      'content' => $cart->contents($cart->items()),
      'cartItems' => $cart->items(),
      'txn' => $txn,
      'checkoutSessionId' => $stripeSession,
      'currentLocation' => location()->country_name
  ];
};