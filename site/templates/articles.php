<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php	
	  $articles = $page->children()
			->sortBy('publishDate', 'desc')
	                 ->paginate(10);
?>

    <div class="row medium-space-top">
        <div class="medium-11 columns">

        <ul class="inline-list">
	<?php foreach($articles as $article): ?>
			<?php $pub = date('M/y', strtotime($article->published()->toString())); ?>
        	<li class="date"><?= $article->title()->lower() ?></li>
    		<?php foreach($article->images()->slice(0, rand(2,4)) as $image): ?>
        	<li>
            	<a class="thumb" style="background-image:url(<?php echo thumb($image, array('height' => 150, 'width' => 150, 'crop' => true))->url(); ?>)"  href="<?php echo $article->url() ?>"></a>
			</li>
        	<?php endforeach ?>
	<?php endforeach ?>
        </ul>
</div>
</div>
	<?php if($articles->pagination()->hasPages()): ?>
	<div class="row">		
		<div class="small-12 small-centered medium-12 columns">
			<?php if($articles->pagination()->hasPrevPage()): ?>
				<span class="left">
					<a href="<?= $articles->pagination()->prevPageURL() ?>">&laquo; Previous</a>
				</span>
			<?php endif ?>
			
			<?php if($articles->pagination()->hasNextPage()): ?>
				<span class="right">
					<a href="<?= $articles->pagination()->nextPageURL() ?>">Next  &raquo;</a>
				</span>
			<?php endif ?>	
		</div>
	</div>
	<?php endif ?>


<?php snippet('footer') ?>
