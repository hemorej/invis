<?php

namespace Newsletter;

/**
 * Encrypts subscriber emails at rest (libsodium secretbox) and derives a
 * keyed digest used for duplicate lookup without decrypting stored records.
 */
class Crypto
{
	/**
	 * @return string
	 * @throws \Exception
	 */
	protected static function key()
	{
		$key = kirby()->option( 'newsletter_encryption_key' );
		if( empty( $key ) )
			throw new \Exception( 'newsletter_encryption_key is not configured' );

		return sodium_base642bin( $key, SODIUM_BASE64_VARIANT_ORIGINAL );
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected static function hmacSecret()
	{
		$secret = kirby()->option( 'newsletter_hmac_secret' );
		if( empty( $secret ) )
			throw new \Exception( 'newsletter_hmac_secret is not configured' );

		return $secret;
	}

	/**
	 * @param string $plain
	 * @return string
	 * @throws \Exception
	 */
	public static function encrypt( string $plain )
	{
		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$cipher = sodium_crypto_secretbox( $plain, $nonce, self::key() );

		return base64_encode( $nonce . $cipher );
	}

	/**
	 * @param string $encoded
	 * @return string
	 * @throws \Exception
	 */
	public static function decrypt( string $encoded )
	{
		$decoded = base64_decode( $encoded );
		$nonce = substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$cipher = substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		$plain = sodium_crypto_secretbox_open( $cipher, $nonce, self::key() );
		if( $plain === false )
			throw new \Exception( 'Failed to decrypt value, key mismatch or corrupted data' );

		return $plain;
	}

	/**
	 * Normalizes and hashes an email for equality lookup without decrypting stored records.
	 *
	 * @param string $email
	 * @return string
	 * @throws \Exception
	 */
	public static function emailDigest( string $email )
	{
		return hash_hmac( 'sha256', self::normalizeEmail( $email ), self::hmacSecret() );
	}

	/**
	 * @param string $uuid
	 * @param string $purpose
	 * @return string
	 * @throws \Exception
	 */
	public static function token( string $uuid, string $purpose )
	{
		return $uuid . '.' . hash_hmac( 'sha256', $uuid . '|' . $purpose, self::hmacSecret() );
	}

	/**
	 * @param string $token
	 * @param string $purpose
	 * @return string|false Returns the uuid on success, false if the token is invalid
	 * @throws \Exception
	 */
	public static function verifyToken( string $token, string $purpose )
	{
		$parts = explode( '.', $token, 2 );
		if( count( $parts ) !== 2 )
			return false;

		[$uuid, $signature] = $parts;
		$expected = hash_hmac( 'sha256', $uuid . '|' . $purpose, self::hmacSecret() );

		return hash_equals( $expected, $signature ) ? $uuid : false;
	}

	/**
	 * @param string $email
	 * @return string
	 */
	public static function normalizeEmail( string $email )
	{
		return strtolower( trim( $email ) );
	}
}
