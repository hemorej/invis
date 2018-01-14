<body>
  <div class="row large-space-top">
	  <header class="show-for-small-only small-8 columns">
	    <h1><a href="<?php echo url() ?>"> <?php echo html($site->title()) ?> </a></h1>
	  </header>
  
		<div class="contain-to-grid sticky">
		  <nav class="top-bar" data-topbar role="navigation">
		  	<ul class="inline-list title-area medium-offset-4 large-offset-4">
		  	  <li class="name"></li>
		  	  <li class="toggle-topbar"><a href="#"><span>menu</span></a></li>
		  	</ul>

		  	<section class="top-bar-section">
	  		  <ul>
	  		  	<li class="hide-for-small"><h3><a href="<?= url() ?>"> <?= html($site->title()) ?> </a></h3></li>
			    <?php foreach($pages->visible() AS $p): ?>
			    <li><h3><a<?php ecco($p->isOpen() && $page->title() != 'cart', ' class="active"') ?> href="<?php echo $p->url() ?>"><?= $p->title()->lower() ?></a></h3></li>
			    <?php endforeach ?>
			    <?php if(s::get('txn')): ?>
			    	<?php $items = page(s::get('txn'))->products()->yaml();
			    		if(count($items) > 0): ?>
			    	<li><h3><a <?php ecco($page->title() == 'cart', ' class="active"') ?>href="/prints/cart">cart</a></h3></li>
			    	<?php endif ?>
			    <?php endif ?>
			  </ul>
			</section>
			</nav>
		</div>
	</div>