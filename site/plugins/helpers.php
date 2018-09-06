<?php

use \Gilbitron\Util\SimpleCache;

function addToStructure($page, $field, $data = array())
{
  $fieldData = $page->$field()->yaml();
  $key = array_search($data['sku'], array_column($fieldData, 'sku'));
  unset($fieldData[$key]);
  $fieldData = array_values($fieldData);

  $fieldData[] = $data;
  $fieldData = yaml::encode($fieldData);
  try {
    $page->update(array($field => $fieldData));
    return true;
  } catch(Exception $e) {
    return $e->getMessage();
  }
}

function inStock($variant)
{
  if(strstr($variant, '::')){
    $idParts = explode('::',$variant);
    $uri = $idParts[0];
    $sku = $idParts[1];

    $variant = page($uri)->variants()->toStructure()->findBy('sku', $sku);
    return $variant->stock->value();
  }

  if (!is_numeric($variant->stock()->value) and $variant->stock()->value === '') return true;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) <= 0) return false;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) > 0) return intval($variant->stock()->value);

  return false;
}

function getUniqueId($type = 'sku')
{
  $prefix = 'pre_';

  switch($type){
    case 'sku':
      $prefix = 'sku_';
      break;
    case 'product':
      $prefix = 'prd_';
      break;
    case 'order':
      $prefix = 'ord_';
      break;
  }

  $bytes = openssl_random_pseudo_bytes(14, $cstrong);
  $hex   = bin2hex($bytes);

  return $prefix . $hex;
}

function getPreview($image){

    if($image->isLandscape()){
        $preview = thumb($image, array('width' => 600))->url();
    }else{
        $preview = thumb($image, array('height' => 600))->url();
    }
    return $preview;
}

function archiveDate($string){
  $month = date('F', strtotime($string));
  $day = date('j', strtotime($string));
  $year = '\'' . date('y', strtotime($string));

  $textualNumbers = array(
  'first',
  'second',
  'third',
  'fourth',
  'five',
  'six',
  'seven',
  'eight',
  'nine',
  'ten',
  'eleven',
  'twelve',
  'thirteen',
  'fourteen',
  'fifteen',
  'sixteen',
  'seventeen',
  'eighteen',
  'nineteen',
  'twenty',
  'twenty-one',
  'twenty-two',
  'twenty-three',
  'twenty-four',
  'twenty-five',
  'twenty-six',
  'twenty-seven',
  'twenty-eight',
  'twenty-nine',
  'thirty',
  'thirty-one');

  return implode(' ', array($month, $textualNumbers[$day-1], $year));
}

function getHomeImage(){

  $cache = new SimpleCache();
  $cache->cache_path = __DIR__ . '/../cache/';
  $cache->cache_time = 2592000; //30d

  $images = array();
  if($data = $cache->get_cache('images')){
    $images = json_decode($data);
  }else{
    foreach(page('projects/portfolio')->files() as $image){
      if($image->isLandscape())
        $images[] = $image->filename();
    }
    $cache->set_cache('images', json_encode($images));
  }

  $file = $images[array_rand($images)];
  $image = page("projects/portfolio/")->file($file);

  return array('images' => $image);
}

function location(){
    
  $cache = new SimpleCache();
  $cache->cache_path = __DIR__ . '/../cache/';
  $cache->cache_time = 86400; //24h

  $remote = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
  if($remote == false)
    return 'CA';

  if($data = $cache->get_cache($remote)){
    $loc = json_decode($data);
  }else{
    $access_key = c::get('ipstack_key');

    $data = $cache->do_curl('http://api.ipstack.com/' . $remote . '?access_key=' . $access_key . '&fields=country_code,location.is_eu&language=en&output=json');
    $cache->set_cache($remote, $data);
    $loc = json_decode($data);
  }

  if($loc->location->is_eu == true)
      return $loc->country_code;

    return false;
  }