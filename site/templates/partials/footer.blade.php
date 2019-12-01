@if(!isset($noCopyright))
	<footer class="moon-gray f7 f6-ns ph2 pv4 mt4">
		@kirbytext(@html($site->copyright()))
	</footer>
@endif

@js('assets/js/min.js')
@include('partials.ga')
</body>

</html>