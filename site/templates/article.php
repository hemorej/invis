<?php 
$image = thumb($page->images()->first(), array('height' => 600));
$title = $page->title();
if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

$extraHeaders = array(
	"<meta name='twitter:card' content='photo' />",
	"<meta name='twitter:site' content='@jerome_a_' />",
	"<meta name='twitter:title' content='".$title."' />",
	"<meta name='twitter:image' content='".$image->url()."' />",
	"<meta name='twitter:url' content='".$page->url()."' />"
	);
?>

<?php snippet('header', array('extraHeaders' => $extraHeaders)) ?>
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

<div class="row">
	<h3><span class="high-contrast"><?= $page->parent()->title()->lower() ?></span><a href="<?= $page->url() ?>"><?= strtolower($headline) ?></a></h3>
	<div class="small-12 medium-12 pull-2 columns">
		<?php 

		echo kirbytext($page->text()) ;
		snippet('interchange', array('images' => $page->images())) ;

		?>
	</div>
	<p class="medium-space-top"></p>

	<?php if($page->hasPrev()): ?>
		<span class="left">
			<a href="<?php echo $page->prev()->url() ?>">&laquo; Previous</a>
		</span>
	<?php endif ?>
	<?php if($page->parent()->title() == 'journal'){ ?>
		<span><a href="<?= $page->parent()->url() . '?archive' ?>">| Archives</a></span>
	<?php } ?>
	<?php if($page->hasNext()): ?>
		<span class="right">
			<a href="<?php echo $page->next()->url() ?>">Next &raquo;</a>
		</span>
	<?php endif ?>

<?php snippet('footer') ?>