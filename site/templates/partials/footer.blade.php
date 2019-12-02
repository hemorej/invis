@if(!isset($noCopyright))
	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4">
		@kirbytext(@html($site->copyright()))
	</footer>
@endif

@include('partials.ga')
</body>

</html>