@if(@get('all'))
    @snippet('header')
    @snippet('menu')

    @php
        $archive = array();
        $articles = $page->children()->listed()->sortBy('publishDate', 'desc')->paginate(12);
    @endphp

    <div class="row large-space-top"></div>
    @foreach($articles as $article)
        <div class="row article-list">
            <div class="small-6 medium-4 medium-text-right columns">
                <h3><a data-preview="{{ getPreview($article->images()->first()) }}" class="cover" href="{{$article->url()}}">{{ archiveDate($article->published()->toString()) }}</a></h3>
            </div>
        </div>
    @endforeach
    <div class="preview">
        <img id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
    </div>

    @if($articles->pagination()->hasPages())
        <div class="row medium-space-top">       
            <div class="small-12 small-centered medium-12 columns">
                @if($articles->pagination()->hasNextPage())
                    <span class="left">
                        <a href="{{ $articles->pagination()->nextPageURL() }}">&laquo; older</a>
                    </span>
                @endif
                
                @if($articles->pagination()->hasPrevPage())
                    <span class="right">
                        <a href="{{ $articles->pagination()->prevPageURL() }}">newer  &raquo;</a>
                    </span>
                @endif
            </div>
        </div>
    @endif

    @snippet('footer')
@else
    {{ @go($page->children()->last()->url()) }}
@endif