<?php 
$image = thumb($page->images()->first(), array('height' => 600));
$title = $page->title();
if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

$meta = array('url' => $page->url(), 'image' => $image->url());
?>

<?php snippet('header', array('meta' => $meta)) ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<div class="small-12 medium-10 columns">
		<h3><span class="high-contrast"><?= $page->parent()->title()->lower() ?></span></h3>
		<?= kirbytext($page->text()) ?>
	</div>
	<p class="medium-space-top"></p>
</div>
<div class="row medium-space-top">
	<div class="small-12 medium-10 columns">
	<?php if($page->hasPrevVisible()): ?>
		<p class="left">
			<a href="<?= $page->prev()->url() ?>">&laquo; <?= $page->prev()->title() ?></a>
		</p>
	<?php endif ?>
	<p class="left"> |<a href="<?= $page->parent()->url() ?>"> All posts</a></p>
	<?php if($page->hasNextVisible()): ?>
		<p class="right">
			<a href="<?= $page->next()->url() ?>"><?= $page->next()->title() ?> &raquo;</a>
		</p>
	<?php endif ?>
	</div>
</div>
<?php snippet('footer') ?>