@if(@get('all'))
    @include("partials.header")
    @include("partials.menu")

    @php
        $archive = array();
        $articles = $page->children()->listed()->sortBy('publishDate', 'desc')->paginate(12);
    @endphp

    <section class="cf mt5-ns mt3 center">
        <nav class="fl w-100 w-30-ns pr3 tr-ns tl">
            @foreach($articles as $article)
                <a data-preview="{{ getPreview($article->images()->first()) }}" class="cover ttl link black-70 f3 s721-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">{{ archiveDate($article->published()->toString()) }}</a>
            @endforeach
        </nav>
        <div class="fl db-ns w-70-ns tl o-30-s o-100-ns fixed static-ns">
            <img id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
        </div>
    </section>

    @if($articles->pagination()->hasPages())
        @if($articles->pagination()->hasNextPage())
            <p class="fl">
                <a class="f5 f4-m f4-ns link pa1-ns black-60 hover-white hover-bg-gold" href="{{ $articles->pagination()->nextPageURL() }}">&laquo;&nbsp;older</a>
            </p>
        @endif
                
        @if($articles->pagination()->hasPrevPage())
            <p class="fr">
                <a class="f5 f4-m f4-ns link pa1-ns black-60 hover-white hover-bg-gold" href="{{ $articles->pagination()->prevPageURL() }}">newer&nbsp;&raquo;</a>
            </p>
        @endif
    @endif

    <span class="cf"></span>

    @extends('partials.footer')
    @section('scripts')
        @if(option('env') == 'prod')
            @js('assets/dist/app.min.js')
        @else
            @js('assets/js/app.js')
        @endif
    @endsection
@else
    {{ @go($page->children()->last()->url()) }}
@endif