<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row small-space-top">
	<section class="small-12 medium-12 medium-overflow pull-2 columns">
	  <article>
    	<?php foreach(getHomeImage() as $image): ?>
			<img srcset="<?= $image->srcset([600, 800, 1200]) ?>">
		<?php endforeach ?>
	  </article>
	</section>
</div>

<?php snippet('footer') ?>
