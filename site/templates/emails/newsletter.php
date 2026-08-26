<?php
/**
 * Document shell for a newsletter edition.
 *
 * Deliberately thin — the letter's design lives in
 * site/snippets/emails/newsletter-body.php so the send, the Panel preview and
 * the public archive page cannot drift apart.
 *
 * @var \Kirby\Cms\Page $edition
 */
use Newsletter\Design as D;
?>
<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="x-apple-disable-message-reformatting">
	<meta name="color-scheme" content="light dark">
	<meta name="supported-color-schemes" content="light dark">
	<title><?= D::esc( site()->title() ) ?> &mdash; <?= D::esc( $edition->title() ) ?></title>
	<!--[if mso]>
	<xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
	<![endif]-->
	<style type="text/css">
		* { text-size-adjust:100%; -ms-text-size-adjust:100%; -webkit-text-size-adjust:100%; }
		html, body { margin:0 !important; padding:0 !important; height:100% !important; width:100% !important; mso-line-height-rule:exactly; }
		table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; }
		img { border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; }
		a { color:<?= D::QUIET ?>; }
		@media only screen and (max-width:620px) {
			.wrap { width:100% !important; max-width:100% !important; }
			.pad { padding-left:22px !important; padding-right:22px !important; }
		}
	</style>
</head>
<body style="margin:0; padding:0; background:<?= D::DESK ?>;">
	<div style="background:<?= D::DESK ?>; padding:32px 12px; font-family:<?= D::SERIF ?>;">
		<span style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;"><?= D::esc( $edition->preheader() ) ?></span>
		<?php snippet( 'emails/newsletter-body', ['edition' => $edition] ) ?>
	</div>
</body>
</html>
