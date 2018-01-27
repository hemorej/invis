<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php $articles = $page->children()->visible()->sortBy('publishDate', 'desc'); ?>

<div class="row medium-space-top">
    <div class="small-12 medium-12 columns">
        <ul class="inline-list">
            <?php foreach($articles as $article): ?>
                <?php if(strtolower($page->title()->value()) == 'prints'): ?>
                    <li class="date"><?= $article->title()->lower() ?></li>
                <?php else: ?>
                    <li class="date centre title"><a href="<?= $article->url() ?>"><?= $article->title()->lower() ?></a></li>
                <?php endif ?>
                <?php foreach($article->images()->slice(0, rand(2,4)) as $image): ?>
                <li>
                    <a class="thumb" style="background-image:url(<?= thumb($image, array('height' => 150, 'width' => 150, 'crop' => true))->url(); ?>)"  href="<?= $article->url() ?>"></a>
                </li>
                <?php endforeach ?>
            <?php endforeach ?>
        </ul>
    </div>
</div>

<?php snippet('footer') ?>