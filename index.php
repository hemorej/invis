<?php

define('KIRBY_HELPER_E', false);

require __DIR__ . '/kirby/bootstrap.php';

echo (new Kirby)->render();
