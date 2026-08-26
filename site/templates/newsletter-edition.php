<?php
	$articles = page()->siblings()->listed()->flip();
?>

<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<div class="mb-34">
	<span class="spectral f-23 fw5 ink-dark ttl"><?= html(page()->parent()->title()) ?></span>
	<span class="spectral f-23 ink-subtle ttl">&nbsp;<?= html(page()->published()->toDate('F j, Y')) ?> — <?= html(page()->title()) ?></span>
</div>

<div class="article-desc"><?= kirbytext(page()->text()) ?></div>

<nav class="flex items-baseline mt-32" style="justify-content:space-between;">
	<div>
		<?php if(page()->hasPrevListed($articles)): ?>
			<a class="lnk-nav spectral f-19 ttl" href="<?= html(page()->prev($articles)->url()) ?>">&laquo; newer</a>
		<?php endif ?>
	</div>
	<a class="lnk-nav spectral f-19 ttl" href="<?= html(page()->parent()->url()) ?>">all editions</a>
	<div>
		<?php if(page()->hasNextListed($articles)): ?>
			<a class="lnk-nav spectral f-19 ttl" href="<?= html(page()->next($articles)->url()) ?>">older &raquo;</a>
		<?php endif ?>
	</div>
</nav>

<?php snippet('partials/footer') ?>
