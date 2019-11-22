<?php
include_once(__DIR__ . '/../models/uidHandler.php');

return [
  'panel.page.*' => function ($page, $oldPage = null) {
  	$stripeHandler = new \UidHandler();
	$stripeHandler->handle($page, $oldPage, $this->type());
  }
];