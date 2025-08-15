@php
	$mainImageUrl = page()->images()->first()->resize(null, 600)->url();
	$structuredData = [
	  "@context" => "https://schema.org/",
	  "@type" => "Person",
	  "name" => "Jerome Arfouche",
	  "url" => site()->url(),
	  "image" => $mainImageUrl
	];
@endphp

@section('additional-meta-tags')
	<meta property="profile:first_name" content="Jerome">
	<meta property="profile:last_name" content="Arfouche">

    <meta property="og:url" content="{{page()->url()}}">
    <meta property="og:image" content={{ $mainImageUrl }}>
    <meta property="og:description" content="about the invisible cities and jerome arfouche">
	<meta property="og:type" content="profile">
	<meta property="og:title" content="{{ page()->title() }}">
@endsection
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
			<a href="
				@if(empty($item->email()->value))
					{{ $item->link() }}
				@else
					mailto: {{ $item->email() }}
				@endif
				" target='_blank' class='f4 f3-ns pa1-l link black-60 hover-white hover-bg-gold di' >
				{{ $item->text() }}
			</a>
		@endforeach
	</section>
@endif

@extends('partials.footer', ['ldjson' => $structuredData]);