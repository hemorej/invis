	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4">
		@kirbytext(@html(kirby()->site()->copyright()))
	</footer>

	@include('partials.ga')
	@yield('scripts')
</body>

</html>