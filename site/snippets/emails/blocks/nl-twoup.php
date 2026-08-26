<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;

$files = $block->images()->toFiles();
$height = (int) ( $block->height()->or( 312 )->value() );
$gap = 20;
$colWidth = (int) ( ( D::CONTENT - $gap ) / 2 );
?>
<?= D::cellOpen( 24 ) ?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::CONTENT ?>" style="width:<?= D::CONTENT ?>px; border-collapse:collapse;"><tr>
	<td width="<?= $colWidth ?>" valign="top" style="width:<?= $colWidth ?>px; font-size:0; line-height:0;"><?= D::image( $files->nth( 0 ), $colWidth, $height ) ?></td>
	<td width="<?= $gap ?>" style="width:<?= $gap ?>px; font-size:0; line-height:0;">&nbsp;</td>
	<td width="<?= $colWidth ?>" valign="top" style="width:<?= $colWidth ?>px; font-size:0; line-height:0;"><?= D::image( $files->nth( 1 ), $colWidth, $height ) ?></td>
</tr></table>
<?= D::cellClose() ?>
