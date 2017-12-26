<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<section class="small-12 medium-8 columns">
	  <article>
	    <?php echo kirbytext($page->text()) ?>
	  </article>
	</section>
	<section class="small-12 medium-4 columns">
	  <article>
	    <?php echo kirbytext($page->links()) ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>