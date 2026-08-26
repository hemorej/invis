<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;

$product = $block->product()->toPage();

$title = $block->title()->or( $product ? $product->title() : '' )->value();
$file = $block->image()->toFile();
if( empty( $file ) && $product )
	$file = $product->images()->first();
?>
<?= D::cellOpen( 24 ) ?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::CONTENT ?>" style="width:<?= D::CONTENT ?>px; border-collapse:collapse;"><tr>
	<td width="180" valign="top" style="width:180px; font-size:0; line-height:0;"><?= D::image( $file, 180, 120, (string) $title ) ?></td>
	<td width="24" style="width:24px; font-size:0; line-height:0;">&nbsp;</td>
	<td width="316" valign="top" style="width:316px;">
		<p style="margin:0 0 8px 0; font-family:<?= D::SERIF ?>; font-size:19px; line-height:24px; color:<?= D::INK ?>; mso-line-height-rule:exactly;"><?= D::esc( $title ) ?></p>
		<?php if( $block->meta()->isNotEmpty() ): ?>
			<p style="margin:0 0 6px 0; font-family:<?= D::SERIF ?>; font-size:15px; line-height:26px; color:<?= D::QUIET ?>; mso-line-height-rule:exactly;"><?= D::esc( $block->meta() ) ?></p>
		<?php endif ?>
		<?php if( $block->price()->isNotEmpty() ): ?>
			<p style="margin:0; font-family:<?= D::SERIF ?>; font-style:italic; font-size:15px; line-height:26px; color:<?= D::FAINT ?>;"><?= D::esc( $block->price() ) ?></p>
		<?php endif ?>
	</td>
</tr></table>
<?= D::cellClose() ?>
