<div class="row">
	<div class="contain-to-grid sticky">
	  <nav class="top-bar" data-topbar role="navigation">
	  	<ul class="inline-list title-area medium-offset-4 large-offset-4">
	  	  <li class="name"></li>
	  	  <li class="toggle-topbar"><a href="#"><span>Menu</span></a></li>
	  	</ul>

	  	<section class="top-bar-section">
  		  <ul>
  		  	<li class="hide-for-small"><h3><a href="<?php echo url() ?>"> <?php echo html($site->title()) ?> </a></h3></li>
		    <?php foreach($pages->visible() AS $p): ?>
		    <li><h3><a<?php echo ($p->isOpen()) ? ' class="active"' : '' ?> href="<?php echo $p->url() ?>"><?php echo $p->title()->lower() ?></a></h3></li>
		    <?php endforeach ?>
		  </ul>
		</section>
		</nav>
	</div>
</div>