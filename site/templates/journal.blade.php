@include("partials.header")
@include("partials.menu")

@php
    $archive = array();
    $articles = $page->children()->listed()->flip()->paginate(10);
    $journals = $site->page('journal-series')->children()->listed()->flip();
@endphp

<section class="cf mt5-ns mt3 center">
    <nav class="fl w-100 w-40-ns pr3 tr-ns tl relative z-1">
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns">&mdash;&nbsp;journals</span>
        @foreach($journals as $year)
            <a data-preview="{{ getPreview($year->images()->first()) }}" data-title="{{$year->title()}} journal entry" class="cover ttl link black-70 f3 s721-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $year->url() }}">@html($year->title()->lower())</a>
        @endforeach
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns mt4 db">&mdash;&nbsp;serial</span>
        @foreach($articles as $article)
            <a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} series cover" class="cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">{{ archiveDate($article->published()->toString()) }}</a>
        @endforeach
    </nav>
    <div class="fl db-ns w-60-ns tl o-30-s o-100-ns fixed static-ns z-0">
        <img alt="{{$articles->first()->title()}} journal entry" id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
    </div>
</section>

<nav class="mw7 mt4 ph2">
    @if($articles->pagination()->hasPages())
        @if($articles->pagination()->hasPrevPage())
            <p class="fl">
                <a class="f5 f4-m f4-ns link pa1-ns black-60 hover-white hover-bg-gold" href="{{ $articles->pagination()->prevPageURL() }}">&laquo;&nbsp;newer</a>
            </p>
        @endif
                
        @if($articles->pagination()->hasNextPage())
            <p class="fr">
                <a class="f5 f4-m f4-ns link pa1-ns black-60 hover-white hover-bg-gold" href="{{ $articles->pagination()->nextPageURL() }}">older&nbsp;&raquo;</a>
            </p>
        @endif
    @endif
</nav>

<span class="cf"></span>

@extends('partials.footer')
@section('scripts')
    @if(option('env') == 'prod')
        @js('assets/dist/app.min.js')
    @else
        @js('assets/js/app.js')
    @endif
@endsection