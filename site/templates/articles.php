<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<?php
	$series = $site->page('projects')->children()->listed()->sortBy('published', 'desc');
	$travels = $site->page('travels')->children()->listed()->sortBy('published', 'desc');
?>

<section class="cf mt5-ns mt3">
	<div class="fl db-ns w-60-ns tl o-30-s o-100-ns fixed static-ns z-0">
		<img alt="<?= html($travels->first()->title()) ?> series cover" id="cover" src="<?= html(getPreview($travels->first()->images()->first())) ?>" >
	</div>
	<nav class="fl w-100 w-40-ns pl3 tl relative z-1">
		<span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns">&mdash;&nbsp;travels</span>
		<?php foreach($travels as $article): ?>
			<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> series cover" class="mw5 cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
		<?php endforeach ?>
		<span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns mt4 db">&mdash;&nbsp;series</span>
		<?php foreach($series as $article): ?>
			<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> series cover" class="mw5 cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
		<?php endforeach ?>
	</nav>
</section>

<div class="db mt4"></div>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
	<?php if(option('env') == 'prod'): ?>
		<?= js('assets/dist/app.min.js') ?>
	<?php else: ?>
		<?= js('assets/js/app.js') ?>
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
