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
	<?php
		$linkGroups = [];
		$currentLabel = null;
		foreach($page->links()->toStructure() as $item) {
			if(!empty($item->separator()->value)) {
				$currentLabel = $item->separator()->value;
				$linkGroups[$currentLabel] = [];
			}
			if($currentLabel !== null) {
				$linkGroups[$currentLabel][] = $item;
			}
		}
		$groupLabels = array_keys($linkGroups);
	?>

	<div class="flex items-start flex-wrap" style="gap:56px;">
		<?php foreach($page->images() as $image): ?>
			<img alt="portrait of the photographer" class="db" style="flex:0 0 360px;width:360px;max-width:100%;height:auto" width="<?= $image->width() ?>" height="<?= $image->height() ?>" sizes="(min-width: 360px) 360px, 100vw" srcset="<?= html($image->srcset([600, 800, 1200])) ?>">
		<?php endforeach ?>
		<div class="spectral f-18 lh-17 ink-copy" style="flex:1;min-width:320px;max-width:56ch">
			<?= kirbytext($page->text()) ?>
		</div>
	</div>

	<div class="flex flex-wrap items-start mt-84 list-pair" style="gap:44px;">
		<?php if(isset($groupLabels[0])): ?>
			<div style="min-width:200px;">
				<span class="gold-lbl mb-22">— <?= html($groupLabels[0]) ?></span>
				<?php foreach($linkGroups[$groupLabels[0]] as $item): ?>
					<a href="<?= html($item->link()) ?>" target="_blank" class="lnk-muted spectral f-18 lh-195 db">
						<?= html($item->text()) ?>
					</a>
				<?php endforeach ?>
			</div>
		<?php endif ?>
		<?php if(isset($groupLabels[1])): ?>
			<div class="flex-1" style="min-width:300px;">
				<span class="gold-lbl mb-22">— <?= html($groupLabels[1]) ?></span>
				<div style="columns:150px 3;column-gap:44px;">
					<?php foreach($linkGroups[$groupLabels[1]] as $item): ?>
						<a href="<?= html($item->link()) ?>" target="_blank" class="lnk-muted spectral f-18 lh-195 db">
							<?= html($item->text()) ?>
						</a>
					<?php endforeach ?>
				</div>
			</div>
		<?php endif ?>
	</div>

	<div class="flex items-baseline flex-wrap mt-64 gap-28">
		<span class="gold-lbl" style="white-space:nowrap;flex:0 0 auto">— contact</span>
		<?php foreach($page->contact()->toStructure() as $item): ?>
			<?php $isInternal = empty($item->email()->value) && str_starts_with($item->link()->value(), '/') ?>
			<a href="<?php if(empty($item->email()->value)): ?><?= html($item->link()) ?><?php else: ?>mailto:<?= html($item->email()) ?><?php endif ?>"
				<?php if(!$isInternal): ?>target="_blank"<?php endif ?>
				class="lnk-muted spectral f-18">
				<?= html($item->text()) ?>
			</a>
		<?php endforeach ?>
	</div>
<?php endif ?>

<?php snippet('partials/footer', ['ldjson' => $structuredData]) ?>
