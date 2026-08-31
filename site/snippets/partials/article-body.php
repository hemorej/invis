<?php
/**
 * Body of a text-type article: the lead/preview image followed by the
 * paragraph / section / image blocks. Structure is deliberately minimal —
 * sections with optional photographs, no shop links.
 *
 * @var \Kirby\Cms\Page $article
 * @var \Kirby\Cms\File|null $previewImage
 * @var string $headline
 */
$previewImage = $previewImage ?? $article->previewImage();
$alt = html( $headline ?? $article->title() );
?>

<?php if( $previewImage ): ?>
	<section class="aspect-ratio aspect-ratio--6x4">
		<div class="img-loader" style="aspect-ratio:<?= $previewImage->width() ?>/<?= $previewImage->height() ?>">
			<img alt="<?= $alt ?>" class="lazy" data-srcset="<?= html( $previewImage->srcset() ) ?>">
		</div>
	</section>
	<span class="cf db mb3"></span>
<?php endif ?>

<div class="article-body mw7">
	<?php foreach( $article->body()->toBlocks() as $block ): ?>
		<?php snippet( 'blocks/' . $block->type(), ['block' => $block] ) ?>
	<?php endforeach ?>
</div>
