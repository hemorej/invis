<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('helpers/helpers', [
  'options' => [
    'cache.backend' => true
  ]
]);

function addToStructure($page, $field, $data = array())
{
  $fieldData = $page->$field()->yaml();
  $key = array_search($data['sku'], array_column($fieldData, 'sku'));
  unset($fieldData[$key]);
  $fieldData = array_values($fieldData);

  $fieldData[] = $data;
  $fieldData = yaml::encode($fieldData);
  try {
    $page->update(array($field => $fieldData));
    return true;
  } catch(Exception $e) {
    return $e->getMessage();
  }
}

function inStock($variant)
{
  if(strstr($variant, '::')){
    $idParts = explode('::',$variant);
    $uri = $idParts[0];
    $sku = $idParts[1];

    $variant = page($uri)->variants()->toStructure()->findBy('sku', $sku);
    return $variant->stock()->value();
  }

  if (!is_numeric($variant->stock()->value) and $variant->stock()->value === '') return true;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) <= 0) return false;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) > 0) return intval($variant->stock()->value);

  return false;
}

function getUniqueId($type = 'sku')
{
  $prefix = 'pre_';

  switch($type){
    case 'sku':
      $prefix = 'sku_';
      break;
    case 'product':
      $prefix = 'prd_';
      break;
    case 'order':
      $prefix = 'ord_';
      break;
  }

  $bytes = openssl_random_pseudo_bytes(14, $cstrong);
  $hex   = bin2hex($bytes);

  return $prefix . $hex;
}

function getPreview($image){

    if($image->isLandscape())
      return $image->resize(600)->url();

    return $image->resize(null, 600)->url();
}

function archiveDate($string){
  $month = date('F', strtotime($string));
  $day = date('j', strtotime($string));
  $year = '\'' . date('y', strtotime($string));

  $textualNumbers = array(
  'first',
  'second',
  'third',
  'fourth',
  'five',
  'six',
  'seven',
  'eight',
  'nine',
  'ten',
  'eleven',
  'twelve',
  'thirteen',
  'fourteen',
  'fifteen',
  'sixteen',
  'seventeen',
  'eighteen',
  'nineteen',
  'twenty',
  'twenty-one',
  'twenty-two',
  'twenty-three',
  'twenty-four',
  'twenty-five',
  'twenty-six',
  'twenty-seven',
  'twenty-eight',
  'twenty-nine',
  'thirty',
  'thirty-one');

  return implode(' ', array($month, $textualNumbers[$day-1], $year));
}

function getHomeImage(){
  $cache = kirby()->cache('backend');

  $images = array();
  if($data = $cache->get('images')){
    $images = json_decode($data);
  }else{
    foreach(page('projects/portfolio')->files() as $image){
      if($image->isLandscape())
        $images[] = $image->filename();
    }
    $cache->set('images', json_encode($images), 43200);
  }

  $file = $images[array_rand($images)];
  $image = page("projects/portfolio/")->file($file);

  return array('images' => $image);
}

function location(){
  $cache = kirby()->cache('backend');

  $remote = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
  if($remote == false)
    return 'CA';

  if($data = $cache->get($remote)){
    $loc = json_decode($data);
  }else{
    $access_key = kirby()->option('ipstack_key');
    $requestURL = "http://api.ipstack.com/$remote?access_key=$access_key&fields=country_code,location.is_eu&language=en&output=json";

    $data = Remote::get($requestURL);
    $cache->set($remote, $data);
    $loc = json_decode($data->content());
  }

  if($loc->location->is_eu == true)
      return $loc->country_code;

    return false;
}

function estimateCurrency($total)
{
  $cache = kirby()->cache('backend');

  if($data = $cache->get('rates')){
    $rates = json_decode($data);
  } else {
    $access_key = kirby()->option('fixer_key');
    $data = Remote::get('http://data.fixer.io/api/latest?access_key=' . $access_key . '&symbols=USD,CAD,GBP');
    $cache->set('rates', $data, 1440);
    $rates = json_decode($data->content());
  }

  $eurBase = $total / $rates->rates->CAD;
  $usd = $eurBase * $rates->rates->USD;
  $gbp = $eurBase * $rates->rates->GBP;

  $estimate = round($usd, 0) . '$/'
            . round($eurBase, 0) . '€/'
            . round($gbp, 0) . '£';

  return $estimate;
}

