<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<?php
    $articles = page()->children()->listed()->flip()->paginate(10);
    $journals = site()->page('journal-series')->children()->listed()->flip();
?>

<section class="cf mt5-ns mt3 center">
    <nav class="fl w-100 w-40-ns pr3 tr-ns tl relative z-1">
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns">&mdash;&nbsp;journals</span>
        <?php foreach($journals as $year): ?>
            <a data-preview="<?= html(getPreview($year->images()->first())) ?>" data-title="<?= html($year->title()) ?> journal entry" class="cover ttl link black-70 f3 s721-cd db pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="<?= html($year->url()) ?>"><?= html($year->title()->lower()) ?></a>
        <?php endforeach ?>
        <span class="ttl link gold i f3 s721-cd pl4 pv0-ns pa2-ns mt4 db">&mdash;&nbsp;serial</span>
        <?php foreach($articles as $article): ?>
            <a data-preview="<?= html(getPreview($article->images()->first())) ?>" data-title="<?= html($article->title()) ?> series cover" class="cover ttl link db black-70 f3 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="<?= html($article->url()) ?>"><?= html(archiveDate($article->published()->toString())) ?></a>
        <?php endforeach ?>

        <?php if($articles->pagination()->hasPages()): ?>
        <nav class="fr-ns mt4 mw7 tr-ns tl relative z-1">
            <?php if($articles->pagination()->hasNextPage()): ?>
                <a class="fr link db black-60 f4 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white ml5" href="<?= html($articles->pagination()->nextPageURL()) ?>">older&nbsp;&raquo;</a>
            <?php endif ?>

            <?php if($articles->pagination()->hasPrevPage()): ?>
                <a class="fl link db black-60 f4 s721-cd pl4 pv0-ns pa2-ns hover-bg-gold hover-white" href="<?= html($articles->pagination()->prevPageURL()) ?>">&laquo;&nbsp;newer</a>
            <?php endif ?>
        </nav>
        <?php endif ?>
    </nav>
    <div class="fl db-ns w-60-ns tl o-30-s o-100-ns fixed static-ns z-0">
        <img alt="<?= html($articles->first()->title()) ?> journal entry" id="cover" src="<?= html(getPreview($articles->first()->images()->first())) ?>" >
    </div>
</section>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
    <?php if(option('env') == 'prod'): ?>
        <?= js('assets/dist/app.min.js') ?>
    <?php else: ?>
        <?= js('assets/js/app.js') ?>
    <?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
