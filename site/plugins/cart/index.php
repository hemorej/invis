<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('cart/cart', [
  'options' => [
    'cache.backend' => true
  ]
]);