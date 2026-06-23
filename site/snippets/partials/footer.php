	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4 tc">
		<?= kirbytext($site->copyright()) ?>
	</footer>

	<?php snippet('partials/consent') ?>
	<?php snippet('partials/umami') ?>
	<?= $slots->scripts() ?>

	<?php if(isset($ldjson)): ?>
		<script type="application/ld+json">
			<?= json_encode($ldjson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
		</script>
	<?php endif ?>
</main>

</html>
