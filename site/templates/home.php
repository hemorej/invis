<?php $heroImage = getHomeImage()['images'] ?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('preload') ?>
	<?php if($heroImage): ?>
	<link rel="preload" as="image" fetchpriority="high" imagesrcset="<?= html($heroImage->srcset([600, 800, 1200])) ?>" imagesizes="100vw">
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<?php if($heroImage): ?>
<div class="img-loader db w-100">
	<img alt="black and white photograph" class="db w-100" width="<?= $heroImage->width() ?>" height="<?= $heroImage->height() ?>" style="height:auto" sizes="100vw" fetchpriority="high" srcset="<?= html($heroImage->srcset([600, 800, 1200])) ?>">
</div>
<?php endif ?>

<?php snippet('partials/footer') ?>
