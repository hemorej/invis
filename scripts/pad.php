<?php

$files = scandir('./');
foreach ($files as $file){
  if(is_dir($file) && !preg_match('/\.\.?/',$file)){
        preg_match('/(\d+)-/', $file, $match);
        $padding = 4 - strlen($match[1]) ;
        $prefix = '';
        for($i = 0; $i<$padding; $i++){
          $prefix .= '0' ;
        }
        rename($file, $prefix.$file);
  }
}

?>

