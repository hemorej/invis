<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
	  <?php snippet('interchange', array('images' => $page->images())); ?>
	  <p class="medium-space-top">&nbsp;</p>
	  <?= kirbytext($page->text()) ?>
	</section>
</div>

<div class="row medium-space-top distribute">
	<section class="small-12 medium-12 columns">
		<?= kirbytext($page->links()); ?>
	</section>
</div>

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
	  <article>
	    <?= kirbytext($page->contact()) ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>