@if(!isset($noCopyright))
	<footer class="silver f7 f6-ns ml3 ml4-ns mt4">
		@kirbytext(@html($site->copyright()))
	</footer>
@endif

@js('assets/js/min.js')
@include('partials.ga')
</body>

</html>