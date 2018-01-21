<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php if($state == 'complete'): ?>
	congratulations ! new order
<?php else:
	go('/prints');
endif ?>

<?php snippet('footer') ?>