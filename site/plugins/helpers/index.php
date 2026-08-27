<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Mailbun\Mailbun;
use Kirby\Data\Yaml;
use Kirby\Http\Remote;
use Kirby\Toolkit\Date;

Kirby::plugin( 'helpers/helpers', [
	'options' => [
		'cache.backend' => true,
	],
] );

/**
 * Upserts a single row into a Kirby structure field, keyed by its `suuid`.
 *
 * The existing row with the same `suuid` (if any) is removed and the new
 * `$data` appended, so callers can treat it as "set this variant/order line
 * to exactly these values" without worrying about duplicates.
 *
 * @param \Kirby\Cms\Page $page  Page owning the field
 * @param string          $field Structure field name (e.g. 'variants')
 * @param array           $data  Row data; must contain a 'suuid' key
 * @return string|true  True on success, or the exception message on failure
 * @throws Throwable
 */
function addToStructure( $page, $field, $data = [] )
{
	$fieldData = $page->$field()->yaml();
	$key = array_search( $data['suuid'], array_column( $fieldData, 'suuid' ) );
	unset( $fieldData[$key] );
	$fieldData = array_values( $fieldData );

	$fieldData[] = $data;
	$fieldData = Yaml::encode( $fieldData );
	try {
		return $page->update( [$field => $fieldData] );
	} catch( Exception $e ) {
		return $e->getMessage();
	}
}

/**
 * Returns a preview URL for an image, sized by orientation: landscape images
 * are constrained to 600px wide, portrait images to 500px tall.
 *
 * @param \Kirby\Cms\File $image
 * @return string Resized image URL
 */
function getPreview( $image )
{

	if( $image->isLandscape() )
		return $image->resize( 600 )->url();

	return $image->resize( null, 500 )->url();
}

/**
 * Formats a date string as "Month ordinal-word", e.g. "March fourteenth",
 * used for the journal archive headings.
 *
 * @param string $string Any strtotime()-parseable date
 * @return string
 */
function archiveDate( $string )
{
	$month = date( 'F', strtotime( $string ) );
	$day = date( 'j', strtotime( $string ) ); // day of month, 1-31
	$year = '\'' . date( 'y', strtotime( $string ) );

	// Index 0 = "first"; look up with $day - 1 below.

	$textualNumbers = [
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
		'twenty-ninth',
		'thirtieth',
		'thirty-first',
	];

	return implode( ' ', [$month, $textualNumbers[$day - 1]] );
}

/**
 * Picks a random landscape image from the portfolio project for the home page.
 *
 * The list of eligible filenames is cached for 12 hours (43200s); only the
 * filename list is cached, a fresh random pick happens on every call.
 *
 * @return array{images: \Kirby\Cms\File}
 * @throws \Kirby\Exception\InvalidArgumentException
 */
function getHomeImage()
{
	$cache = kirby()->cache( 'helpers.helpers.backend' );

	$images = [];
	if( $data = $cache->get( 'images' ) ) {
		$images = json_decode( $data );
	} else {
		foreach( page( 'projects/portfolio' )->files() as $image ) {
			if( $image->isLandscape() )
				$images[] = $image->filename();
		}
		$cache->set( 'images', json_encode( $images ), 43200 );
	}

	$file = $images[array_rand( $images )];
	$image = page( "projects/portfolio/" )->file( $file );

	return ['images' => $image];
}

/**
 * Masks an email address for safe inclusion in logs, e.g. "jerome@example.com" -> "j***@example.com".
 *
 * @param string|null $email
 * @return string
 */
function maskEmail( ?string $email ): string
{
	if( empty( $email ) || !str_contains( $email, '@' ) )
		return 'unknown';

	[$local, $domain] = explode( '@', $email, 2 );
	return substr( $local, 0, 1 ) . '***@' . $domain;
}

/**
 * Resolves the visitor's approximate location from their IP via ipapi.co.
 *
 * In non-production environments this skips the lookup and returns the
 * hard-coded `geolocation` option. Successful lookups are cached per IP.
 * Returns 'CA' when the remote address is unusable and null on API error.
 *
 * @return object|string|null Decoded ipapi.co response, a country code, or null
 */
function location()
{
	if( !in_array( kirby()->option( 'env', 'dev' ), ['prod', 'production'] ) )
		return kirby()->option( 'geolocation' );

	try {
		$cache = kirby()->cache( 'helpers.helpers.backend' );
		$remote = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );

		if( $remote == false )
			return 'CA';

		if( $data = $cache->get( $remote ) ) {
			$loc = json_decode( $data );
		} else {
			$key = kirby()->option( 'ipapi_key' );
			$requestURL = "https://ipapi.co/$remote/json?key=$key";

			$data = Remote::get( $requestURL );
			$response = $data->content();

			if( property_exists( $response, 'error' ) && $response->error == true ) {
				throw new Exception( $response->error );
			}

			$cache->set( $remote, $response );
			$loc = json_decode( $response );
		}

		return $loc;
	} catch( Exception $e ) {
		$logger = ( new Logger\Logger( 'geolocation' ) )->getLogger();
		$logger->error( 'could not resolve IP address', ['ip' => $remote ?? null, 'reason' => $e->getMessage()] );

		return null;
	}
}

/**
 * Emails the site owner about an order-processing exception, then logs the
 * outcome. Never throws — a failed alert must not break the caller's own
 * error handling.
 *
 * @param string $sid     Session / transaction id the failure relates to
 * @param string $orderId  Order suuid, if one exists yet
 * @param string $error    Human-readable failure reason
 * @return void
 */
function sendAlert( $sid, $orderId, $error = "Unknown reason" )
{
	$logger = ( new Logger\Logger( 'order' ) )->getLogger();

	try {
		$mailbun = new Mailbun();
		$mailbun->send(
			kirby()->option( 'alert_address' ),
			'Order exception alert',
			'error',
			['orderId' => $orderId, 'sid' => $sid, 'error' => $error]
		);

		$logger->warning( "order exception alert sent", ['txn' => $sid, 'order' => $orderId, 'reason' => $error] );
	} catch( \Throwable $t ) {
		$logger->error( "failed to send order exception alert", ['txn' => $sid, 'order' => $orderId, 'reason' => $error, 'mail_error' => $t->getMessage()] );
	}
}

/**
 * Static list of country names for the checkout address <select>.
 *
 * @return string[]
 */
function countryList()
{
	return [
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
		"Zimbabwe"];
}

/**
 * Today's date in the site's timezone (America/Montreal), as 'Y-m-d'.
 *
 * @return string
 */
function getToday()
{
	return Date::today(new DateTimeZone('America/Montreal'))->format('Y-m-d');
}