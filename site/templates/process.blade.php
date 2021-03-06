@php 
	$image = $page->images()->first()->resize(null, 600);
	$title = $page->title();
	if( $page->title() == $page->uid())
		$title = $page->parent()->slug();

	$meta = array('url' => $page->url(), 'image' => $image->url());
@endphp

@include('partials.header', ['meta' => $meta])
@include('partials.menu')

<section class="measure black-70 f4 f3-m f3-ns ph2 lh-copy">
	<span class="f4 f3-ns black-70 db ttl">{{ $page->parent()->title() }}</span>
		@kirbytext($page->text())
	<span class='db mb2'></span>
</section>

<nav class="mw7 mt4 ph2">
	@if($page->hasPrevListed())
		<p class="fl">
			<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold ttl" href="{{ $page->prev()->url() }}">&laquo; {{ $page->prev()->title() }}</a>
		</p>
	@endif
	<p class="fl">{{ e($page->hasPrevListed(), '&nbsp;&nbsp;&nbsp;')}}<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="/journal">all posts</a></p>
	@if($page->hasNextListed())
		<p class="fr">
			<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold ttl" href="{{ $page->next()->url() }}">{{ $page->next()->title()}} &raquo;</a>
		</p>
	@endif
</nav>
<span class="cf"></span>

@include('partials.footer')