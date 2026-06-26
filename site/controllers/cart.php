<?php

use \Cart\Cart;

return function($site, $page, $kirby)
{
    $session = $kirby->session(['long' => true]);
    $cart = new Cart();

    if (!$session->get('txn') && get('action') != 'add')
        return true;

    $token = get('csrf');
    if(csrf($token) === true)
    {
        $action = get('action');
        $id = get('id', implode('::', array(get('uri', ''), get('variant', ''))));
        $quantity = intval(get('quantity'));
        if ($action == 'add') $cart->add($id, $quantity);
        if ($action == 'delete') $cart->delete($id);
    }

    $txn = $cart->getCartPage();
    $subtotal = $cart->subtotal($cart->items());
    $currencies = $cart->estimateCurrency($subtotal);

    return [
        'items'           => $cart->items()->count(),
        'total'           => $subtotal,
        'currencies'      => $currencies,
        'content'         => $cart->contents($cart->items()),
        'cartItems'       => $cart->items(),
        'txn'             => $txn,
        'checkoutSessionId' => null,
        'currentLocation' => location()->country_name
    ];
};
