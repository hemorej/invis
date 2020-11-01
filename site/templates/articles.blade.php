@include('partials.header')
@include('partials.menu')

@php
	$series = $site->page('projects')->children()->listed()->sortBy('published', 'desc');
	$travels = $site->page('travels')->children()->listed()->sortBy('published', 'desc');
@endphp

<section class="cf mt5-ns mt3">
	<div class="fl db-ns w-60-ns tl o-30-s o-100-ns fixed static-ns z-0">
		<img alt="{{$travels->first()->title()}} series cover" id="cover" src="{{ getPreview($travels->first()->images()->first()) }}" >
	</div>
	<nav class="fl w-100 w-40-ns pl3 tl relative z-1">
		<span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns">&mdash;&nbsp;travels</span>
		@foreach($travels as $article)
			<a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} series cover" class="mw5 cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">@html($article->title()->lower())</a>
		@endforeach
		<span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns mt4 db">&mdash;&nbsp;series</span>
		@foreach($series as $article)
			<a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} series cover" class="mw5 cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">@html($article->title()->lower())</a>
		@endforeach
	</nav>
</section>

<div class="db mt4"></div>

@extends('partials.footer')
@section('scripts')
	@if(option('env') == 'prod')
		@js('assets/dist/app.min.js')
	@else
		@js('assets/js/app.js')
	@endif
@endsection