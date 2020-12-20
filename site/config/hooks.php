<?php
include_once(__DIR__ . '/../models/shippingHandler.php');

return [
  'page.update:after' => function ($newPage, $oldPage = null) {
  	$shippingHandler = new \ShippingHandler();
	$shippingHandler->handle($newPage, $oldPage);
  }
];