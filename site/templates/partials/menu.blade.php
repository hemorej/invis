<body>
	<nav class="pa3 pa4-ns">
		<a class="ttl f3 f1-ns lh-title link black-70 hover-black-70 b dib-l db m5 mr3-ns pa2 hover-bg-gold meta-cd" href="{{ url() }}" title="{{ html($site->title()) }}">{{ html($site->title()) }}</a>
		@foreach($pages->listed() as $p)
			<a class="{{ e($p->isOpen(), 'bb b--gold bw2') }} ttl link gray f5 f4-m f3-ns lh-copy dib mr3-ns pa1 pa2-ns hover-bg-gold hover-white meta-cd" href="{{ $p->url() }}" title="{{ $p->title()->lower() }}">{{ $p->title()->lower() }}</a>
		@endforeach
	</nav>