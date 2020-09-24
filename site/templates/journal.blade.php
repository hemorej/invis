@if(@get('all'))
    @include("partials.header")
    @include("partials.menu")

    @php
        $archive = array();
        $articles = $page->children()->listed()->flip()->paginate(12);
    @endphp

    <section class="cf mt5-ns mt3 center">
        <nav class="fl w-100 w-30-ns pr3 tr-ns tl">
            @foreach($articles as $article)
                <a data-preview="{{ getPreview($article->images()->first()) }}" data-title="{{$article->title()}} journal entry" class="cover ttl link black-70 f3 s721-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">{{ archiveDate($article->published()->toString()) }}</a>
            @endforeach
        </nav>
        <div class="fl db-ns w-70-ns tl o-30-s o-100-ns fixed static-ns">
            <img alt="{{$articles->first()->title()}} journal entry" id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
        </div>
    </section>

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

    <span class="cf"></span>

    @extends('partials.footer')
    @section('scripts')
        @if(option('env') == 'prod')
            @js('assets/dist/app.min.js')
        @else
            @js('assets/js/app.js')
        @endif
    @endsection
    <script>
        ready(log);
        function ready(fn) {
          if (document.readyState != 'loading'){
            fn();
          } else {
            document.addEventListener('DOMContentLoaded', fn);
          }
        }
        function log(){
            window.umami('journal-archive');
        }
    </script>
@else
    {{ @go($page->children()->last()->url()) }}
@endif