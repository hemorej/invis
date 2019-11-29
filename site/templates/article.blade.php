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

$pull = true;
if(count($page->images()) == 1 && $page->images()->first()->isPortrait())
	$pull = false;

@endphp

<div class="row medium-space-top">
	<h3><span class="high-contrast">{{ $page->parent()->title() | lower }}</span><a href="{{ $page->url() }}">{{ $headline | lower }}</a></h3>
	<div class="small-12 medium-12 {{ $pull == true ?? 'medium-overflow pull-2' }} columns">
		@kirbytext($page->text())
		@foreach($page->images() as $image)
			<img srcset="{{ $image->srcset([600, 800, 1200]) }}">
		@endforeach
	</div>
	<p class="medium-space-top"></p>

	@if($page->hasPrevListed())
		<p class="left">
			<a href="{{ $page->prev()->url() }}">&laquo; {{ $page->parent()->title() == 'journal' ? 'Previous' : $page->prev()->title() }}</a>
		</p>
	@endif

	@if($page->parent()->title() == 'journal')
		<p class="left"> |<a href="{{ $page->parent()->url() }}?all=1">All posts</a></p>
	@endif
	
	@if($page->hasNextListed())
		<p class="right">
			<a href="{{ $page->next()->url() }}">{{ $page->parent()->title() == 'journal' ? 'Next' : $page->next()->title() }} &raquo;</a>
		</p>
	@endif

@include('partials.footer')