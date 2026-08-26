<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;

$file = $block->image()->toFile();
$height = (int) ( $block->height()->or( 347 )->value() );
$caption = $block->caption()->value();
?>
<?= D::cellOpen( 30 ) ?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::CONTENT ?>" style="width:<?= D::CONTENT ?>px; border-collapse:collapse;">
	<tr><td style="font-size:0; line-height:0;"><?= D::image( $file, D::CONTENT, $height, (string) $caption ) ?></td></tr>
	<?php if( !empty( $caption ) ): ?>
		<tr><td style="padding-top:10px; font-family:<?= D::SERIF ?>; font-style:italic; font-size:13px; line-height:20px; color:<?= D::FAINT ?>;"><?= D::esc( $caption ) ?></td></tr>
	<?php endif ?>
</table>
<?= D::cellClose() ?>
