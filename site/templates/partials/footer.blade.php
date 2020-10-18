	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4 tc">
		@kirbytext(@html(kirby()->site()->copyright()))
	</footer>

	@include('partials.umami')
	@yield('scripts')
</main>

</html>