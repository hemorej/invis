<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<section class="small-12 medium-10 columns">
	  <article>
	    <?= $page->text()->kirbytext() ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>