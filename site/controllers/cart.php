<?php


return function($site, $page, $kirby)
{
  // $kirby->session()->destroy();exit;
  $session = $kirby->session(['long' => true]);

  if (!$session->get('txn') && get('action') != 'add')
    return true;

  $token = get('csrf');
  if(csrf($token) === true)
  {
    if ($action = get('action')) {
      $id = get('id', implode('::', array(get('uri', ''), get('variant', ''))));
      $quantity = intval(get('quantity'));
      $variant = get('variant');
      if ($action == 'add') add($id, $quantity);
      if ($action == 'delete') delete($id);
    }
  }
  // Set txn object
  $txn = $site->page("prints/cart/" . $session->get('txn'));
  $total = cartSubtotal(getItems());
  $currencies = estimateCurrency($total);

  return [
      'items' => getItems()->count(),
      'total' => $total,
      'currencies' => $currencies,
      'content' => cartContents(getItems()),
      'txn' => $txn
  ];
};