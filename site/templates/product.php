<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick-theme.css"/>

<?php 
$image = thumb($page->images()->first(), array('height' => 600));
$title = $page->title();
if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

$meta = array('url' => $page->url(), 'image' => $image->url());
?>

<?php snippet('header', array('meta' => $meta)) ?>
<?php snippet('menu') ?>

<noscript>
<div class="alert-box row" style="display:block">
    <div class="medium-12 columns">
      <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</div>
</noscript>

<?php 
if($page->parent()->title() != 'journal'){
    $headline = $page->title()->lower();
}else{
    $published = $page->published()->toString();
    if(!empty($published)){
       if(strpos($published, ',') != false){
            $headline = $published ;
        }else{
            $headline = date('F d, Y', strtotime($published));
        }
    }else if( $page->title() != $page->uid()){
        $headline = "_".$page->title()->lower();
    }
}

?>

<div class="row medium-space-top">
    <h3><span class="high-contrast"><?= $page->parent()->title()->lower() ?></span><a href="<?= $page->url() ?>"><?= strtolower($headline) ?></a></h3>
    <div class="small-12 medium-8 columns">
        <div class="slick">
            <?php foreach($page->images() as $image): ?>
                <div><img src="<?= $image->url() ?>"></div>
            <?php endforeach ?>
        </div>
    </div>
        <section class="small-12 medium-4 columns variants">
        <?php $variants = $page->variants()->toStructure();
        $stock = 0;
        foreach($variants as $variant){
            $stock += $variant->stock()->value();
        }

        if(count($variants) == 0 || $stock == 0):
            echo 'Out of stock';
        else: ?>

        <ul class="inline-list">
        <?php $first = true;
        $activeSku = 0;
        foreach ($variants as $variant): ?>
            <?php if(inStock($variant)): ?>
                <li <?php ecco($first == true, 'class="active variant"', 'class="variant"') ?>>
                    <a href="#" data-option-variant='<?= $variant->sku() ?>' data-option-price="<?= $variant->price ?>"><?= $variant->name() ?> &mdash; $<?= $variant->price ?></a>
                </li>&nbsp;
            <?php if($first == true)
                    $activeSku = $variant->sku();
                $first = false; ?>
            <?php endif; 
        endforeach ?>
        </ul>

        <form id="cart-form" method="post" action="">
            <div class="description">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="uri" value="<?= $page->uri() ?>">
                <input type="hidden" name="variant" value='<?= $activeSku ?>'>
            </div>

            <div class="action">
                <button id="add-cart" type="submit">add to cart</button>
            </div>
        </form>
        <?= kirbytext($page->description()); ?>
        <?php endif ?>
        </section>
    </div>
    <div class="row medium-space-top">
        <div class="small-12 medium-12 columns">
        <?php if($page->hasPrevVisible()): ?>
            <span class="left">
                <a href="<?= $page->prev()->url() ?>">&laquo; <?= $page->prev()->title() ?></a>
            </span>
        <?php endif ?>
        <?php if($page->hasNextVisible()): ?>
            <span class="right">
                <a href="<?= $page->next()->url() ?>"><?= $page->next()->title() ?> &raquo;</a>
            </span>
        <?php endif ?>
        </div>
    </div>  
</div>
<?php snippet('footer') ?>
<script type="text/javascript" src="//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick.min.js"></script>
<?php if(c::get('env') == 'prod'): ?>
    <?= js('assets/js/vendor/cart.min.js') ?>
<?php else: ?>
    <?= js('assets/js/vendor/cart.js') ?>
<? endif ?>