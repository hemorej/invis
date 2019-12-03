<?php
include_once(__DIR__ . '/../models/shippingHandler.php');

return [
  'page.update:after' => function ($page, $oldPage = null) {
  	$shippingHandler = new \ShippingHandler();
	$shippingHandler->handle($page, $oldPage);
  }
];