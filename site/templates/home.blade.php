@include('partials.header')
@include('partials.menu')

<article class="ml3 ml4-ns w-70">
	@foreach(getHomeImage() as $image)
		<img srcset="{{ $image->srcset([600, 800, 1200]) }}">
	@endforeach
</article>

@include('partials.footer')
