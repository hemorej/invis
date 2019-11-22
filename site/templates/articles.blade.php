@snippet('header')
@snippet('menu')

@php
	if($page->title() == 'process'){
		$articles = $page->children()->listed();
	}else{
		$articles = $page->children()->listed()->sortBy('publishDate', 'desc');
	}
@endphp

<div class="row large-space-top"></div>
@foreach($articles as $article)
	<div class="row article-list">
	    <div class="small-6 medium-4 medium-text-right columns">
	        <h3><a data-preview="{{ getPreview($article->images()->first()) }}" class="cover" href="{{ $article->url() }}">@html($article->title()->lower())</a></h3>
	    </div>
	</div>
@endforeach

<div class="preview">
    <img id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
</div>

@snippet('footer', array('noCopyright'=>true))