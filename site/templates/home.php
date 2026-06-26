<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<a href="<?= html($pages->listed()->first()->url()) ?>" class="db">
	<?php foreach(getHomeImage() as $image): ?>
		<img alt="black and white photograph" class="db w-100" srcset="<?= html($image->srcset([600, 800, 1200])) ?>">
	<?php endforeach ?>
</a>

<?php snippet('partials/footer') ?>
