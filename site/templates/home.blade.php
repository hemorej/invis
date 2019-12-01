@include('partials.header')
@include('partials.menu')

<article class="aspect-ratio aspect-ratio--6x4">
	@foreach(getHomeImage() as $image)
		<img alt="black and white photograph" srcset="{{ $image->srcset([600, 800, 1200]) }}">
	@endforeach
</article>

@include('partials.footer')
