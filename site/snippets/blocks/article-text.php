<?php
/**
 * Paragraph block for text-type articles.
 *
 * @var \Kirby\Cms\Block $block
 */
$lead = $block->lead()->value();
$spacing = $block->tight()->toBool() ? 'mt-14' : 'mt-38';
?>
<div class="article-copy <?= $spacing ?>">
	<?php if( !empty( $lead ) ): ?>
		<p class="article-lead spectral"><?= html( $lead ) ?></p>
	<?php endif ?>
	<p class="spectral"><?= $block->text() ?></p>
</div>
