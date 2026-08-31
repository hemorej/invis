<?php
/**
 * Section divider for text-type articles: a gold stub + hairline, then an
 * italic gold label.
 *
 * @var \Kirby\Cms\Block $block
 */
?>
<div class="article-section mt-64 mb-26">
	<span class="article-rule"></span>
	<span class="gold-lbl">&mdash; <?= html( $block->label() ) ?></span>
</div>
