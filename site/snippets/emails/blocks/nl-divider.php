<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;
?>
<?= D::cellOpen( 38 ) ?>
<?= D::accentRule() ?>
<p style="margin:16px 0 0 0; font-family:<?= D::SERIF ?>; font-style:italic; font-size:17px; line-height:20px; color:<?= D::ACCENT ?>; mso-line-height-rule:exactly;">&mdash; <?= D::esc( $block->label() ) ?></p>
<?= D::cellClose() ?>
