<?php

if(isset($_GET['all'])){

    snippet('header');
    snippet('menu');

    $archive = array();
    $articles = $page->children()->visible()->sortBy('publishDate', 'desc')->paginate(80);
    
    foreach($articles as $article){
        $key = date('M/y', strtotime($article->published()->toString()));
        if(array_key_exists($key, $archive)) {
            array_push($archive[$key], $article);
        }
        else{
            $archive[$key] = array($article);
        }
    }
    ?>
    <div class="row">
    <div class="small-12 medium-12 columns">
        <h3><span class="high-contrast"><?= $page->title()->lower() ?></span><a href="<?= $page->url() . '?archive' ?>">archives</a></h3>
    </div>

    <div class="row medium-space-top">
        <div class="medium-12 columns">
            <ul class="inline-list">
        <?php 
        foreach (array_keys($archive) as $key): 
            $value = $archive[$key]; ?>

            <li class="date centre"><?php echo html($key) ?></small></li>
            <?php foreach($value as $link): ?>
                <li>
                    <a class="thumb" style="background-image:url(<?php echo thumb($link->images()->first(), array('height' => 150, 'width' => 150, 'crop' => true))->url(); ?>)" href="<?php echo $link->url(); ?>"></a>
                </li>
        <?php endforeach ?>
    <?php endforeach ?>
            </ul>
        </div>
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
