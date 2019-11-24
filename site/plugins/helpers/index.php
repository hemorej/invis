<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('helpers/helpers', [
  'options' => [
    'cache.backend' => true
  ]
]);

function addToStructure($page, $field, $data = array())
{
  $fieldData = $page->$field()->yaml();
  $key = array_search($data['sku'], array_column($fieldData, 'sku'));
  unset($fieldData[$key]);
  $fieldData = array_values($fieldData);

  $fieldData[] = $data;
  $fieldData = Yaml::encode($fieldData);
  try {
    $page->update(array($field => $fieldData));
    return true;
  } catch(Exception $e) {
    return $e->getMessage();
  }
}

function getPreview($image){

    if($image->isLandscape())
      return $image->resize(600)->url();

    return $image->resize(null, 600)->url();
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
  $cache = kirby()->cache('backend');

  $images = array();
  if($data = $cache->get('images')){
    $images = json_decode($data);
  }else{
    foreach(page('projects/portfolio')->files() as $image){
      if($image->isLandscape())
        $images[] = $image->filename();
    }
    $cache->set('images', json_encode($images), 43200);
  }

  $file = $images[array_rand($images)];
  $image = page("projects/portfolio/")->file($file);

  return array('images' => $image);
}

function location(){
  $cache = kirby()->cache('backend');

  $remote = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
  if($remote == false)
    return 'CA';

  if($data = $cache->get($remote)){
    $loc = json_decode($data);
  }else{
    $access_key = kirby()->option('ipstack_key');
    $requestURL = "http://api.ipstack.com/$remote?access_key=$access_key&fields=country_code,location.is_eu&language=en&output=json";

    $data = Remote::get($requestURL);
    $cache->set($remote, $data);
    $loc = json_decode($data->content());
  }

  if($loc->location->is_eu == true)
      return $loc->country_code;

    return false;
}

