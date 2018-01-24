<?php
use \Gilbitron\Util\SimpleCache;
s::start();

return function($site, $pages, $page) {

  if (!s::get('txn') && get('action') != 'add') {
    return true;

  } else {
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
    $txn = page(s::get('txn'));
    $total = cartSubtotal(getItems());
    $currencies = estimateCurrency($total);

    return [
        'items' => getItems()->count(),
        'total' => $total,
        'currencies' => $currencies,
        'content' => cartContents(getItems()),
        'txn' => $txn
    ];
  }
};

function estimateCurrency($total)
{
  $cache = new SimpleCache();
  $cache->cache_path = __DIR__ . '/../cache/';
  $cache->cache_time = 86400; //24h

  if($data = $cache->get_cache('rates')){
    $rates = json_decode($data);
  } else {
    $data = $cache->do_curl('https://api.fixer.io/latest?base=CAD&symbols=USD,EUR,GBP');
    $cache->set_cache('rates', $data);
    $rates = json_decode($data);
  }

  $estimate = round($total * $rates->rates->USD, 0) . '$/'
            . round($total * $rates->rates->EUR, 0) . '€/'
            . round($total * $rates->rates->GBP, 0) . '£';

  return $estimate;
}

function cartContents($items){
  $types = array();
  $content = "";

  foreach($items as $item){
    if(array_key_exists($item->type()->value(), $types)){
      $types[$item->type()->value()] = $types[$item->type()->value()] + $item->quantity->value();
    }else{
      $types[$item->type()->value()] = 1*$item->quantity->value();
    }
  }

  foreach($types as $type => $quantity){
    $line = join(' ', array($quantity, $type));
    if($quantity > 1)
      $line .= 's';
    
    $content = $content . ', ' . $line;
  }

  return ltrim($content, ', ');
}

function cartSubtotal($items) {
  $subtotal = 0;
  foreach ($items as $item) {
    $itemAmount = $item->amount()->value;
    $subtotal += $itemAmount * (float) $item->quantity()->value;
  }
  return $subtotal;
}

function getItems() {

  $return = new Collection();
  
  $items = page(s::get('txn'))->products()->toStructure();

  // Return the empty collection if there are no items
  if (!$items->count()) return $return;

  foreach ($items as $key => $item) {
      $return->append($key, $item);
    }

  return $return;
}

function add($id, $quantity) {

  $quantityToAdd = $quantity ? (int) $quantity : 1;
  $idParts = explode('::',$id); // $id is formatted uri::sku
  $uri = $idParts[0];
  $sku = $idParts[1];
  $item = page(s::get('txn'))->products()->toStructure()->findBy('sku', $sku);
  $items = page(s::get('txn'))->products()->yaml();
  $product = page($uri);
  $variant = page($uri)->variants()->toStructure()->findBy('sku', $sku);

  if (empty($item)) {
    // Add a new item
    $items[] = [
      'id' => $id,
      'uri' => $uri,
      'variant' => $variant->name(),
      'name' => $product->title(),
      'amount' => $variant->price(),
      'type' => $product->type()->value(),
      'sku' => $sku,
      'quantity' => updateQty($id, $quantityToAdd),
    ];
  } else {
    // Increase the quantity of an existing item
    foreach ($items as $key => $i) {
      if ($i['id'] == $item->id()) {
        $newQty = $quantity ? (int)$quantity : (int)$item->quantity()->value + 1;
        $items[$key]['quantity'] = updateQty($id, $newQty);
        continue;
      }
    }
  }

  // Create the transaction file if we don't have one yet
  if (!s::get('txn')) {
    $txn_id = s::id();
    
    page('prints')->create('prints/orders/'.$txn_id, 'order', [
      'txn-id' => $txn_id,
      'txn-date'  => date('m/d/Y H:i:s', time()),
      'status' => 'pending',
      'session-start' => $timestamp,
      'session-end' => $timestamp,
      'products' => yaml::encode($items)
    ]);

    s::set('txn', 'prints/orders/'.$txn_id);
  }else{
    page(s::get('txn'))->update(['products' => yaml::encode($items)]);
  }

}

function updateQty($id, $newQty) {
  // $id is formatted uri::variantslug::optionslug
  $idParts = explode('::',$id);
  $uri = $idParts[0];
  $variantSlug = $idParts[1];

  // Get combined quantity of this option's siblings
  $siblingsQty = 0;
  foreach (page(s::get('txn'))->products()->toStructure() as $item) {
    if (strpos($item->id(), $uri.'::'.$variantSlug) === 0) {
      $siblingsQty += $item->quantity()->value;
    }
  }

  foreach (page($uri)->variants()->toStructure() as $variant) {
      // Store the stock in a variable for quicker processing
      $stock = inStock($variant);

      if ($siblingsQty === 0) {
        // If there are no siblings
        if ($stock === true or $stock >= $newQty){
          // If there is enough stock
          return $newQty;
        } else if ($stock === false) {
          // If there is no stock
          return 0;
        } else {
          // If there is insufficient stock
          return $stock;
        }
      } else {
        // If there are siblings
        if ($stock === true or $stock >= $newQty) {
          // If the siblings plus $newQty won't exceed the max stock, go ahead
          return $newQty;
        } else if ($stock === false or $stock <= $siblingsQty) {
          // If the siblings have already maxed out the stock, return 0 
          return $siblingsQty;
        } else if ($stock > $siblingsQty and $stock <= $siblingsQty + $newQty) {
          // If the siblings don't exceed max stock, but the newQty will, reduce newQty to the appropriate level
          return $siblingsQty;
        }
      }
    }

  // The script should never get to this point
  return 0;
}

function delete($id) {
  $items = page(s::get('txn'))->products()->yaml();
  foreach ($items as $key => $i) {
    if ($i['id'] == $id) {
      unset($items[$key]);
    }
  }
  page(s::get('txn'))->update(['products' => yaml::encode($items)]);
}