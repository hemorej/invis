<?php

namespace Cart;

class Cart
{
	protected $cache;
	protected $site;
	protected $session;
	protected $cartPage;

	function __construct()
	{
		$this->cache = kirby()->cache('backend');
		$this->site = kirby()->site();
		$this->session = kirby()->session();
		$this->cartPage = $this->site->page('prints/cart/' . $this->session->get('txn'));
	}

	public function estimateCurrency($total)
	{
	  if($data = $this->cache->get('rates')){
	    $rates = json_decode($data);
	  } else {
	    $access_key = kirby()->option('fixer_key');
	    $data = \Remote::get('http://data.fixer.io/api/latest?access_key=' . $access_key . '&symbols=USD,CAD,GBP');
	    $this->cache->set('rates', $data, 1440);
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

	public static function inStock($variant)
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

	public function contents($items){
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

	public function subtotal($items) {
	  $subtotal = 0;
	  foreach ($items as $item) {
	    $itemAmount = $item->amount()->value;
	    $subtotal += $itemAmount * (float) $item->quantity()->value;
	  }
	  return $subtotal;
	}

	public function items()
	{
	  $return = new \Collection();

	  if(empty($this->cartPage))
	    return $return;

	  $items = $this->cartPage->products()->toStructure();

	  // Return the empty collection if there are no items
	  if (empty($items)) return $return;

	  foreach ($items as $key => $item)
	      $return->append($key, $item);

	  return $return;
	}

	public function add($id, $quantity)
	{
	  if(!empty($quantity) && $quantity <= 0)
	    return;

	  $quantityToAdd = $quantity ? intval($quantity) : 1;
	  $idParts = explode('::', $id); // $id is formatted uri::sku
	  $uri = $idParts[0];
	  $sku = $idParts[1];
	  $item = empty($this->cartPage) ? null : $this->cartPage->products()->toStructure()->findBy('sku', $sku);
	  $items = empty($this->cartPage) ? array() : $this->cartPage->products()->yaml();
	  $product = $this->site->page($uri);
	  $variant = $this->site->page($uri)->variants()->toStructure()->findBy('sku', $sku);

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
	      'quantity' => $this->updateQty($id, $quantityToAdd),
	    ];
	  } else {
	    // Increase the quantity of an existing item
	    foreach ($items as $key => $i) {
	      if ($i['id'] == $item->id()) {
	        $newQty = $quantity ? (int)$quantity : (int)$item->quantity()->value + 1;
	        $items[$key]['quantity'] = $this->updateQty($id, $newQty);
	        continue;
	      }
	    }
	  }

	  // Create the transaction file if we don't have one yet
	  if (!$this->session->get('txn')) {
	    $txn_id = $this->session->startTime() . $this->session->expiryTime();
	    $timestamp = time();
	    
	    $page = new Page([
	        'dirname' => "3_$this->cartPage",
	        'slug' => $txn_id,
	        'template' => 'order',
	        'content' => [
	          'txn-id' => $txn_id,
	          'txn-date'  => date('m/d/Y H:i:s', time()),
	          'status' => 'pending',
	          'session-start' => time(),
	          'session-end' => time(),
	          'products' => \Yaml::encode($items)
	        ]
	      ]);
	    $page->save();
	    $session->set('txn', $txn_id);

	  }else{
	    $this->site->page($this->cartPage)->update(['products' => \Yaml::encode($items)]);
	  }

	}

	public function updateQty($id, $newQty)
	{
	  // $id is formatted uri::variantslug::optionslug
	  $idParts = explode('::',$id);
	  $uri = $idParts[0];
	  $variantSlug = $idParts[1];

	  // Get combined quantity of this option's siblings
	  $siblingsQty = 0;
	  if(!empty($this->cartPage)){
	    foreach($this->cartPage->products()->toStructure() as $item) {
	      if (strpos($item->id(), $uri.'::'.$variantSlug) === 0) {
	        $siblingsQty += $item->quantity()->value;
	      }
	    }
	  }

	  foreach ($this->site->page($uri)->variants()->toStructure() as $variant) {
	      // Store the stock in a variable for quicker processing
	      if(!$this->inStock($variant))
	        continue;

	      $stock = self::inStock($variant);

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

	public function delete($id)
	{
	  $items = $this->site->page($this->cartPage)->products()->yaml();
	  foreach ($items as $key => $i) {
	    if ($i['id'] == $id) {
	      unset($items[$key]);
	    }
	  }
	  $this->site->page($this->cartPage)->update(['products' => \Yaml::encode($items)]);
	}
}