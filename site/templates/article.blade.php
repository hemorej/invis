@php 
	$image = $page->images()->first()->resize(null, 600);
	$title = $page->title();
	if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

	$meta = array('url' => $page->url(), 'image' => $image->url());
@endphp

@include('partials.header', ['meta' => $meta])
@include('partials.menu')

@php 
	if($page->parent()->title() != 'journal'){
		$headline = $page->title()->lower();
	}else{
		$published = $page->published()->toString();
		if(!empty($published)){
		   if(strpos($published, ',') != false){
				$headline = $published ;
			}else{
				$headline = date('F d, Y', strtotime($published));
			}
		}else if( $page->title() != $page->uid()){
			$headline = "_".$page->title()->lower();
		}
	}
@endphp

<section class="black-70 ph2">
	<span class="f4 f3-ns black-70 db ttl">{{ $page->parent()->title() }}&nbsp;<a class="f4 f3-ns link black-60 hover-white hover-bg-gold pa1 ttl" href="{{ $page->url() }}">{{ $headline }}</a></span>
		@kirbytext($page->text())
		<span class='db mb3'></span>
		@php $skip = false @endphp

		@foreach($page->images() as $current)
			@php
				if($skip == true){ $skip = false; continue;}
				$next = $page->images()->nth($loop->index + 1);
				$hasNextPortrait = false;
				if($next !== null)
					$hasNextPortrait = $next->isPortrait();
			@endphp

			@if($current->isPortrait() && $hasNextPortrait)
				<div class="mw8 center">
					<section class="fl w-50 pt4-m pb4-m pr4-l pr2">
						<img alt="{{$headline}}" class="lazy" data-srcset="{{ $current->srcset('vertical') }}">
					</section>
					<section class="fr w-50 pt4-m pb4-m pl4-l pl2">
						<img alt="{{$headline}}" class="lazy" data-srcset="{{ $next->srcset('vertical') }}">
					</section>
				</div>
				@php $skip = true @endphp
			@else
				@if($page->parent()->title() == 'journal')
					<section class="aspect-ratio aspect-ratio--6x4">
				@else
					@if($current->isPortrait())
						<section class="mw6 db center pa5">
					@elseif($current->isSquare())
						<section class="mw6 db pv5">
					@else
						<section class="aspect-ratio aspect-ratio--6x4">
					@endif
				@endif

					@if($current->isPortrait() && count($page->images()) == 1)
						<img style="max-width: 50%" alt="{{$headline}}" class="lazy" data-srcset="{{ $current->srcset('vertical') }}">
					@elseif($current->isPortrait())
						<img alt="{{$headline}}" class="lazy" data-srcset="{{ $current->srcset('vertical') }}">
					@else
						<img alt="{{$headline}}" class="lazy" data-srcset="{{ $current->srcset() }}">
					@endif
				</section>
			@endif
			<span class='cf db mb3'></span>
		@endforeach
</section>

<nav class="mt4 ph2">
	@php
		if(in_array($page->parent()->title(), ['journal', 'journals'])){
			$articles = $page->siblings()->listed()->flip();
		}else{
			$articles = $page->siblings()->listed()->sortBy('published', 'desc');
		}
	@endphp
	@if($page->hasPrevListed($articles))
		<p class="fl">
			<a class="f5 f4-m f4-ns pa1-l link black-60 hover-white hover-bg-gold ttl" href="{{ $page->prev($articles)->url() }}">&laquo; {{ $page->parent()->title() == 'journal' ? 'next' : $page->prev($articles)->title() }}</a>
		</p>
	@endif
	@if($page->parent()->title() == 'journal')
		<p class="fl">&nbsp;&nbsp;<a class="f5 f4-m f4-ns link pa1-l black-60 hover-white hover-bg-gold" href="{{ $page->parent()->url() }}">all posts</a></p>
	@endif
	@if($page->hasNextListed($articles))
		<p class="fr">
			<a class="f5 f4-m f4-ns link pa1-l black-60 hover-white hover-bg-gold ttl" href="{{ $page->next($articles)->url() }}">{{ $page->parent()->title() == 'journal' ? 'previous' : $page->next($articles)->title() }} &raquo;</a>
		</p>
	@endif
</nav>
<span class="cf"></span>

@extends('partials.footer')
@section('scripts')
	@if(@option('env') == 'prod')
	    @js('assets/dist/app.min.js')
	@else
		@js('https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.3.0/dist/lazyload.min.js')
		@js('assets/js/app.js')
	@endif
@endsection