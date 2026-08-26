<?php
/**
 * The newsletter letter itself: masthead, headline, blocks, footer.
 *
 * Shared by the email send, the Panel preview and the public archive page, so
 * all three are guaranteed to render identically.
 *
 * @var \Kirby\Cms\Page $edition
 */
use Newsletter\Design as D;

$blocks = $edition->blocks()->toBlocks();
$dateLine = $edition->published()->isNotEmpty()
	? mb_strtolower( $edition->published()->toDate( 'F Y' ) )
	: '';
?>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::PAGE ?>" align="center" class="wrap" style="width:<?= D::PAGE ?>px; max-width:<?= D::PAGE ?>px; margin:0 auto; background:<?= D::PAPER ?>; border-collapse:collapse;">

	<?= D::cellOpen( 38 ) ?>
	<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::CONTENT ?>" style="width:<?= D::CONTENT ?>px; border-collapse:collapse;"><tr>
		<td align="left" valign="baseline" style="font-family:<?= D::DISPLAY ?>; font-size:26px; line-height:28px; letter-spacing:-0.01em; color:<?= D::INK ?>; font-weight:300;"><?= D::esc( site()->title() ) ?></td>
		<td align="right" valign="baseline" style="font-family:<?= D::SERIF ?>; font-size:13px; line-height:16px; color:<?= D::FAINT ?>;"><?= D::esc( mb_strtolower( $edition->title() ) ) ?></td>
	</tr></table>
	<?= D::cellClose() ?>

	<?= D::cellOpen( 26 ) ?>
	<?= D::hairline() ?>
	<?= D::cellClose() ?>

	<?= D::cellOpen( 34 ) ?>
	<?php if( $dateLine !== '' ): ?>
		<p style="margin:0 0 10px 0; font-family:<?= D::SERIF ?>; font-style:italic; font-size:17px; line-height:20px; color:<?= D::ACCENT ?>; mso-line-height-rule:exactly;">&mdash; <?= D::esc( $dateLine ) ?></p>
	<?php endif ?>
	<h1 style="margin:0; font-family:<?= D::SERIF ?>; font-weight:500; font-size:30px; line-height:36px; letter-spacing:-0.01em; color:<?= D::INK ?>; mso-line-height-rule:exactly;"><?= D::esc( $edition->headline() ) ?></h1>
	<?= D::cellClose() ?>

	<?php foreach( $blocks as $block ): ?>
		<?php snippet( 'emails/blocks/' . $block->type(), ['block' => $block] ) ?>
	<?php endforeach ?>

	<tr><td class="pad" style="padding:44px <?= D::GUTTER ?>px <?= D::GUTTER ?>px <?= D::GUTTER ?>px;">
		<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="<?= D::CONTENT ?>" style="width:<?= D::CONTENT ?>px; border-collapse:collapse;">
			<tr><td height="1" style="height:1px; line-height:1px; font-size:0; background:<?= D::RULE ?>;">&nbsp;</td></tr>
			<tr><td style="padding-top:20px; font-family:<?= D::SERIF ?>; font-size:13px; line-height:22px; color:<?= D::FAINT ?>;"><?= D::address() ?></td></tr>
			<tr><td style="padding-top:14px; font-family:<?= D::SERIF ?>; font-size:13px; line-height:22px; color:<?= D::FAINT ?>;">
				<?php foreach( D::footerLinks() as $i => $link ): ?>
					<?php if( $i > 0 ): ?>&nbsp;&middot;&nbsp;<?php endif ?>
					<a href="<?= D::esc( $link['href'] ) ?>" style="color:#777777; text-decoration:none; border-bottom:1px solid #dcdcdc;"><?= D::esc( $link['label'] ) ?></a>
				<?php endforeach ?>
			</td></tr>
		</table>
	</td></tr>

</table>
