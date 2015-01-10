<?php 

$image = thumb($page->images()->first(), array('height' => 600));
$extraHeaders = array(
	"<meta name='twitter:card' content='photo' />",
	"<meta name='twitter:site' content='@jerome_a_' />",
	"<meta name='twitter:title' content='".$page->title()."' />",
	"<meta name='twitter:image' content='".$image->url()."' />",
	"<meta name='twitter:url' content='".$page->url()."' />"
	);
?>

<?php snippet('header', array('extraHeaders' => $extraHeaders)) ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
	<?php 
	if( $page->title() != $page->uid()){
		$headline = "_".$page->title()->lower();
	}
	else if("" !== $page->published()->toString()){
		$headline = date('F d, Y', strtotime($page->published()->toString()));
	}
	?>

</div>
<div class="row">
	<div class="small-12 medium-2 columns">
		<h3><a href="<?php echo $page->url() ?>"><?php echo $headline ?></a></h3>
	</div>

	<div class="small-12 medium-10 columns">	
			<?php 

			echo kirbytext($page->text()) ;
			foreach($page->images() as $image): 
				snippet('interchange', array('image' => $image, 'alt' => $image->alt(), 'caption' => $image->caption()));
			endforeach ?>

			<p class="medium-space-top"></p>
					
				<?php if($page->hasPrev()): ?>
					<span class="left">
						<a href="<?php echo $page->prev()->url() ?>">&laquo; Previous</a>
					</span>
				<?php endif ?>
				
				<?php if($page->hasNext()): ?>
					<span class="right">
						<a href="<?php echo $page->next()->url() ?>">Next &raquo;</a>
					</span>
				<?php endif ?>

		</div>
	</div>	
</div>
<?php snippet('footer') ?>