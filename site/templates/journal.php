<?php

if(get('all')){

    snippet('header');
    snippet('menu');

    $archive = array();
    $articles = $page->children()->listed()->sortBy('publishDate', 'desc')->paginate(12); ?>

    <div class="row large-space-top"></div>
    <?php foreach($articles as $article): ?>
    <div class="row article-list">
        <div class="small-6 medium-4 medium-text-right columns">
            <h3><a data-preview="<?= getPreview($article->images()->first()) ?>" class="cover" href="<?= $article->url() ?>"><?= archiveDate($article->published()->toString())  ?></a></h3>
        </div>
    </div>
    <?php endforeach ?>
    <div class="preview">
        <img id="cover" src="<?= getPreview($articles->first()->images()->first()) ?>" >
    </div>

    <?php if($articles->pagination()->hasPages()): ?>
    <div class="row medium-space-top">       
        <div class="small-12 small-centered medium-12 columns">
            <?php if($articles->pagination()->hasNextPage()): ?>
                <span class="left">
                    <a href="<?= $articles->pagination()->nextPageURL() ?>">&laquo; older</a>
                </span>
            <?php endif ?>
            
            <?php if($articles->pagination()->hasPrevPage()): ?>
                <span class="right">
                    <a href="<?= $articles->pagination()->prevPageURL() ?>">newer  &raquo;</a>
                </span>
            <?php endif ?>  
        </div>
    </div>
    <?php endif ?>

    <?php snippet('footer') ?>

<?php }else{
    $article = $page->children()->last();
    echo go($article->url());
}
?>