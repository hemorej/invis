<?php
include_once(__DIR__ . '/../models/shippingHandler.php');

return [
  'page.update:after' => function ($page, $oldPage = null) {
  	$stripeHandler = new \ShippingHandler();
	$stripeHandler->handle($page, $oldPage);
  }
];