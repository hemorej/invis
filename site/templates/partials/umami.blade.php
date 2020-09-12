@php $loc = location(); @endphp

@if($loc->location->is_eu == true)
	@css('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.css')
	@if(@option('env') == 'prod')
		@js('assets/dist/consent.min.js', ['id' => 'consent', 'data-loc' => $loc->country_code])
	@else
		@js('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.js')
		@js('assets/js/consent.js', ['id' => 'consent', 'data-loc' => $loc->country_code])
	@endif
@endif

<script async defer data-website-id="@option('umami_code')" src="https://umami.the-invisible-cities.com/umami.js"></script>