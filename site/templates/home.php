<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row small-space-top">
	<section class="small-12 medium-12 medium-overflow pull-2 columns">
	  <article>
        <?php
        $image = $pages->invisible()->find('portfolio')->files()->shuffle()->first();
        $page = array('images' => $image) ;
        snippet('interchange', array('images' => $page)) ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>
