<?php

namespace Newsletter;

use Kirby\Cms\File;
use Kirby\Uuid\Uuid;

/**
 * Single source of truth for the newsletter email's visual design.
 *
 * Every value that is fixed across editions lives here — accent colour, type
 * stack, page geometry, footer chrome. The block snippets under
 * site/snippets/emails/blocks/ read from this class, so a change here applies
 * to every edition, past preview and future send.
 */
class Design
{
	const ACCENT      = '#ffb700';
	const INK         = '#2a2a2a';
	const BODY        = '#3c3c3c';
	const QUIET       = '#555555';
	const FAINT       = '#9a9a9a';
	const PAPER       = '#fdfdfd';
	const DESK        = '#eceae6';
	const PLACEHOLDER = '#dedbd6';
	const RULE        = '#e7e7e7';

	const SERIF   = "Georgia,'Times New Roman',serif";
	const DISPLAY = "'Arial Narrow',Arial,Helvetica,sans-serif";
	const MONO    = "'Courier New',Courier,monospace";

	/** Outer email width in px */
	const PAGE = 600;
	/** Inner content width in px */
	const CONTENT = 520;
	/** Horizontal padding in px */
	const GUTTER = 40;

	/**
	 * Postal address shown in the footer, sourced from the site's Address
	 * field (Panel > Site > Config) so it stays out of version control.
	 *
	 * @return string
	 */
	public static function address()
	{
		return nl2br( self::esc( site()->address() ) );
	}

	/**
	 * @return array<array{label: string, href: string}>
	 */
	public static function footerLinks()
	{
		return [
			['label' => 'unsubscribe', 'href' => url( 'newsletter' )],
			['label' => 'past editions', 'href' => url( 'newsletter/editions' )],
			['label' => 'the-invisible-cities.com', 'href' => url( '/' )],
		];
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public static function esc( $value )
	{
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Opens the content cell every block sits in.
	 *
	 * @param int $padTop
	 * @return string
	 */
	public static function cellOpen( int $padTop )
	{
		return '<tr><td class="pad" style="padding:' . $padTop . 'px ' . self::GUTTER . 'px 0 ' . self::GUTTER . 'px;">';
	}

	/**
	 * @return string
	 */
	public static function cellClose()
	{
		return '</td></tr>';
	}

	/**
	 * A 1px hairline the full content width.
	 *
	 * @return string
	 */
	public static function hairline()
	{
		return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="' . self::CONTENT . '" style="width:' . self::CONTENT . 'px; border-collapse:collapse;"><tr>'
			. '<td height="1" style="height:1px; line-height:1px; font-size:0; background:' . self::RULE . ';">&nbsp;</td>'
			. '</tr></table>';
	}

	/**
	 * The gold stub plus hairline used above every section label.
	 *
	 * @return string
	 */
	public static function accentRule()
	{
		return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="' . self::CONTENT . '" style="width:' . self::CONTENT . 'px; border-collapse:collapse;"><tr>'
			. '<td width="28" height="2" style="width:28px; height:2px; line-height:2px; font-size:0; background:' . self::ACCENT . ';">&nbsp;</td>'
			. '<td height="1" style="height:1px; line-height:1px; font-size:0; background:' . self::RULE . ';">&nbsp;</td>'
			. '</tr></table>';
	}

	/**
	 * Renders a hard-cropped image at an exact pixel size, or a matching grey
	 * placeholder when the image is missing — so a letter can be laid out
	 * before the scans are in.
	 *
	 * @param File|null $file
	 * @param int $width
	 * @param int $height
	 * @param string $alt
	 * @return string
	 */
	public static function image( $file, int $width, int $height, string $alt = '' )
	{
		if( empty( $file ) )
			return self::placeholder( $width, $height );

		$thumb = $file->thumb( ['width' => $width, 'height' => $height, 'crop' => true] );

		return '<img src="' . self::esc( $thumb->url() ) . '" width="' . $width . '" height="' . $height . '"'
			. ' alt="' . self::esc( $alt !== '' ? $alt : $file->alt()->value() ) . '"'
			. ' style="display:block; width:' . $width . 'px; height:' . $height . 'px; border:0; outline:none; text-decoration:none;">';
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public static function placeholder( int $width, int $height )
	{
		return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="' . $width . '" style="width:' . $width . 'px; border-collapse:collapse;"><tr>'
			. '<td align="center" valign="middle" height="' . $height . '" style="width:' . $width . 'px; height:' . $height . 'px; background:' . self::PLACEHOLDER . '; font-family:' . self::MONO . '; font-size:11px; line-height:16px; color:#6c6c6c; letter-spacing:0.04em;">[ ' . $width . ' &times; ' . $height . ' ]</td>'
			. '</tr></table>';
	}

	/**
	 * Resolves a link field value (page://, file:// or a plain URL) to an
	 * absolute URL suitable for an email client.
	 *
	 * @param string|null $value
	 * @return string
	 */
	public static function link( $value )
	{
		$value = trim( (string) $value );

		if( $value === '' )
			return url( '/' );

		if( str_starts_with( $value, 'page://' ) || str_starts_with( $value, 'file://' ) ) {
			$model = Uuid::for( $value )->model();
			return empty( $model ) ? url( '/' ) : $model->url();
		}

		if( str_starts_with( $value, 'mailto:' ) || str_starts_with( $value, 'tel:' ) )
			return $value;

		if( str_starts_with( $value, '/' ) )
			return url( $value );

		return $value;
	}

	/**
	 * Applies email-safe styling to the inline markup a writer field produces.
	 * Writer emits bare <a>, <strong> and <em> tags; email clients need the
	 * colours spelled out inline.
	 *
	 * @param string|null $html
	 * @return string
	 */
	public static function inlineHtml( $html )
	{
		$html = (string) $html;

		$html = str_replace(
			'<a ',
			'<a style="color:' . self::INK . '; text-decoration:none; border-bottom:1px solid ' . self::ACCENT . ';" ',
			$html
		);
		$html = str_replace( '<em>', '<em style="font-style:italic;">', $html );
		$html = str_replace( '<strong>', '<strong style="font-weight:600; color:' . self::INK . ';">', $html );

		return $html;
	}
}
