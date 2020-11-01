@include('partials.header')
@include('partials.menu')


@if(get('terms'))
	<section class="black-70 f4 f3-m f3-ns ph2 pv4 mt4">
        @kirbytext(@html(kirby()->site()->terms()))
    </section>
@else
	<section class="mw7 ph2">
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
			<a href="{{ $item->link() }}" target='_blank' class='f4 f3-ns pa1-l pa2 link black-60 hover-white hover-bg-gold' >
				{{ $item->text() }}
			</a>
			<span class='db mb2'></span>
		@endforeach
	</section>

	<section class="mt5">
		<span class="f4 f3-ns black pa2">contact</span>
		@foreach($page->contact()->toStructure() as $item)
			<a href="{{ e(empty($item->email()->value), $item->link(), 'mailto:'.$item->email()) }}" target='_blank' class='f4 f3-ns pa1-l link black-60 hover-white hover-bg-gold di umami--click--{{$item->text()}}' >
				{{ $item->text() }}
			</a>
		@endforeach
	</section>
@endif

@include('partials.footer')