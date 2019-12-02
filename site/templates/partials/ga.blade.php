@php $loc = location(); @endphp

@if($loc->location->is_eu == true)
	@css('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css')
	@js('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js')
	@js('assets/js/prod/consent.min.js', ['id' => 'consent', 'data-loc' => $loc, 'data-ga' => option('ga_code')])
@else
	@if(!empty(@option('ga_code')))
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		  ga('create', '<?= option('ga_code') ?>', 'auto');
		  ga('send', 'pageview');

		</script>
	@endif
@endif