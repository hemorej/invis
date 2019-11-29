@include('partials.header')
@include('partials.menu')

<div class="row small-space-top">
	<section class="small-12 medium-12 medium-overflow pull-2 columns">
	  <article>
    	@foreach(getHomeImage() as $image)
			<img srcset="{{ $image->srcset([600, 800, 1200]) }}">
		@endforeach
	  </article>
	</section>
</div>

@include('partials.footer')
