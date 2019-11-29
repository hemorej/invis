@php 
	$image = $page->images()->first()->resize(null, 600);
	$title = $page->title();
	if( $page->title() == $page->uid())
		$title = $page->parent()->slug();

	$meta = array('url' => $page->url(), 'image' => $image->url());
@endphp

@include('partials.header', ['meta' => $meta])
@include('partials.menu')

<div class="row medium-space-top">
	<div class="small-12 medium-10 columns">
		<h3><span class="high-contrast">{{ $page->parent()->title() | lower }}</span></h3>
		@kirbytext($page->text())
	</div>
	<p class="medium-space-top"></p>
</div>
<div class="row medium-space-top">
	<div class="small-12 medium-10 columns">
		@if($page->hasPrevListed())
			<p class="left">
				<a href="{{ $page->prev()->url() }}">&laquo; {{ $page->prev()->title() }}</a>|
			</p>
		@endif
		<p class="left"><a href="{{ $page->parent()->url() }}">All posts</a></p>
		@if($page->hasNextListed())
			<p class="right">
				<a href="{{ $page->next()->url() }}">{{ $page->next()->title() }} &raquo;</a>
			</p>
		@endif
	</div>
</div>
@include('partials.footer')