<?php
	$mainImage = page()->images()->first()->resize(null, 600);
	$title = (page()->title() == page()->uid()) ? page()->parent()->slug() : page()->title();
	$headline = '';
	$published = page()->published()->toString();

	if (page()->parent()->title() != 'journal') {
		$headline = page()->title()->lower();
	} elseif (!empty($published)) {
		$headline = (strpos($published, ',') !== false) ? $published : date('F d, Y', strtotime($published));
	} elseif (page()->title() != page()->uid()) {
		$headline = "_" . page()->title()->lower();
	}

	$structuredData = [
	  "@context" => "https://schema.org",
	  "@type" => "BlogPosting",
	  "mainEntityOfPage" => [
		"@type" => "WebPage",
		"@id" => page()->url()
	  ],
	  "headline" => $headline,
	  "description" => "journal entry",
	  "image" => $mainImage->url(),
	  "author" => [
		"@type" => "Person",
		"name" => "Jerome Arfouche"
	  ],
	  "datePublished" => empty($published) ? getToday() : $published
	];
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('meta') ?>
	<meta property="og:type" content="article">
    <meta property="og:title" content="<?= html($headline) ?>">
    <meta property="og:url" content="<?= html(page()->url()) ?>">
    <meta property="og:image" content="<?= html($mainImage->url()) ?>">

    <meta property="og:description" content="black and white photography">
    <meta property="article:author" content="Jerome Arfouche">
    <meta property="article:section" content="Photography">
    <meta property="article:tag" content="black and white, photography">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<section class="black-70 ph2">
	<span class="f4 f3-ns black-70 db ttl"><?= html(page()->parent()->title()) ?>&nbsp;<a class="f4 f3-ns link black-60 hover-white hover-bg-gold pa1 ttl" href="<?= html(page()->url()) ?>"><?= html($headline) ?></a></span>
		<?= kirbytext(page()->text()) ?>
		<span class='db mb3'></span>
		<?php $skip = false ?>

		<?php $loopIdx = 0; foreach(page()->images() as $current): ?>
			<?php
				if($skip == true){ $skip = false; $loopIdx++; continue;}
				$next = page()->images()->nth($loopIdx + 1);
				$hasNextPortrait = false;
				if($next !== null)
					$hasNextPortrait = $next->isPortrait();
			?>

			<?php if($current->isPortrait() && $hasNextPortrait): ?>
				<div class="mw8 center">
					<section class="fl w-50 pt4-m pb4-m pr4-l pr2">
						<img alt="<?= html($headline) ?>" class="lazy" data-srcset="<?= html($current->srcset('vertical')) ?>">
					</section>
					<section class="fr w-50 pt4-m pb4-m pl4-l pl2">
						<img alt="<?= html($headline) ?>" class="lazy" data-srcset="<?= html($next->srcset('vertical')) ?>">
					</section>
				</div>
				<?php $skip = true ?>
			<?php else: ?>
				<?php if(page()->parent()->title() == 'journal'): ?>
					<section class="aspect-ratio aspect-ratio--6x4">
				<?php else: ?>
					<?php if($current->isPortrait()): ?>
						<section class="mw6 db center pa5">
					<?php elseif($current->isSquare()): ?>
						<section class="mw6 db pv5">
					<?php else: ?>
						<section class="aspect-ratio aspect-ratio--6x4">
					<?php endif ?>
				<?php endif ?>

				<?php if($current->isPortrait() && count(page()->images()) == 1): ?>
					<img style="max-width: 45%" alt="<?= html($headline) ?>" class="lazy" data-srcset="<?= html($current->srcset('vertical')) ?>">
				<?php elseif($current->isPortrait()): ?>
					<img alt="<?= html($headline) ?>" class="lazy" data-srcset="<?= html($current->srcset('vertical')) ?>">
				<?php else: ?>
					<img alt="<?= html($headline) ?>" class="lazy" data-srcset="<?= html($current->srcset()) ?>">
				<?php endif ?>
				</section>
			<?php endif ?>
			<span class='cf db mb3'></span>
		<?php $loopIdx++; endforeach ?>
</section>

<nav class="mt4 ph2">
	<?php
		if(in_array(page()->parent()->title(), ['journal', 'journals'])){
			$articles = page()->siblings()->listed()->flip();
		}else{
			$articles = page()->siblings()->listed()->sortBy('published', 'desc');
		}
	?>
	<?php if(page()->hasPrevListed($articles)): ?>
		<p class="fl">
			<a class="f5 f4-m f4-ns pa1-l link black-60 hover-white hover-bg-gold ttl" href="<?= html(page()->prev($articles)->url()) ?>">&laquo; <?= html(page()->parent()->title() == 'journal' ? 'next' : page()->prev($articles)->title()) ?></a>
		</p>
	<?php endif ?>
	<?php if(page()->parent()->title() == 'journal'): ?>
		<p class="fl">&nbsp;&nbsp;<a class="f5 f4-m f4-ns link pa1-l black-60 hover-white hover-bg-gold" href="<?= html(page()->parent()->url()) ?>">all posts</a></p>
	<?php endif ?>
	<?php if(page()->hasNextListed($articles)): ?>
		<p class="fr">
			<a class="f5 f4-m f4-ns link pa1-l black-60 hover-white hover-bg-gold ttl" href="<?= html(page()->next($articles)->url()) ?>"><?= html(page()->parent()->title() == 'journal' ? 'previous' : page()->next($articles)->title()) ?> &raquo;</a>
		</p>
	<?php endif ?>
</nav>
<span class="cf"></span>

<?php snippet('partials/footer', ['ldjson' => $structuredData], false, true) ?>
<?php slot('scripts') ?>
	<?php if(@option('env') == 'prod'): ?>
	    <?= js('assets/dist/app.min.js') ?>
	<?php else: ?>
		<?= js('https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.3.0/dist/lazyload.min.js') ?>
		<?= js('assets/js/app.js') ?>
	<?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
