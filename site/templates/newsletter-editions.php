<?php
	$editions = page()->children()->listed()->flip()->paginate(10);
?>

<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<div class="mb-34">
	<span class="spectral f-23 fw5 ink-dark ttl">Newsletter archive</span>
</div>

<?php if($editions->count() === 0): ?>
	<p class="spectral f-18 lh-17 ink-copy">No editions published yet.</p>
<?php else: ?>
	<div class="list-pair">
		<?php foreach($editions as $edition): ?>
			<a class="lnk-content spectral f-18 lh-174 db mb2" href="<?= html($edition->url()) ?>">
				<?= html($edition->published()->toDate('F j, Y')) ?> — <?= html($edition->title()) ?>
			</a>
		<?php endforeach ?>
	</div>
	<?php if($editions->pagination()->hasPages() && $editions->pagination()->hasNextPage()): ?>
		<a class="lnk-muted spectral f-18 db ttl mt-28" href="<?= html($editions->pagination()->nextPageURL()) ?>">older&nbsp;»</a>
	<?php endif ?>
<?php endif ?>

<?php snippet('partials/footer') ?>
