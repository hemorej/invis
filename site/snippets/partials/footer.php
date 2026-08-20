	<footer class="mt-120 flex items-baseline justify-center flex-wrap spectral f-13 lh-14 ink-light" style="padding-top:8px;gap:26px;" data-r="footer">
		<span class="spectral f-13 lh-14 ink-light footer-copyright"><?= kirbytext($site->copyright()) ?></span>
	</footer>

	<?= $slots->scripts() ?>

	<?php if(isset($ldjson)): ?>
		<script type="application/ld+json">
			<?= json_encode($ldjson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
		</script>
	<?php endif ?>
</main>

</html>
