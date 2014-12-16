<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row small-space-top">
	<section class="small-12 medium-10 medium-push-2 columns">
	  <article>
        <?php 
        $image = $pages->find('portfolio')->files()->shuffle()->first();
        snippet('interchange', array('image' => $image)) ?>
	  </article>
	</section>
</div>

<?php snippet('footer', array('noCopyright' => true)) ?>