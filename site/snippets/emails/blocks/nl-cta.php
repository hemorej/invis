<?php
/**
 * @var \Kirby\Cms\Block $block
 */
use Newsletter\Design as D;

$href = D::link( $block->href()->value() );
$label = $block->label()->value();
$isButton = $block->variant()->value() === 'button';
?>
<?php if( $isButton ): ?>
	<?= D::cellOpen( 24 ) ?>
	<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;"><tr>
		<td align="center" bgcolor="<?= D::PAPER ?>" width="248" style="width:248px; border:2px solid <?= D::ACCENT ?>; padding:0;">
			<a href="<?= D::esc( $href ) ?>" style="display:block; padding:13px 26px; font-family:<?= D::SERIF ?>; font-size:16px; line-height:20px; color:<?= D::INK ?>; text-decoration:none; white-space:nowrap;"><?= D::esc( $label ) ?></a>
		</td>
	</tr></table>
	<?= D::cellClose() ?>
<?php else: ?>
	<?= D::cellOpen( 18 ) ?>
	<p style="margin:0;"><a href="<?= D::esc( $href ) ?>" style="font-family:<?= D::SERIF ?>; font-size:16px; line-height:24px; color:<?= D::INK ?>; text-decoration:none; border-bottom:2px solid <?= D::ACCENT ?>;"><?= D::esc( $label ) ?> &raquo;</a></p>
	<?= D::cellClose() ?>
<?php endif ?>
