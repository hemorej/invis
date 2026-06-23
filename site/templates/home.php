<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<article class="aspect-ratio aspect-ratio--6x4">
	<?php foreach(getHomeImage() as $image): ?>
		<img alt="black and white photograph" srcset="<?= html($image->srcset([600, 800, 1200])) ?>">
	<?php endforeach ?>
</article>

<?php snippet('partials/footer') ?>
