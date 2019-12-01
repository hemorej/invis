@include('partials.header')
@include('partials.menu')


<section class="ph2 aspect-ratio aspect-ratio--6x4">
	@foreach($page->images() as $image)
		<img alt="portrait of the photographer" srcset="{{ $image->srcset([600, 800, 1200]) }}">
	@endforeach
</section>

<section class="measure black-70 f4 f3-m f3-ns ph2 lh-copy">
 	@kirbytext($page->text())
</section>

<section class="cc1 cc2-m cc3-l">
	@foreach($page->links()->toStructure() as $item)
		@if(!empty($item->separator()->value))
			<span class="f4 f3-ns black db pa2">{{ $item->separator() }}</span><span class='db mb2'></span>
		@endif
		<a href="{{ $item->url() }}" target='_blank' class='f4 f3-ns pa2 link black-60 hover-white hover-bg-gold' >
			{{ $item->text() }}
		</a>
		<span class='db mb2'></span>
	@endforeach
</section>

<section class="mt5">
	<span class="f4 f3-ns black pa2">contact</span>
	@foreach($page->contact()->toStructure() as $item)
		<a href="{{ e(empty($item->email()->value), $item->url(), 'mailto:'.$item->email()) }}" target='_blank' class='f4 f3-ns pa2 link black-60 hover-white hover-bg-gold di' >
			{{ $item->text() }}
		</a>
	@endforeach
</section>

@include('partials.footer')