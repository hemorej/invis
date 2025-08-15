	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4 tc">
		@kirbytext(@html(kirby()->site()->copyright()))
	</footer>

	@include('partials.consent')
	@yield('scripts')

	@isset($ldjson)
		<script type="application/ld+json">
			<?= json_encode($ldjson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
		</script>
	@endisset
</main>

</html>