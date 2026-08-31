<?php
/**
 * Image block for text-type articles, with an optional caption.
 *
 * @var \Kirby\Cms\Block $block
 */
$file = $block->image()->toFile();

if( !$file )
	return;

$caption = $block->caption()->value();
$alt     = !empty( $caption ) ? $caption : $file->alt()->or( '' )->value();
?>
<figure class="article-figure mt-48 mb-48">
	<?php if( $file->isPortrait() ): ?>
		<div class="mw6 db center">
	<?php elseif( $file->isSquare() ): ?>
		<div class="mw6 db center">
	<?php else: ?>
		<div class="aspect-ratio aspect-ratio--6x4">
	<?php endif ?>
		<div class="img-loader" style="aspect-ratio:<?= $file->width() ?>/<?= $file->height() ?>">
			<img alt="<?= html( $alt ) ?>" class="lazy" data-srcset="<?= html( $file->srcset( $file->isPortrait() ? 'vertical' : 'default' ) ) ?>">
		</div>
	</div>
	<?php if( !empty( $caption ) ): ?>
		<figcaption class="article-caption spectral"><?= html( $caption ) ?></figcaption>
	<?php endif ?>
</figure>
