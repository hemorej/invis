<?php 

return array(
  'title' => 'Orders (Last 30 days)',
  'html' => function() {
    return tpl::load(__DIR__.DS.'cart-stats.html.php');
  }  
);