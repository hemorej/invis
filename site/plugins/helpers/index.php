<?php

@include_once __DIR__ . '/vendor/autoload.php';
use \Mailbun\Mailbun;
use \Payments\StripeConnector as Stripe;

Kirby::plugin('helpers/helpers', [
  'options' => [
    'cache.backend' => true
  ],
  'routes' => [[
    'pattern' => 'customer',
    'method' => 'POST',
    'action'  => function () {
      if(csrf(get('csrf')) === true){
        $stripe = new Stripe();
        $customer = $stripe->findCustomer(urldecode(get('email')));
        if(!empty($customer) && !empty($customer['data'])){
          $url = $stripe->redirectToPortal($customer['data'][0]->id);
          return [
            'location' => $url
          ];
        }
        
        return [
          'location' => null
        ];
      }
    }
  ]]
]);

function addToStructure($page, $field, $data = array())
{
  $fieldData = $page->$field()->yaml();
  $key = array_search($data['suuid'], array_column($fieldData, 'suuid'));
  unset($fieldData[$key]);
  $fieldData = array_values($fieldData);

  $fieldData[] = $data;
  $fieldData = \Yaml::encode($fieldData);
  try {
    kirby()->impersonate('kirby');
    $page->update(array($field => $fieldData));
    return true;
  } catch(\Exception $e) {
    return $e->getMessage();
  }
}

function getPreview($image){

    if($image->isLandscape())
      return $image->resize(600)->url();

    return $image->resize(null, 500)->url();
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
  'fifth',
  'sixth',
  'seventh',
  'eighth',
  'ninth',
  'tenth',
  'eleventh',
  'twelfth',
  'thirteenth',
  'fourteenth',
  'fifteenth',
  'sixteenth',
  'seventeenth',
  'eighteenth',
  'nineteenth',
  'twentieth',
  'twenty-first',
  'twenty-second',
  'twenty-third',
  'twenty-fourth',
  'twenty-fifth',
  'twenty-sixth',
  'twenty-seventh',
  'twenty-eighth',
  'twenty-nineth',
  'thirtieth',
  'thirty-first');

  return implode(' ', array($month, $textualNumbers[$day-1]));
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
  try{
    $cache = kirby()->cache('backend');
    // $remote = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    $remote = filter_var('192.0.229.53', FILTER_VALIDATE_IP);
    if($remote == false)
      return 'CA';

    if($data = $cache->get($remote)){
      $loc = json_decode($data);
    }else{
      $key = kirby()->option('ipapi_key');
      $requestURL = "https://ipapi.co/$remote/json?key=$key";

      $data = \Remote::get($requestURL);
      $cache->set($remote, $data->content());
      $loc = json_decode($data->content());
    }

    return $loc;
  }catch(\Exception $e){
    $logger = (new Logger\Logger('geolocation'))->getLogger();
    $logger->error('Could not resolve IP address: ' . $e->getMessage());
  
    return null;
  }
}

function sendAlert($sid, $orderId, $error = "Unknown reason")
{
  $mailbun = new Mailbun();
  $mailbun->send(
      kirby()->option('alert_address'),
        'Order exception alert',
        'error', 
        array('orderId' => $orderId, 'sid' => $sid, 'error' => $error)
  );

  $logger = (new Logger\Logger('order'))->getLogger();
  $logger->info("Alert sent for " . $orderId);
}

function countryList(){
  return array(
    "Afghanistan",
    "Aland Islands",
    "Albania",
    "Algeria",
    "Andorra",
    "Angola",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
    "Bangladesh",
    "Barbados",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bhutan",
    "Bolivia",
    "Bosnia and Herzegovina",
    "Botswana",
    "Brazil",
    "Brunei Darussalam",
    "Bulgaria",
    "Burkina Faso",
    "Burma",
    "Burundi",
    "Cambodia",
    "Cameroon",
    "Canada",
    "Cape Verde",
    "Cayman Islands",
    "Central African Republic",
    "Chad",
    "Chile",
    "China",
    "Colombia",
    "Comoros",
    "Congo-Kinshasa",
    "Congo, Republic of",
    "Costa Rica",
    "Cote d'Ivoire",
    "Croatia",
    "Cuba",
    "Cyprus",
    "Czech Republic",
    "Denmark",
    "Djibouti",
    "Dominica",
    "Dominican Republic",
    "Ecuador",
    "Egypt",
    "El Salvador",
    "Equatorial Guinea",
    "Eritrea",
    "Estonia",
    "Ethiopia",
    "Fiji",
    "Finland",
    "France",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Gibraltar",
    "Greece",
    "Greenland",
    "Grenada",
    "Guam",
    "Guatemala",
    "Guinea",
    "Guinea-Bissau",
    "Guyana",
    "Haiti",
    "Honduras",
    "Hong Kong",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran",
    "Iraq",
    "Ireland",
    "Israel",
    "Italy",
    "Jamaica",
    "Japan",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Kiribati",
    "Korea, Republic of",
    "Kuwait",
    "Kyrgyz Republic",
    "Laos",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Liechtenstein",
    "Lithuania",
    "Luxembourg",
    "Macao",
    "Macedonia",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Maldives",
    "Mali",
    "Malta",
    "Marshall Islands",
    "Mauritania",
    "Mauritius",
    "Mexico",
    "Micronesia",
    "Moldova",
    "Monaco",
    "Mongolia",
    "Montenegro",
    "Morocco",
    "Mozambique",
    "Namibia",
    "Nauru",
    "Nepal",
    "Netherlands",
    "New Caledonia",
    "New Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "Norway",
    "Oman",
    "Pakistan",
    "Palau",
    "Palestinian Territory",
    "Panama",
    "Papua New Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Poland",
    "Portugal",
    "Puerto Rico",
    "Qatar",
    "Romania",
    "Russian Federation",
    "Rwanda",
    "Saint Kitts and Nevis",
    "Saint Lucia",
    "Saint Vincent",
    "Samoa",
    "San Marino",
    "Sao Tome and Principe",
    "Saudi Arabia",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leone",
    "Singapore",
    "Slovakia",
    "Slovenia",
    "Solomon Islands",
    "Somalia",
    "South Africa",
    "South Sudan",
    "Spain",
    "Sri Lanka",
    "Sudan",
    "Suriname",
    "Swaziland",
    "Sweden",
    "Switzerland",
    "Syrian Arab Republic",
    "Taiwan",
    "Tajikistan",
    "Tanzania",
    "Thailand",
    "Timor-Leste",
    "Togo",
    "Tonga",
    "Trinidad and Tobago",
    "Tunisia",
    "Turkey",
    "Turkmenistan",
    "Tuvalu",
    "Uganda",
    "Ukraine",
    "United Arab Emirates",
    "United Kingdom",
    "United States",
    "Uruguay",
    "Uzbekistan",
    "Vanuatu",
    "Vatican City",
    "Venezuela",
    "Vietnam",
    "Western Sahara",
    "Yemen",
    "Zambia",
    "Zimbabwe");
}