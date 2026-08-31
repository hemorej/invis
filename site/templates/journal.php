<?php
	$articles = page()->children()->listed()->flip()->paginate(10);
	$journals = site()->page('journal-series')->children()->listed()->flip();
	$coverImage = $articles->first()->previewImage();
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('preload') ?>
	<link rel="preload" as="image" fetchpriority="high" href="<?= html(getPreview($coverImage)) ?>">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<div class="flex items-center flex-wrap gap-52">
	<div class="flex flex-wrap min-w-300 gap-40 list-pair" style="flex:1.35;justify-content:flex-end;">
		<div style="flex:1.2;min-width:185px;text-align:right;">
			<span class="gold-lbl mb-22">— journals</span>
			<?php foreach($journals as $year): ?>
				<a data-preview="<?= html(getPreview($year->images()->first())) ?>" data-title="<?= html($year->title()) ?> journal entry" class="cover lnk-content spectral f-18 lh-174 ttl" style="text-align:right;" href="<?= html($year->url()) ?>"><?= html($year->title()->lower()) ?></a>
			<?php endforeach ?>
		</div>
		<div style="flex:1.3;min-width:200px;text-align:right;">
			<span class="gold-lbl mb-22">— serial</span>
			<?php foreach($articles as $article): ?>
				<a data-preview="<?= html(getPreview($article->previewImage())) ?>" data-title="<?= html($article->title()) ?> series cover" class="cover lnk-content spectral f-18 lh-174 ttl" style="text-align:right;" href="<?= html($article->url()) ?>"><?php if($article->isTextArticle()): ?>&#x275D;&nbsp;<?php endif ?><?= html(archiveDate($article->published()->toString())) ?></a>
			<?php endforeach ?>
			<?php if($articles->pagination()->hasPrevPage()): ?>
				<a rel="prev" class="lnk-muted spectral f-18 db ttl" style="margin-top:28px;text-align:right;" href="<?= html($articles->pagination()->prevPageURL()) ?>">«&nbsp;newer</a>
			<?php endif ?>
			<?php if($articles->pagination()->hasNextPage()): ?>
				<a rel="next" class="lnk-muted spectral f-18 db ttl" style="margin-top:28px;text-align:right;" href="<?= html($articles->pagination()->nextPageURL()) ?>">older&nbsp;»</a>
			<?php endif ?>
		</div>
	</div>
	<div class="flex-1 min-w-300 journal-cover">
		<div class="img-loader">
			<img alt="<?= html($articles->first()->title()) ?> journal entry" id="cover" class="db w-100 cover-photo" width="<?= $coverImage->width() ?>" height="<?= $coverImage->height() ?>" fetchpriority="high" src="<?= html(getPreview($coverImage)) ?>">
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
