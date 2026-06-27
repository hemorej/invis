<?php

define('KIRBY_HELPER_E', false);
define('KIRBY_HELPER_GO', false);

require __DIR__ . '/kirby/bootstrap.php';

echo (new Kirby)->render();
