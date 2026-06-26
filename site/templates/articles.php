<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<?php
	$series = $site->page('projects')->children()->listed()->sortBy('published', 'desc');
	$travels = $site->page('travels')->children()->listed()->sortBy('published', 'desc');
?>

<div class="flex items-start flex-wrap gap-52">
	<div class="flex-1 min-w-300">
		<img alt="<?= html($travels->first()->title()) ?> series cover" id="cover" class="db w-100" src="<?= html(getPreview($travels->first()->images()->first())) ?>">
	</div>
	<div class="flex flex-wrap min-w-300 gap-40" style="flex:1.35;">
		<div style="flex:1.3;min-width:180px;">
			<span class="gold-lbl mb-22">— travels</span>
			<?php foreach($travels as $article): ?>
				<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> series cover" class="cover lnk-content spectral f-18 lh-174 ttl" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
			<?php endforeach ?>
		</div>
		<div style="flex:1;min-width:150px;">
			<span class="gold-lbl mb-22">— series</span>
			<?php foreach($series as $article): ?>
				<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> series cover" class="cover lnk-content spectral f-18 lh-174 ttl" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
			<?php endforeach ?>
		</div>
	</div>
</div>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
	<?php if(option('env') == 'prod'): ?>
		<?= js('assets/dist/app.min.js') ?>
	<?php else: ?>
		<?= js('assets/js/app.js') ?>
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
