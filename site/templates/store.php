<?php
	$books = $page->children()->filterBy('type', 'zine')->listed()->flip();
	$prints = $page->children()->filterBy('type', 'print')->listed()->flip();
	$coverImage = $prints->first()->images()->first();
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('preload') ?>
	<link rel="preload" as="image" fetchpriority="high" href="<?= html(getPreview($coverImage)) ?>">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<div class="flex items-center flex-wrap gap-52">
	<div class="flex-1 min-w-300">
		<img alt="<?= html($prints->first()->title()) ?> product preview" id="cover" class="db w-100 cover-photo" width="<?= $coverImage->width() ?>" height="<?= $coverImage->height() ?>" fetchpriority="high" src="<?= html(getPreview($coverImage)) ?>">
	</div>
	<div class="flex flex-wrap min-w-300 gap-40" style="flex:1.35;">
		<div style="flex:1.3;min-width:200px;">
			<span class="gold-lbl mb-22">— prints</span>
			<?php foreach($prints as $article): ?>
				<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> product preview" class="cover lnk-content spectral f-18 lh-174 ttl" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
			<?php endforeach ?>
		</div>
		<div style="flex:1;min-width:175px;">
			<span class="gold-lbl mb-22">— books</span>
			<?php foreach($books as $article): ?>
				<a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> product preview" class="cover lnk-content spectral f-18 lh-174 ttl" href="<?= html($article->url()) ?>"><?= html($article->title()->lower()) ?></a>
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
