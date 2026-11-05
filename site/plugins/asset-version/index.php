<?php

use Kirby\Cms\App;

/**
 * Appends a content hash (?v=...) to local css()/js() asset URLs so browsers
 * can cache them indefinitely and still pick up a changed file on the next
 * request. External URLs and files that do not exist are left untouched.
 */
$versioned = static function ( App $kirby, string $url ): string {
	static $hashes = [];

	if( preg_match( '#^(?:[a-z][a-z0-9+.-]*:)?//#i', $url ) || str_contains( $url, '?' ) ) {
		return $url;
	}

	$file = $kirby->root( 'index' ) . '/' . ltrim( $url, '/' );

	if( !array_key_exists( $file, $hashes ) ) {
		$hashes[$file] = is_file( $file ) ? substr( md5_file( $file ), 0, 10 ) : null;
	}

	return $hashes[$file] === null ? $url : $url . '?v=' . $hashes[$file];
};

App::plugin( 'asset-version/asset-version', [
	'components' => [
		'css' => fn ( App $kirby, string $url, $options = null ): string => $versioned( $kirby, $url ),
		'js'  => fn ( App $kirby, string $url, $options = null ): string => $versioned( $kirby, $url ),
	],
] );
