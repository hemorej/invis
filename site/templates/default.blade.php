@snippet('header')
@snippet('menu')

<div class="row medium-space-top">
	<section class="small-12 medium-10 columns">
	  <article>
	    @kirbytext($page->text())
	  </article>
	</section>
</div>

@snippet('footer')