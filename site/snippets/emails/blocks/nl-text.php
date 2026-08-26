<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;

$lead = $block->lead()->value();
$padTop = $block->tight()->toBool() ? 14 : 24;
?>
<?= D::cellOpen( $padTop ) ?>
<?php if( !empty( $lead ) ): ?>
	<p style="margin:0 0 6px 0; font-family:<?= D::SERIF ?>; font-size:19px; line-height:26px; color:<?= D::INK ?>; mso-line-height-rule:exactly;"><?= D::esc( $lead ) ?></p>
<?php endif ?>
<p style="margin:0; font-family:<?= D::SERIF ?>; font-size:17px; line-height:30px; color:<?= D::BODY ?>; mso-line-height-rule:exactly; text-wrap:pretty;"><?= D::inlineHtml( $block->text()->value() ) ?></p>
<?= D::cellClose() ?>
