	<footer class="mt-120 flex items-baseline justify-center flex-wrap spectral f-13 lh-14 ink-light" style="padding-top:8px;gap:26px;" data-r="footer">
		<span class="spectral f-13 lh-14 ink-light footer-copyright"><?= kirbytext($site->copyright()) ?></span>
		<span class="flex items-baseline spectral f-13 lh-14" style="gap:12px" id="theme-switch">
			<a href="#" class="theme-lnk" data-theme-set="auto">auto</a>
			<span class="ink-rule2">·</span>
			<a href="#" class="theme-lnk" data-theme-set="light">light</a>
			<span class="ink-rule2">·</span>
			<a href="#" class="theme-lnk" data-theme-set="dark">dark</a>
		</span>
	</footer>

	<script>
	(function(){
		var KEY = 'tic-theme';
		var root = document.documentElement;
		var links = document.querySelectorAll('.theme-lnk');

		function markActive(t){
			links.forEach(function(l){
				l.classList.toggle('is-active', l.getAttribute('data-theme-set') === t);
			});
		}

		var saved = 'auto';
		try { saved = localStorage.getItem(KEY) || 'auto'; } catch (e) {}
		markActive(saved);

		links.forEach(function(l){
			l.addEventListener('click', function(e){
				e.preventDefault();
				var t = l.getAttribute('data-theme-set');
				if (t === 'auto') root.removeAttribute('data-theme'); else root.setAttribute('data-theme', t);
				try { localStorage.setItem(KEY, t); } catch (e) {}
				markActive(t);
			});
		});
	})();
	</script>

	<?= $slots->scripts() ?>

	<?php if(isset($ldjson)): ?>
		<script type="application/ld+json">
			<?= json_encode($ldjson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
		</script>
	<?php endif ?>
</main>

</html>
