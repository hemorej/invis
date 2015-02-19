<?php

if(isset($_GET['archive'])){

    snippet('header');
    snippet('menu');

    $archive = array();
    $articles = $page->children()->visible()->flip()->paginate(80);
    
    foreach($articles as $article){
        $key = date('M Y', strtotime($article->published()->toString()));
        if(array_key_exists($key, $archive)) {
            array_push($archive[$key], $article);
        }
        else{
            $archive[$key] = array($article);
        }
    }

    foreach (array_keys($archive) as $key): 
        $value = $archive[$key]; ?>

        <div class="row medium-space-top">
            <div class="medium-2 columns">
                <h3><small><?php echo html($key) ?></small></h3>
            </div>
            <div class="small-12 medium-10 columns">
                <ul class="inline-list">
                <?php foreach($value as $link): ?>
                    <li>
                        <a href="<?php echo $link->url(); ?>">
                            <img src="<?php echo thumb($link->images()->first(), array('height' => 100, 'width' => 100, 'crop' => true))->url(); ?>" />
                        </a>
                    </li>
                <?php endforeach ?>
                </ul>
            </div>
        </div>
    <?php endforeach ?>

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
    $article = $page->children()->visible()->flip()->first();
    echo go($article->url());
}
?>