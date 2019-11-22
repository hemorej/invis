<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php if($page->title() == 'process'):
	$articles = $page->children()->listed();
else:
	$articles = $page->children()->listed()->sortBy('publishDate', 'desc');
endif ?>

<div class="row large-space-top"></div>
<?php foreach($articles as $article): ?>
<div class="row article-list">
    <div class="small-6 medium-4 medium-text-right columns">
        <h3><a data-preview="<?= getPreview($article->images()->first()) ?>" class="cover" href="<?= $article->url() ?>"><?= html($article->title()->lower()) ?></a></h3>
    </div>
</div>
<?php endforeach ?>
<div class="preview">
    <img id="cover" src="<?= getPreview($articles->first()->images()->first()) ?>" >
</div>

<?php snippet('footer', array('noCopyright'=>true)) ?>