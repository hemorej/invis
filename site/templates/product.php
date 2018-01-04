<?php 
$image = thumb($page->images()->first(), array('height' => 600));
$title = $page->title();
if( $page->title() == $page->uid()){ $title = $page->parent()->slug();}

$extraHeaders = array(
    "<meta name='twitter:card' content='photo' />",
    "<meta name='twitter:site' content='@jerome_a_' />",
    "<meta name='twitter:title' content='".$title."' />",
    "<meta name='twitter:image' content='".$image->url()."' />",
    "<meta name='twitter:url' content='".$page->url()."' />"
    );
?>

<?php snippet('header', array('extraHeaders' => $extraHeaders)) ?>
<?php snippet('menu') ?>

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

<div class="row">
    <h3><span class="high-contrast"><?= $page->parent()->title()->lower() ?></span><a href="<?= $page->url() ?>"><?= strtolower($headline) ?></a></h3>
    <div class="small-12 medium-10 columns">   
        <?php 

        echo kirbytext($page->text()) ;
        snippet('interchange', array('images' => $page->images())) ;

        ?>

        <p class="medium-space-top"></p>

        <?php if($page->hasPrev()): ?>
            <span class="left">
                <a href="<?php echo $page->prev()->url() ?>">&laquo; Previous | </a>
            </span>
        <?php endif ?>
        <?php if($page->hasNextVisible()): ?>
            <span class="right">
                <a href="<?php echo $page->next()->url() ?>">Next &raquo;</a>
            </span>
        <?php endif ?>
    </div>
        <section class="medium-2 columns variants">
        <?php $variants = $page->variants()->toStructure();
        if(count($variants) == 0 || (count($variants) == 1 && !inStock($variants->first()))){
            echo 'Out of stock';
        }else{ ?>

        <ul class="inline-list">
        <?php $first = true;
        foreach ($variants as $variant): ?>
            <li <?php ecco($first == true, 'class="active variant"', 'class="variant"') ?>>
                <?php if(inStock($variant)): ?>
                <a href="#" data-option-variant='<?= $variant->name() ?>' data-option-price="<?= $variant->price ?>"><?= $variant->name() ?> &mdash; $<?= $variant->price ?></a>
                <?php endif ?>
            </li>&nbsp;
        <?php $first = false;
        endforeach ?>
        </ul>

        <form id="cart-form" method="post" action="">
            <div class="description">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="uri" value="<?= $page->uri() ?>">
                <input type="hidden" name="variant" value='<?= $variants->first()->name() ?>'>
            </div>

            <div class="action">
                <button id="add-cart" type="submit">Add to cart</button>
            </div>
        </form>
        <?php } ?>
        </section>
           
    </div>  
</div>
<?php snippet('footer') ?>
<?php echo js('assets/js/vendor/cart.js') ?>

<?php
function inStock($variant) {

  if (!is_numeric($variant->stock()->value) and $variant->stock()->value === '') return true;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) <= 0) return false;
  if (is_numeric($variant->stock()->value) and intval($variant->stock()->value) > 0) return intval($variant->stock()->value);

  return false;
}
?>