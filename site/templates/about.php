<?php
	$mainImageUrl = page()->images()->first()->resize(null, 600)->url();
	$structuredData = [
	  "@context" => "https://schema.org/",
	  "@type" => "Person",
	  "name" => "Jerome Arfouche",
	  "url" => site()->url(),
	  "image" => $mainImageUrl
	];
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('meta') ?>
	<meta property="profile:first_name" content="Jerome">
	<meta property="profile:last_name" content="Arfouche">
	<meta property="og:url" content="<?= html(page()->url()) ?>">
	<meta property="og:image" content=<?= html($mainImageUrl) ?>>
	<meta property="og:description" content="about the invisible cities and jerome arfouche">
	<meta property="og:type" content="profile">
	<meta property="og:title" content="<?= html(page()->title()) ?>">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<?php if(get('terms')): ?>
	<section class="spectral f-18 lh-17 ink-copy">
        <?= kirbytext($site->terms()) ?>
    </section>
<?php else: ?>
	<section>
		<?php foreach($page->images() as $image): ?>
			<img alt="portrait of the photographer" class="db w-100 mw-520 mb-54" srcset="<?= html($image->srcset([600, 800, 1200])) ?>">
		<?php endforeach ?>
	</section>

	<section class="col-2 mw-840 spectral f-18 lh-17 ink-copy">
		<?= kirbytext($page->text()) ?>
	</section>

	<div class="col-4 mt-84">
		<?php foreach($page->links()->toStructure() as $item): ?>
			<?php if(!empty($item->separator()->value)): ?>
				<span class="spectral fw5 f-19 ink-dark db" style="margin-bottom:16px;margin-top:16px;break-inside:avoid;"><?= html($item->separator()) ?></span>
			<?php endif ?>
			<a href="<?= html($item->link()) ?>" target="_blank" class="lnk-muted spectral f-18 lh-195 db umami--click--<?= html($item->text()) ?>">
				<?= html($item->text()) ?>
			</a>
		<?php endforeach ?>
	</div>

	<div class="flex items-baseline flex-wrap mt-64 gap-28">
		<span class="spectral fw5 f-19 ink-dark">contact</span>
		<?php foreach($page->contact()->toStructure() as $item): ?>
			<a href="<?php if(empty($item->email()->value)): ?><?= html($item->link()) ?><?php else: ?>mailto:<?= html($item->email()) ?><?php endif ?>"
				target="_blank"
				class="lnk-muted spectral f-18 umami--click--<?= html($item->text()) ?>">
				<?= html($item->text()) ?>
			</a>
		<?php endforeach ?>
	</div>
<?php endif ?>

<?php snippet('partials/footer', ['ldjson' => $structuredData]) ?>
