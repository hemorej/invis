<?php $article = $page->children()->visible()->flip()->first(); ?>
<?php echo go($article->url()) ?>