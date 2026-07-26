	<footer class="mt-120 tc spectral f-13 lh-14 ink-light" style="padding-top:8px;">
		<?= kirbytext($site->copyright()) ?>
	</footer>

	<?= $slots->scripts() ?>

	<?php if(isset($ldjson)): ?>
		<script type="application/ld+json">
			<?= json_encode($ldjson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
		</script>
	<?php endif ?>
</main>

</html>
