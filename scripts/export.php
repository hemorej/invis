<?php

$xml_string = file_get_contents( "./export.xml") ;

$parser = xml_parser_create() ;
xml_parse_into_struct($parser, $xml_string, $xml_array);
xml_parser_free($parser);

$counter = 1 ;
$date ;
$dir ;

foreach($xml_array as $tag) {

  if($tag["tag"] == 'PUBDATE'){
        $date = new DateTime($tag["value"], new DateTimeZone('America/Montreal')) ;
        $dir = str_pad($counter, 2, "0", STR_PAD_LEFT) . '-' . $date->format('d-m-Y') ;
        mkdir("/tmp/content/".$dir);
        $counter++ ;
        continue ;
  }
  if($tag["tag"] == 'CONTENT:ENCODED'){
        preg_match('/<em>(.+)<\/em>/', $tag["value"], $match) ;
        $file = "Published: ". $date->format('d-m-Y') . "\n" . '----' . "\nText: " ;
        if(isset($match[1])) $file .= $match[1] ;
        file_put_contents("/tmp/content/".$dir."/article.txt", $file) ;

        $list = explode("\n", $tag["value"]) ; 
        foreach($list as $each){
                preg_match('/([^\/]+jpg).+/', $each, $images) ;
                if(isset($images[1])){
                        $cmd = "find /tmp/search -iname " . $images[1] . " -type f -print0 | xargs -0 -I '{}' /bin/mv \"{}\" /tmp/content/". $dir ;
                        exec($cmd, $output) ;
                }
        }
        continue ;
  }
}

?>
