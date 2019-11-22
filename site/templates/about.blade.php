@snippet('header')
@snippet('menu')

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
  		@foreach($page->images() as $image):
			<img srcset="<?= $image->srcset([600, 800, 1200]) ?>">
		@endforeach
	  <p class="medium-space-top">&nbsp;</p>
	  @kirbytext($page->text())
	</section>
</div>

<div class="row medium-space-top distribute">
	<section class="small-12 medium-12 columns">
		@kirbytext($page->links())
	</section>
</div>

<div class="row medium-space-top">
	<section class="small-12 medium-12 columns">
	    @kirbytext($page->contact())
	</section>
</div>

@snippet('footer')