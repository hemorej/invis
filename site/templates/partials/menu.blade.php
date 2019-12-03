<body class="mw7 center bg-lightest-gray">
	<nav class="mt5-l pv4">
		<a class="ttl tracked-tight f2 f2-m f1-ns lh-title link black-70 hover-black-70 di-l db pv2-ns pa2 hover-bg-gold meta-cd" href="{{ url() }}" title="{{ html($site->title()) }}">{{ html($site->title()) }}</a>
		@foreach($pages->listed() as $p)
			<a class="{{ e($p->isOpen(), 'bb b--gold bw2') }} ttl link gray f5 f4-m f3-ns lh-copy dib pv0 pa2 hover-bg-gold hover-white meta-cd" href="{{ $p->url() }}" title="{{ $p->title()->lower() }}">{{ $p->title()->lower() }}</a>
		@endforeach
	</nav>