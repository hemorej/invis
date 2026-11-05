<?php

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Remote;
use Kirby\Uuid\Uuid;
use Logger\Logger;
use Mailbun\Mailbun;

App::plugin( 'helpers/helpers', [
	'options' => [
		'cache.backend' => true,
	],
] );

/**
 * Upserts a single row into a Kirby structure field, keyed by its `suuid`.
 *
 * An existing row with the same `suuid` is replaced in place; otherwise the
 * new `$data` is appended. Callers can treat it as "set this variant/order line
 * to exactly these values" without worrying about duplicates or row order.
 *
 * @param \Kirby\Cms\Page $page  Page owning the field
 * @param string          $field Structure field name (e.g. 'variants')
 * @param array           $data  Row data; must contain a 'suuid' key
 * @return \Kirby\Cms\Page The updated page
 * @throws InvalidArgumentException When `$data` has no `suuid`
 * @throws \Throwable When the page update fails (callers must handle it)
 */
function addToStructure( Page $page, string $field, array $data = [] ): Page
{
	if( empty( $data['suuid'] ) )
		throw new InvalidArgumentException( 'addToStructure requires a suuid in $data' );

	$fieldData = $page->$field()->yaml();

	// Compare as strings: YAML may hand back numeric-looking ids as ints.
	$key = array_search( (string) $data['suuid'], array_map( 'strval', array_column( $fieldData, 'suuid' ) ), true );

	if( $key === false ) {
		$fieldData[] = $data;
	} else {
		$fieldData[$key] = $data;
	}

	return $page->update( [$field => Yaml::encode( array_values( $fieldData ) )] );
}

/**
 * Gives every variant of a product page a `suuid` if it doesn't have one yet.
 *
 * The `suuid` is the stable id the cart, the order lines and the Stripe price
 * lookup key all rely on, so it must exist before a product is synced or sold.
 * Existing ids are never changed. Returns the page unchanged when nothing was
 * missing, or the updated page (use that one, the passed-in object is stale).
 *
 * @param Page $page A page that may have a `variants` structure (only `product` pages are touched)
 * @return Page
 * @throws \Throwable When the page update fails
 */
function ensureVariantIds( Page $page ): Page
{
	if( $page->intendedTemplate()->name() !== 'product' )
		return $page;

	$variants = $page->variants()->yaml();
	$changed = false;

	foreach( $variants as &$variant ) {
		if( empty( $variant['suuid'] ) ) {
			$variant['suuid'] = Uuid::generate();
			$changed = true;
		}
	}
	unset( $variant );

	if( !$changed )
		return $page;

	return kirby()->impersonate( 'kirby', fn() => $page->update( ['variants' => Yaml::encode( $variants )] ) );
}

/**
 * Returns a preview URL for an image, sized by orientation: landscape images
 * are constrained to 600px wide, portrait images to 500px tall.
 *
 * @param \Kirby\Cms\File $image
 * @return string Resized image URL
 */
function getPreview( File $image ): string
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
function archiveDate( string $string ): string
{
	try {
		$date = new DateTimeImmutable( $string );
	} catch( Exception ) {
		return $string; // not a parseable date: show it as entered
	}

	$month = $date->format( 'F' );
	$day = (int) $date->format( 'j' ); // day of month, 1-31

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
 * @return array{images: \Kirby\Cms\File|null} `images` is null when there is nothing to show
 */
function getHomeImage(): array
{
	$cache = kirby()->cache( 'helpers.helpers.backend' );

	$images = [];
	if( $data = $cache->get( 'images' ) ) {
		$images = json_decode( $data );
	} else {
		foreach( page( 'projects/portfolio' )?->files() ?? [] as $image ) {
			if( $image->isLandscape() )
				$images[] = $image->filename();
		}
		// Don't cache an empty list, so adding images shows up immediately.
		if( $images !== [] )
			$cache->set( 'images', json_encode( $images ), 43200 );
	}

	// No portfolio images (e.g. a bare local checkout): the home page renders without a hero.
	if( $images === [] )
		return ['images' => null];

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
 * hard-coded `geolocation` option. Lookups are cached per IP for a day; a
 * failed lookup (or a non-public IP) yields a Canada default, cached briefly
 * on failure so a rate-limited or unreachable API isn't hit on every request.
 * Always returns an object, so callers can read `->country_name` safely.
 *
 * @return object Decoded ipapi.co response, the dev `geolocation` option, or the default
 */
function location(): object
{
	if( !in_array( kirby()->option( 'env', 'dev' ), ['prod', 'production'] ) )
		return kirby()->option( 'geolocation' );

	$default = (object) ['country_code' => 'CA', 'country_name' => 'Canada'];

	$ip = filter_var( kirby()->visitor()->ip(), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	if( $ip === false )
		return $default;

	$cache = kirby()->cache( 'helpers.helpers.backend' );
	$cacheKey = 'geo-' . $ip;

	if( $data = $cache->get( $cacheKey ) )
		return json_decode( $data );

	try {
		$key = kirby()->option( 'ipapi_key' );
		$response = Remote::get( "https://ipapi.co/$ip/json?key=$key", ['timeout' => 3] );
		$loc = json_decode( $response->content() );

		// ipapi reports failures (rate limit, bad key...) as JSON with `error: true`
		if( $response->code() !== 200 || !is_object( $loc ) || !empty( $loc->error ) )
			throw new Exception( $loc->reason ?? 'HTTP ' . $response->code() );

		$cache->set( $cacheKey, $response->content(), 1440 );

		return $loc;
	} catch( Exception $e ) {
		$logger = ( new Logger( 'geolocation' ) )->getLogger();
		$logger->error( 'could not resolve IP address', ['ip' => $ip, 'reason' => $e->getMessage()] );

		$cache->set( $cacheKey, json_encode( $default ), 10 );

		return $default;
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
function sendAlert( ?string $sid, ?string $orderId, string $error = "Unknown reason" ): void
{
	$logger = ( new Logger( 'order' ) )->getLogger();

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
function countryList(): array
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
function getToday(): string
{
	return ( new DateTimeImmutable( 'now', new DateTimeZone( 'America/Montreal' ) ) )->format( 'Y-m-d' );
}