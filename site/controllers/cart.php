<?php

use \Cart\Cart;

/**
 * Controller for the cart template.
 *
 * Applies an optional add/delete action from the request (guarded by a CSRF
 * token), then returns the current cart state — item count, subtotal, a
 * multi-currency estimate, a rendered contents summary and the draft order
 * page — for the template to render.
 *
 * @param \Kirby\Cms\Site  $site
 * @param \Kirby\Cms\Page  $page
 * @param \Kirby\Cms\App   $kirby
 * @return array|true  True short-circuits rendering when there is nothing to do
 *                     (no active txn and the request is not an "add").
 */
return function($site, $page, $kirby)
{
    // Long-lived session so an in-progress cart survives the checkout round-trip to Stripe.
    $session = $kirby->session(['long' => true]);
    $cart = new Cart();

    // Nothing to show and nothing to create: let Kirby render the page as-is.
    if (!$session->get('txn') && get('action') != 'add')
        return true;

    $token = get('csrf');
    if(csrf($token) === true)
    {
        $action = get('action');
        // Item id is "uri::variant-uuid"; fall back to building it from separate fields.
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
