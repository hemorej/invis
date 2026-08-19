<main class="mw-1040 center" style="padding:clamp(132px, 26vh, 300px) 36px 64px">
	<header class="flex items-baseline flex-wrap mb-96" style="gap:28px;">
		<a class="s721-cd-light tracked-tight ttl f-logo fw3 ink-dark no-underline" href="<?= url() ?>" title="<?= html($site->title()) ?>"><?= html($site->title()) ?></a>
		<nav class="flex flex-wrap ml-auto" style="gap:26px;">
			<?php foreach($pages->listed() as $p): ?>
				<?php
					$isActive = $p->isOpen() && $site->page()->title() != 'cart';
					$cls = 'ttl nav-lnk ' . ($isActive ? 'nav-lnk-on' : 'nav-lnk-off');
				?>
				<a class="<?= html($cls) ?>" href="<?= html($p->url()) ?>"><?= html($p->title()) ?></a>
			<?php endforeach ?>
			<?php if(!empty(kirby()->session()->get('txn'))): ?>
				<?php $cls = 'nav-lnk ' . ($site->page()->title() == 'cart' ? 'nav-lnk-on' : 'nav-lnk-off'); ?>
				<a class="<?= html($cls) ?>" href="/prints/cart">cart</a>
			<?php endif ?>
		</nav>
	</header>
