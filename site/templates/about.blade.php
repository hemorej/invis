@include('partials.header')
@include('partials.menu')


<section class="ml3 ml4-ns w-80">
	@foreach($page->images() as $image)
		<img srcset="{{ $image->srcset([600, 800, 1200]) }}">
	@endforeach
</section>
<section class="measure black-70 ma3 ma4-ns f3-ns f4-m f4">
  @kirbytext($page->text())
</section>

<section class="cc1 cc2-m cc3-l ml3 ml4-ns">
	@foreach($page->links()->toStructure() as $item)
		@if(!empty($item->separator()->value))
			<span class="f4 f3-ns black db">{{ $item->separator() }}</span><span class='db mb2'></span>
		@endif
		<a href="{{ $item->url() }}" target='_blank' class='f4 f3-ns link black-60 hover-white hover-bg-gold' >
			{{ $item->text() }}
		</a>
		<span class='db mb2'></span>
	@endforeach
</section>

<section class="ml3 ml4-ns mt5">
	<span class="f4 f3-ns black">contact</span>
	@foreach($page->contact()->toStructure() as $item)
		<a href="{{ e(empty($item->email()->value), $item->url(), 'mailto:'.$item->email()) }}" target='_blank' class='f4 f3-ns link black-60 hover-white pa2 hover-bg-gold di' >
			{{ $item->text() }}
		</a>
	@endforeach
</section>

@include('partials.footer')