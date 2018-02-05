<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php $articles = $page->children()->visible()->sortBy('publishDate', 'desc'); ?>

<div class="row large-space-top">
    <div class="small-12 medium-12 columns">
        <?php foreach($articles as $article): ?>
        <div class="row">
            <div class="small-12 medium-4 medium-text-right columns">
                <h3><a data-preview="<?= getPreview($article->images()->first()) ?>" class="cover" href="<?= $article->url() ?>"><?= html($article->title()->lower()) ?></a></h3>
            </div>
        </div>
    <?php endforeach ?>
        <div class="preview hide-for-small">
            <img id="cover" src="<?= getPreview($articles->first()->images()->first()) ?>" >
        </div>
    </div>
</div>

<?php snippet('footer', array('noCopyright'=>true)) ?>