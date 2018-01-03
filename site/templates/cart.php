<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
    <h1><span class="high-contrast">
        <?= $page->title()->html() ?>
    </h1></span>

<?= $page->text()->kirbytext() ?>

<?php if (!s::get('txn') or $txn->products()->toStructure()->count() === 0) { ?>
    <section class="small-12 medium-8 columns">
      <article>
        No cart items
        </article>
    </section>

<?php } else { ?>

<!-- Cart items -->
<div class="row">
    <div class="small-2 medium-2 columns">image</div>
    <div class="small-6 medium-6 columns text-center">
        description
    </div>
    <div class="small-2 medium-2 columns text-right">
        quantity
    </div>
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
            <input class="input-qty right" name="cart" value="<?= $item->quantity() ?>" min="0" type="number" id="form-cart">
        </div>
        <div class="small-2 medium-2 columns">
            <?php
                foreach ($product->variants()->toStructure() as $variant) {
                    if ($item->variant() == str::slug($variant->name())) {
                        $v = $variant;
                    }
                }
            ?>
            <span class="right"><?= 'CAD'.$item->amount()->value * $item->quantity()->value ?>
                <form action="" method="post">
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

<script src="https://checkout.stripe.com/checkout.js"></script>
<button id="customButton">Checkout with stripe</button>

<script>
var handler = StripeCheckout.configure({
  key: 'pk_test_6pRNASCoBOKtIshFeQd4XMUh',
  image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
  locale: 'auto',
  token: function(token) {
    // You can access the token ID with `token.id`.
    // Get the token ID to your server-side code for use.
  }
});

document.getElementById('customButton').addEventListener('click', function(e) {
  // Open Checkout with further options:
  handler.open({
    name: 'the Invisible Cities',
    description: <?= $items ?> + ' <?= $type ?>',
    zipCode: true,
    currency: 'CAD',
    shippingAddress: true,
    amount: <?= $total*100 ?>
  });
  e.preventDefault();
});

// Close Checkout on page navigation:
window.addEventListener('popstate', function() {
  handler.close();
});
</script>
    </div>
<?php } ?>
</div>
</div>
<?php snippet('footer') ?>