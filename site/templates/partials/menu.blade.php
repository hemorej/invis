<body class="mw7 center bg-lightest-gray">
	<nav class="mt5-l pv4 ttl meta-cd">
		<a class="link tracked-tight f2 f2-m f1-ns black-70 hover-black-70 di-l db pv2-l ph2 hover-bg-gold" href="{{ url() }}" title="{{ html($site->title()) }}">{{ html($site->title()) }}</a>
		@foreach($pages->listed() as $p)
			<a class="{{ e($p->isOpen() && $site->page()->title() != 'cart', 'bb b--gold bw2') }} {{ e($loop->first, 'pa2', 'pa1') }} link gray f5 f4-m f3-ns dib pv0 ml0-ns pa1-l hover-bg-gold hover-white" href="{{ $p->url() }}" title="{{ $p->title() }}">{{ $p->title() }}</a>
		@endforeach

		@if(!empty(kirby()->session()->get('txn')))
			<a class="{{ e($site->page()->title() == 'cart', 'bb b--gold bw2') }} pa1 link gray f5 f4-m f3-ns dib pv0 ml0-ns pa1-l hover-bg-gold hover-white" href="/prints/cart" title="cart">cart</a>
		@endif
	</nav>