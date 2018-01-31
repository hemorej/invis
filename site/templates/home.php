<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row small-space-top">
	<section class="small-12 medium-12 medium-overflow pull-2 columns">
	  <article>
        <?php
        $images = page('projects/portfolio')->files()->filter(function($image) {
        	return $image->isLandscape();
      	});        
        
        $page = array('images' => $images->shuffle()->first()) ;
        snippet('interchange', array('images' => $page)) ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>
