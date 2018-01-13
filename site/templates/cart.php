<?php snippet('header') ?>
<div class="alert-box row">
    <div class="medium-12 columns">
      <h3>Sorry, there's only <span id="stock-error"></span> left in stock</h3>
      <a href="#" class="close">&times;</a>
    </div>
</div>
<?php snippet('menu') ?>

<div class="row medium-space-top">
    <h1><span class="high-contrast">
        <?= $page->title()->html() ?>
    </h1></span>

<?= $page->text()->kirbytext() ?>

<?php if (!s::get('txn') or $txn->products()->toStructure()->count() === 0): ?>
    <section class="small-12 medium-8 columns">
        <article>
            Your cart is empty. Would you like to look at some <a href="./">prints</a>?
        </article>
    </section>

<?php else: ?>
<style>
.loading{display:none;position:fixed;z-index:999;height:2em;width:2em;overflow:show;margin:auto;top:0;left:0;bottom:0;right:0}.loading:before{content:'';display:block;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.3)}.loading:not(:required){font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.loading:not(:required):after{content:'';display:block;font-size:10px;width:.5em;height:.5em;margin-top:-.5em;-webkit-animation:spinner 1500ms infinite linear;-moz-animation:spinner 1500ms infinite linear;-ms-animation:spinner 1500ms infinite linear;-o-animation:spinner 1500ms infinite linear;animation:spinner 1500ms infinite linear;border-radius:.5em;-webkit-box-shadow:rgba(0,0,0,0.75) 1.5em 0 0 0,rgba(0,0,0,0.75) 1.1em 1.1em 0 0,rgba(0,0,0,0.75) 0 1.5em 0 0,rgba(0,0,0,0.75) -1.1em 1.1em 0 0,rgba(0,0,0,0.5) -1.5em 0 0 0,rgba(0,0,0,0.5) -1.1em -1.1em 0 0,rgba(0,0,0,0.75) 0 -1.5em 0 0,rgba(0,0,0,0.75) 1.1em -1.1em 0 0;box-shadow:rgba(0,0,0,0.75) 1.5em 0 0 0,rgba(0,0,0,0.75) 1.1em 1.1em 0 0,rgba(0,0,0,0.75) 0 1.5em 0 0,rgba(0,0,0,0.75) -1.1em 1.1em 0 0,rgba(0,0,0,0.75) -1.5em 0 0 0,rgba(0,0,0,0.75) -1.1em -1.1em 0 0,rgba(0,0,0,0.75) 0 -1.5em 0 0,rgba(0,0,0,0.75) 1.1em -1.1em 0 0}@-webkit-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-moz-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-o-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}
</style>
<div class="loading">Loading&#8230;</div>
<!-- Cart items -->
<div class="row">
    <div class="small-2 medium-2 columns">image</div>
    <div class="small-6 medium-6 columns">description</div>
    <div class="small-2 medium-2 columns text-right">quantity</div>
    <div class="small-2 medium-2 columns"></div>
</div>

<?php $first = true;
    foreach(getItems() as $i => $item): ?>
    <div class="row cart <?php ecco($first==true, 'medium-space-top') ?>">
        <div class="small-2 medium-2 columns">
            <?php $product = page($item->uri()) ?>
            <img src="<?= $product->images()->first()->thumb(['width'=>100, 'height'=>100, 'crop'=>true])->url() ?>" title="<?= $item->name() ?>">
        </div>
        <div class="small-6 medium-6 columns">
            <a href="<?= $product->url() ?>">
            <?= $item->name ?>&mdash;<?php e($item->variant()->isNotEmpty(), $item->variant()) ?>
            </a>
        </div>
        <div class="small-2 medium-2 columns">
            <input class="input-qty right" name="cart[<?=$item->sku() ?>]" id="<?= $item->uri() . '::' . $item->sku() ?>" value="<?= $item->quantity() ?>" min="0" max="<?= inStock($item->id()) ?>" data-variant="<?= $item->sku() ?>" type="number">
            <input id="input-csrf" type="hidden" name="csrf" value="<?= csrf() ?>">
        </div>
        <div class="small-2 medium-2 columns">
            <span class="right"><?= 'CAD'.$item->amount()->value * $item->quantity()->value ?>
                <form action="" method="post">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $item->id() ?>">
                    <button type="submit">delete</button> 
                </form>
            </span>
        </div>
    </div>
<?php $first = false;
    endforeach ?>

<div class="row medium-space-top">
    <div class="small-12 medium-8 columns">
        <h2>total</h2>
    </div>
    <div class="small-12 medium-4 columns">
        <h2 class="right">CAD<?= $total ?></h2>
    </div>
</div>

<button id="checkoutButton">Checkout with stripe</button>
</div>

<?= js('https://checkout.stripe.com/checkout.js') ?>
<input id="checkout-key" type="hidden" name="key" value="<?= c::get("stripe_key_pub") ?>">
<input id="checkout-total" type="hidden" name="total" value="<?= $total*100 ?>">
<input id="checkout-content" type="hidden" name="content" value="<?= $content ?>">

<?php endif ?>

<?php snippet('footer') ?>
<?= js('assets/js/vendor/cart.js') ?>