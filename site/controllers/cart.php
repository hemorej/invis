<?php
ini_set('session.gc_maxlifetime', 10800);
s::start();

return function($site, $pages, $page) {

  if (!s::get('txn') && get('action') != 'add') {
    // Show the empty cart page if no transaction file has been created yet
    return true;

  } else {
    // Handle cart updates
    if ($action = get('action')) {
      $id = get('id', implode('::', array(get('uri', ''), get('variant', ''), get('option', ''))));
      $quantity = intval(get('quantity'));
      $variant = get('variant');
      $option = get('option');
      if ($action == 'add') add($id, $quantity);
      if ($action == 'remove') remove($id);
      if ($action == 'delete') delete($id);
    }

    // Set txn object
    $txn = page(s::get('txn'));

    $total = cartSubtotal(getItems());

    return [
        'items' => getItems()->count(),
        'total' => $total,
        'content' => cartContents(getItems()),
        'txn' => $txn
    ];
  }
};

function cartContents($items){
  $types = array();
  $content = "";

  foreach($items as $item){
    if(array_key_exists($item->type()->value(), $types)){
      $types[$item->type()->value()] = ++$types[$item->type()->value()];
    }else{
      $types[$item->type()->value()] = 1*$item->quantity()->value;
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

  // Create the transaction file if we don't have one yet
  if (!s::get('txn')) {
    $txn_id = s::id();
    $timestamp = time();
    page('prints')->create('prints/orders/'.$txn_id, 'order', [
      'txn-id' => $txn_id,
      'txn-date'  => $timestamp,
      'status' => 'abandoned',
      'session-start' => $timestamp,
      'session-end' => $timestamp,
    ]);

    s::set('txn', 'prints/orders/'.$txn_id);
  }

  $quantityToAdd = $quantity ? (int) $quantity : 1;
  $item = page(s::get('txn'))->products()->toStructure()->findBy('id', $id);
  $items = page(s::get('txn'))->products()->yaml();
  $idParts = explode('::',$id); // $id is formatted uri::variantslug::optionslug
  $uri = $idParts[0];
  $variantSlug = $idParts[1];
  $product = page($uri);
  $variant = null;
  foreach (page($uri)->variants()->toStructure() as $v) {
    $variant = $v;
  }

  if (empty($item)) {
    // Add a new item
    $items[] = [
      'id' => $id,
      'uri' => $uri,
      'variant' => $variantSlug,
      'name' => $product->title(),
      'amount' => $variant->price(),
      'type' => $product->type()->value(),
      'quantity' => updateQty($id, $quantityToAdd),
    ];
  } else {
    // Increase the quantity of an existing item
    foreach ($items as $key => $i) {
      if ($i['id'] == $item->id()) {
        $items[$key]['quantity'] = updateQty($id, (int) $item->quantity()->value + $quantityToAdd);
        continue;
      }
    }
  }
  page(s::get('txn'))->update(['products' => yaml::encode($items)]);
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
        if ($stock === true or $stock >= $siblingsQty + $newQty) {
          // If the siblings plus $newQty won't exceed the max stock, go ahead
          return $newQty;
        } else if ($stock === false or $stock <= $siblingsQty) {
          // If the siblings have already maxed out the stock, return 0 
          return 0;
        } else if ($stock > $siblingsQty and $stock <= $siblingsQty + $newQty) {
          // If the siblings don't exceed max stock, but the newQty will, reduce newQty to the appropriate level
          return $stock - $siblingsQty;
        }
      }
    }

  // The script should never get to this point
  return 0;
}

function inStock($variant) {

  if (!is_numeric($variant->stock()->value) and $variant->stock()->value === '') return true;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) <= 0) return false;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) > 0) return intval($variant->stock()->value);

  return false;
}
