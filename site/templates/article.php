<?php 
$image = thumb($page->images()->first(), array('height' => 600));
$title = $page->title();
if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

$meta = array('url' => $page->url(), 'image' => $image->url());
?>

<?php snippet('header', array('meta' => $meta)) ?>
<?php snippet('menu') ?>

<?php 
if($page->parent()->title() != 'journal'){
	$headline = $page->title()->lower();
}else{
	$published = $page->published()->toString();
	if(!empty($published)){
	   if(strpos($published, ',') != false){
			$headline = $published ;
		}else{
			$headline = date('F d, Y', strtotime($published));
		}
	}else if( $page->title() != $page->uid()){
		$headline = "_".$page->title()->lower();
	}
}

?>
<?php $pull = true;
if(count($page->images()) == 1 && $page->images()->first()->isPortrait())
	$pull = false;
?>

<div class="row medium-space-top">
	<h3><span class="high-contrast"><?= $page->parent()->title()->lower() ?></span><a href="<?= $page->url() ?>"><?= strtolower($headline) ?></a></h3>
	<div class="small-12 medium-12 <?= ecco($pull == true, 'medium-overflow pull-2') ?> columns">
		<?php 

		echo kirbytext($page->text()) ;
		snippet('interchange', array('images' => $page->images()));

		?>
	</div>
	<p class="medium-space-top"></p>

	<?php if($page->hasPrevVisible()): ?>
		<p class="left">
			<a href="<?= $page->prev()->url() ?>">&laquo; <?= ecco($page->parent()->title() == 'journal', 'Previous', $page->prev()->title()) ?></a>
		</p>
	<?php endif ?>
	<?php if($page->parent()->title() == 'journal'){ ?>
		<p class="left"> |<a href="<?= $page->parent()->url() . '?all=1' ?>">All posts</a></p>
	<?php } ?>
	<?php if($page->hasNextVisible()): ?>
		<p class="right">
			<a href="<?= $page->next()->url() ?>"><?= ecco($page->parent()->title() == 'journal', 'Next', $page->next()->title()) ?> &raquo;</a>
		</p>
	<?php endif ?>

<?php snippet('footer') ?>