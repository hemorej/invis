<main class="mw8 center bg-lightest-gray">
	<nav class="mt5-l pv4 ttl s721-cd-light">
		<a class="link tracked-tight f2 f2-m f1-ns black-70 hover-black-70 di-ns db pv2-l ph2 hover-bg-gold mr3" href="<?= url() ?>" title="<?= html($site->title()) ?>"><?= html($site->title()) ?></a>
		<?php $loopIdx = 0; foreach($pages->listed() as $p): ?>
			<?php
				$class = 'link gray f4 f3-ns dib pv0 ml0-ns pa1-l hover-bg-gold hover-white';
				if($p->isOpen() && $site->page()->title() != 'cart')
					$class .= ' bb b--gold bw2';

				if($loopIdx === 0){
					$class .= ' pa2';
				}else{
					$class .= ' pa1';
				}
			?>
			<a class="<?= html($class) ?>" href="<?= html($p->url()) ?>" title="<?= html($p->title()) ?>"><?= html($p->title()) ?></a>
		<?php $loopIdx++; endforeach ?>

		<?php if(!empty(kirby()->session()->get('txn'))): ?>
			<?php
				$class = 'pa1 link gray f4 f3-ns dib pv0 ml0-ns pa2-l hover-bg-gold hover-white';
				if($site->page()->title() == 'cart')
					$class .= ' bb b--gold bw2';
			?>
			<a class="<?= html($class) ?>" href="/prints/cart" title="cart">cart</a>
		<?php endif ?>
	</nav>
