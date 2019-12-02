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
	<span class="f5 f4-m f3-ns black-70 db">{{ $page->parent()->title() | lower }}&nbsp;<a class="f5 f4-m f3-ns link black-60 hover-white hover-bg-gold pa2" href="{{ $page->url() }}">{{ $headline | lower }}</a></span>
		@kirbytext($page->text())
		<span class='db mb3'></span>
		@php $skip = false @endphp
		@foreach($page->images() as $current)
			@php if($skip == true){ $skip = false; continue;} @endphp
			@php $next = $page->images()->nth($loop->index + 1); @endphp
			@if($current->isPortrait() && $next->isPortrait())
				<div class="mw7">
					<section class="fl w-100 w-50-ns pr1">
						<img alt="{{$headline}}" srcset="{{ $current->srcset('vertical') }}">
					</section>
					<section class="fl w-100 w-50-ns pl1">
						<img alt="{{$headline}}" srcset="{{ $next->srcset('vertical') }}">
					</section>
				</div>
				@php $skip = true @endphp
			@else
				<section class="{{e($current->isPortrait(), 'mw6 dib', 'aspect-ratio aspect-ratio--6x4')}}">
					@if($current->isPortrait())
						<img alt="{{$headline}}" srcset="{{ $current->srcset('vertical') }}">
					@else
						<img alt="{{$headline}}" srcset="{{ $current->srcset() }}">
					@endif
				</section>
			@endif
			<span class='cf db mb3'></span>
		@endforeach
</section>

<nav class="mt4 ph2">
	@if($page->hasPrevListed())
		<p class="fl">
			<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="{{ $page->prev()->url() }}">&laquo; {{ $page->parent()->title() == 'journal' ? 'previous' : $page->prev()->title() }}</a>
		</p>
	@endif
	@if($page->parent()->title() == 'journal')
		<p class="fl">&nbsp;&vert;&nbsp;<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="{{ $page->parent()->url() }}?all=1">all posts</a></p>
	@endif
	@if($page->hasNextListed())
		<p class="fr">
			<a class="f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="{{ $page->next()->url() }}">{{ $page->parent()->title() == 'journal' ? 'next' : $page->next()->title() }} &raquo;</a>
		</p>
	@endif
</nav>
<span class="cf"></span>

@include('partials.footer')