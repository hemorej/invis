<?php

$shippedOrders = page('prints/orders')->children()->filter(function($page){
  return $page->modified() > time() - (60 * 60 * 24 * 30) && $page->status() == 'shipped';
});

$unshippedOrders = page('prints/orders')->children()->filter(function($page){
  return $page->modified() > time() - (60 * 60 * 24 * 30) && $page->status() == 'paid';
});

?>

<style>
  .cart-stats { display: flex; justify-content: space-between; }
  .cart-stats-card {
    display: inline-block;
  }
  .cart-stats-card:first-child + div {
    margin: 0 1em;
  }
  .cart-stats-card h3 {
    margin-top: 0.25em;
  }
  .cart-stats-card h3 + p {
    margin-top: 0.25em;
  }
  .cart-stats-card p {
    margin-top: 1em;
  }
  .abandoned .amount,
  .abandoned h3 { color: #B3000A; }
  .pending .amount,
  .pending h3 { color: #AE5B00; }
  .paid .amount,
  .paid h3 { color: #6d8a14; }
  .cart-stats-card small {
    font-size: 0.7em;
    text-transform: uppercase;
  }
</style>

<div class="cart-stats">
  <div class="cart-stats-card">
    <h3>Shipped orders</h3>
  <?php foreach ($shippedOrders as $order): ?>
    <div><a href="pages/prints/orders/<?= $order->uid() ?>/edit"><?= $order->order_id()->value() ?></a></div>
  <?php endforeach ?>
  </div>

  <div class="cart-stats-card">
    <h3>Unshipped orders</h3>
  <?php foreach ($unshippedOrders as $order): ?>
    <div><a href="pages/prints/orders/<?= $order->uid() ?>/edit"><?= $order->order_id()->value() ?></a></div>
  <?php endforeach ?>
  </div>
</div>