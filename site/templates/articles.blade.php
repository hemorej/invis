@include('partials.header')
@include('partials.menu')

@php
	if($page->title() == 'process'){
		$articles = $page->children()->listed();
	}else{
		$articles = $page->children()->listed()->sortBy('publishDate', 'desc');
	}
@endphp

<section class="cf mt5-ns mt3 center">
	<nav class="fl w-100 w-30-ns pr3 tr-ns tl">
		@foreach($articles as $article)
			<a data-preview="{{ getPreview($article->images()->first()) }}" class="cover ttl link black gray-ns f3 meta-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="{{ $article->url() }}">@html($article->title()->lower())</a>
		@endforeach
	</nav>
	<div class="fl db-ns w-70-ns tl o-30-s o-100-ns fixed static-ns">
		<img id="cover" src="{{ getPreview($articles->first()->images()->first()) }}" >
	</div>
</section>

<div class="db mt4"></div>

@include('partials.footer')

<script>
	var classname = document.getElementsByClassName("cover");

	for (var i = 0; i < classname.length; i++) {
	    classname[i].addEventListener('mouseover', setPreview, false);
	}

	function setPreview(){
		document.getElementById('cover').setAttribute("src", this.getAttribute('data-preview'))
	}
</script>