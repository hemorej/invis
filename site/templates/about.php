<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
  		<?php foreach($page->images() as $image): ?>
			<img srcset="<?= $image->srcset([600, 800, 1200]) ?>">
		<?php endforeach ?>
	  <p class="medium-space-top">&nbsp;</p>
	  <?= $page->text()->kirbytext() ?>
	</section>
</div>

<div class="row medium-space-top distribute">
	<section class="small-12 medium-12 columns">
		<?= $page->links()->kirbytext() ?>
	</section>
</div>

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
	    <?= $page->contact()->kirbytext() ?>
	</section>
</div>

<?php snippet('footer') ?>