function cartContents($items){
  $types = array();
  $content = "";

  foreach($items as $item){
    if(array_key_exists($item->type()->value(), $types)){
      $types[$item->type()->value()] = $types[$item->type()->value()] + $item->quantity()->value();
    }else{
      $types[$item->type()->value()] = 1*$item->quantity()->value();
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

  $site = kirby()->site();
  $session = kirby()->session();
  $return = new Collection();
  $cartPage = $site->page('prints/cart/' . $session->get('txn'));

  if(empty($cartPage))
    return $return;

  // $items = Yaml::decode($cartPage->products()));
  $items = $cartPage->products()->toStructure();

  // Return the empty collection if there are no items
  if (empty($items)) return $return;

  foreach ($items as $key => $item)
      $return->append($key, $item);

  return $return;
}

function add($id, $quantity) {

  $site = kirby()->site();
  $session = kirby()->session();

  if(!empty($quantity) && $quantity <= 0)
    return;

  $quantityToAdd = $quantity ? intval($quantity) : 1;
  $idParts = explode('::', $id); // $id is formatted uri::sku
  $uri = $idParts[0];
  $sku = $idParts[1];
  $cartPageName = "prints/cart/" . $session->get('txn');
  $item = empty($site->page($cartPageName)) ? null : $site->page($cartPageName)->products()->toStructure()->findBy('sku', $sku);
  $items = empty($site->page($cartPageName)) ? array() : $site->page($cartPageName)->products()->yaml();
  $product = $site->page($uri);
  $variant = $site->page($uri)->variants()->toStructure()->findBy('sku', $sku);

  if (empty($item)) {
    // Add a new item
    $items[] = [
      'id' => $id,
      'uri' => $uri,
      'variant' => $variant->name()->value(),
      'name' => $product->title()->value(),
      'amount' => $variant->price()->value(),
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
  if (!$session->get('txn')) {
    $txn_id = $session->startTime() . $session->expiryTime();
    $cartPageName = "prints/cart/$txn_id";
    $timestamp = time();
    
    $page = new Page([
        'dirname' => "3_$cartPageName",
        'slug' => $txn_id,
        'template' => 'order',
        'content' => [
          'txn-id' => $txn_id,
          'txn-date'  => date('m/d/Y H:i:s', time()),
          'status' => 'pending',
          'session-start' => time(),
          'session-end' => time(),
          'products' => yaml::encode($items)
        ]
      ]);
    $page->save();
    $session->set('txn', $txn_id);

  }else{
    $cartPageName = "prints/cart/" . $session->get('txn');
    $site->page($cartPageName)->update(['products' => yaml::encode($items)]);
  }

}

function updateQty($id, $newQty) {
  $site = kirby()->site();
  $session = kirby()->session();

  // $id is formatted uri::variantslug::optionslug
  $idParts = explode('::',$id);
  $uri = $idParts[0];
  $variantSlug = $idParts[1];

  // Get combined quantity of this option's siblings
  $siblingsQty = 0;
  $cartPage = $site->page('prints/cart/' . $session->get('txn'));
  if(!empty($cartPage)){
    foreach($cartPage->products()->toStructure() as $item) {
      if (strpos($item->id(), $uri.'::'.$variantSlug) === 0) {
        $siblingsQty += $item->quantity()->value;
      }
    }
  }

  foreach ($site->page($uri)->variants()->toStructure() as $variant) {
      // Store the stock in a variable for quicker processing
      if(!inStock($variant))
        continue;

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
  $site = kirby()->site();
  $session = kirby()->session();
  $cartPageName = "prints/cart/" . $session->get('txn');

  $items = $site->page($cartPageName)->products()->yaml();
  foreach ($items as $key => $i) {
    if ($i['id'] == $id) {
      unset($items[$key]);
    }
  }
  $site->page($cartPageName)->update(['products' => yaml::encode($items)]);
}