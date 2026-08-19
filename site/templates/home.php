<?php $heroImage = getHomeImage()['images'] ?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('preload') ?>
	<link rel="preload" as="image" fetchpriority="high" imagesrcset="<?= html($heroImage->srcset([600, 800, 1200])) ?>" imagesizes="100vw">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<img alt="black and white photograph" class="db w-100" width="<?= $heroImage->width() ?>" height="<?= $heroImage->height() ?>" style="height:auto" sizes="100vw" fetchpriority="high" srcset="<?= html($heroImage->srcset([600, 800, 1200])) ?>">

<?php snippet('partials/footer') ?>
