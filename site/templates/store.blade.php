@include('partials.header')
@include('partials.menu')

@php
    $books = $page->children()->filterBy('type', 'zine')->listed()->flip();
    $prints = $page->children()->filterBy('type', 'print')->listed()->flip();
@endphp

<section class="cf mt5-ns mt3">
    <div class="fl db-ns w-60-ns tl o-30-s o-100-ns fixed static-ns z-0">
        <img alt="{{ $prints->first()->title()}} product preview" id="cover" src="{{ getPreview($prints->first()->images()->first()) }}" >
    </div>
    <nav class="fl w-100 w-40-ns pl3 tl relative z-1">
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns">&mdash;&nbsp;books</span>
        @foreach($books as $article)
            <a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} product preview" class="cover ttl link black-70 f3 s721-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">@html($article->title()->lower())</a>
        @endforeach
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns mt4 db">&mdash;&nbsp;prints</span>
        @foreach($prints as $article)
            <a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} series cover" class="cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">@html($article->title()->lower())</a>
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