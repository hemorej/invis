<?php 
$loc = location();
if($loc !== false): ?>
	<?= css('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css') ?>
	<?= js('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js') ?>
	<?= js('assets/js/vendor/consent.js', ['id' => 'consent', 'data-loc' => $loc, 'data-ga' => env('GA_CODE')]) ?>
<?php else: ?>
	<?php if(!empty(env('GA_CODE'))): ?>
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		  ga('create', '<?= env('GA_CODE') ?>', 'auto');
		  ga('send', 'pageview');

		</script>
	<?php endif ?>
<?php endif ?>