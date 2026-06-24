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
	<section class="black-70 f4 f3-m f3-ns ph2 pv4 mt4">
        <?= kirbytext($site->terms()) ?>
    </section>
<?php else: ?>
	<section class="mw7 ph2">
		<?php foreach($page->images() as $image): ?>
			<img alt="portrait of the photographer" srcset="<?= html($image->srcset([600, 800, 1200])) ?>">
		<?php endforeach ?>
	</section>

	<section class="measure black-70 f4 f3-m f3-ns ph2 lh-copy">
	 	<?= kirbytext($page->text()) ?>
	</section>

	<section class="cc1 cc2-m cc3-l">
		<?php foreach($page->links()->toStructure() as $item): ?>
			<?php if(!empty($item->separator()->value)): ?>
				<span class="f4 f3-ns black db pa2"><?= html($item->separator()) ?></span><span class='db mb2'></span>
			<?php endif ?>
			<a href="<?= html($item->link()) ?>" target='_blank' class='f4 f3-ns pa1-l pa2 link black-60 hover-white hover-bg-gold' >
				<?= html($item->text()) ?>
			</a>
			<span class='db mb2'></span>
		<?php endforeach ?>
	</section>

	<section class="mt5">
		<span class="f4 f3-ns black pa2">contact</span>
		<?php foreach($page->contact()->toStructure() as $item): ?>
			<a href="
				<?php if(empty($item->email()->value)): ?>
					<?= html($item->link()) ?>
				<?php else: ?>
					mailto: <?= html($item->email()) ?>
				<?php endif ?>
				" target='_blank' class='f4 f3-ns pa1-l link black-60 hover-white hover-bg-gold di umami--click--<?= html($item->text()) ?>' >
				<?= html($item->text()) ?>
			</a>
		<?php endforeach ?>
	</section>
<?php endif ?>

<?php snippet('partials/footer', ['ldjson' => $structuredData]) ?>
