<?php
	$editions = page()->children()->listed()->flip()->paginate(10);
	$coverImage = $editions->first() ? $editions->first()->thumbnail()->toFile() : null;
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('preload') ?>
	<?php if($coverImage): ?>
	<link rel="preload" as="image" fetchpriority="high" href="<?= html(getPreview($coverImage)) ?>">
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<?php if($editions->count() === 0): ?>
	<div class="mb-34">
		<span class="spectral f-23 fw5 ink-dark ttl">
			<a class="lnk-nav spectral f-19 ttl" href="https://invis.ddev.site/newsletter/editions">Newsletter archive</a>
		</span>
	</div>
	<p class="spectral f-18 lh-17 ink-copy">No editions published yet.</p>
<?php else: ?>
	<div class="flex items-center flex-wrap gap-52">
		<div class="flex-1 min-w-300 list-pair" style="text-align:right;">
			<span class="gold-lbl mb-22">— letters</span>
			<?php foreach($editions as $edition): ?>
				<?php $preview = $edition->thumbnail()->toFile() ?: $edition->images()->first() ?>
				<a data-preview="<?= html($preview ? getPreview($preview) : '') ?>" data-title="<?= html($edition->title()) ?>" class="cover lnk-content spectral f-18 lh-174 ttl db" style="text-align:right;" href="<?= html($edition->url()) ?>">
					<?= html($edition->published()->toDate('F j, Y')) ?> — <?= html($edition->title()) ?>
				</a>
			<?php endforeach ?>
			<?php if($editions->pagination()->hasPages() && $editions->pagination()->hasNextPage()): ?>
				<a class="lnk-muted spectral f-18 db ttl" style="margin-top:28px;text-align:right;" href="<?= html($editions->pagination()->nextPageURL()) ?>">older&nbsp;»</a>
			<?php endif ?>
		</div>
		<?php if($coverImage): ?>
		<div class="flex-1 min-w-300 journal-cover">
			<div class="img-loader">
				<img alt="<?= html($editions->first()->title()) ?>" id="cover" class="db w-100 cover-photo" width="<?= $coverImage->width() ?>" height="<?= $coverImage->height() ?>" fetchpriority="high" src="<?= html(getPreview($coverImage)) ?>">
			</div>
		</div>
		<?php endif ?>
	</div>
<?php endif ?>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
	<?php if(option('env') == 'prod'): ?>
		<?= js('assets/dist/app.min.js') ?>
	<?php else: ?>
		<?= js('assets/js/app.js') ?>
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
