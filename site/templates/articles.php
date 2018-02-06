<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php $articles = $page->children()->visible()->sortBy('publishDate', 'desc'); ?>

<div class="row large-space-top"></div>
<?php foreach($articles as $article): ?>
<div class="row">
    <div class="small-5 medium-4 text-right columns">
        <h3><a data-preview="<?= getPreview($article->images()->first()) ?>" class="cover" href="<?= $article->url() ?>"><?= html($article->title()->lower()) ?></a></h3>
    </div>
</div>
<?php endforeach ?>
<div class="preview">
    <img id="cover" src="<?= getPreview($articles->first()->images()->first()) ?>" >
</div>

<?php snippet('footer', array('noCopyright'=>true)) ?>