<?php snippet('header') ?>
<?php snippet('menu') ?>

<div class="row medium-space-top">
<h1 dir="auto"><?= $page->title()->html() ?></h1>

<?= $page->text()->kirbytext() ?>

<?php if (!s::get('txn') or $txn->products()->toStructure()->count() === 0) { ?>
    <p dir="auto" class="notification warning">
        No cart items
    </p>
<?php } else { ?>

    <!-- Cart items -->
    <div class="table-overflow">
        <table dir="auto" class="checkout">
            <thead>
                <tr>
                    <th> product </th>
                    <th> quantity </th>
                    <th> price </th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach(getItems() as $i => $item) { ?>
                    <?php $product = page($item->uri()) ?>
                    <tr>
                        <td>
                            <a href="<?= $product->url() ?>">
                                <?php if ($product->hasImages()) { ?>
                                    <img src="<?= $product->images()->first()->thumb(['width'=>60, 'height'=>60, 'crop'=>true])->url() ?>" title="<?= $item->name() ?>">
                                <?php } ?>
                                <strong><?= $item->name ?></strong><br>
                                <?php e($item->sku()->isNotEmpty(), '<strong>SKU</strong> '.$item->sku().' / ') ?>
                                <?php e($item->variant()->isNotEmpty(), $item->variant()) ?>
                            </a>
                        </td>
                        <td class="quantity">
                            <form action="" method="post">
                                <input type="hidden" name="id" value="<?= $item->id() ?>">
                                <?php if ($item->quantity()->value == 1) { ?>
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit">
                                        x
                                    </button>
                                <?php } else { ?>
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit">
                                        -
                                    </button>
                                <?php } ?>
                            </form>
                            <span><?= $item->quantity() ?></span>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?= $item->id() ?>">
                                <button type="submit">
                                    +
                                </button>
                            </form>
                        </td>
                        <td>
                            <?php
                                // Price text
                                foreach ($product->variants()->toStructure() as $variant) {
                                    if ($item->variant() == str::slug($variant->name())) {
                                        $v = $variant;
                                    }
                                }
                                echo '<class="badge">CAD'.$item->amount()->value * $item->quantity()->value.'</del><br>';
                            ?>

                        </td>
                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $item->id() ?>">
                                <button type="submit">delete</button> 
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

            <tfoot>
                <tr class="total">
                    <td colspan="2"> total </td>
                    <td>
                        CAD<?= $total ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
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