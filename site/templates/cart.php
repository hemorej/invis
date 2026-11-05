<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>
<?php use \Cart\Cart; ?>

<noscript>
    <div class="spectral f-18 lh-17">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>

<?php if(!$kirby->session()->get('txn') or empty($txn) or $txn->products()->toStructure()->count() === 0): ?>
    <p class="spectral f-21 lh-16 ink-copy" style="margin:0 0 80px">Your cart is empty. Would you like to look at some <a class="gold no-underline" href="/prints">prints</a>?</p>
<?php else: ?>
<div id="cart" x-data="cart">
    <!-- Loading overlay -->
    <div x-cloak :class="orderWaiting ? 'db' : 'dn'" class="fixed top-0 left-0 w-100 h-100 z-999" style="background:rgba(0,0,0,0.15)"></div>

    <!-- Hidden data refs -->
    <input x-ref="userLocation" type="hidden" value="<?= html($currentLocation) ?>" />
    <input x-ref="checkoutKey" type="hidden" value="<?= option('stripe_key_pub') ?>">
    <input x-ref="checkoutSessionID" type="hidden" value="<?= html($checkoutSessionId) ?>">
    <input x-ref="checkoutTotal" type="hidden" value="<?= html($total) ?>">
    <input x-ref="currencies" type="hidden" value="<?= html($currencies) ?>">
    <input x-ref="checkoutContent" type="hidden" value="<?= html($content) ?>">
    <input x-ref="ppCsrf" type="hidden" value="<?= csrf() ?>">

    <!-- Stock error banner -->
    <div x-cloak :class="error ? 'db' : 'dn'" class="spectral f-18 gold ba b--accent pa3 mb-32 tc">
        Sorry, there&rsquo;s only <span x-text="leftInStock"></span> left in stock.
        <a class="ml2 gold no-underline pointer" @click.prevent="error = false">&times;</a>
    </div>

    <!-- Step indicator (hidden on order confirmation) -->
    <div x-show="inCart || inShipping" class="flex items-baseline mb-64 flex-wrap">
        <a class="step-lnk spectral" :class="inCart && !inCheckout ? 'step-on' : 'step-off'" href="#">
            <span class="gold f-15">01</span>&nbsp;&nbsp;cart
        </a>
        <span class="ink-rule2 spectral f-18 lh-1" style="margin:0 22px">———</span>
        <a class="step-lnk spectral" :class="inShipping || inCheckout ? 'step-on' : 'step-off'" href="#">
            <span class="gold f-15">02</span>&nbsp;&nbsp;shipping
        </a>
        <span class="ink-rule2 spectral f-18 lh-1" style="margin:0 22px">———</span>
        <a class="step-lnk spectral" :class="inCheckout ? 'step-on' : 'step-off'" href="#">
            <span class="gold f-15">03</span>&nbsp;&nbsp;payment
        </a>
    </div>

    <!-- ===== STEP 1: CART ===== -->
    <template x-if="inCart && !inCheckout">
    <div>
        <!-- Column labels -->
        <div class="cart-lbl-grid spectral f-18 lh-1 gold pb-22" style="font-style:italic">
            <span></span>
            <span>— item</span>
            <span class="tr">price</span>
            <span class="tr">quantity</span>
        </div>

        <?php
        $cartItemsArr = $cartItems;
        foreach($cartItemsArr as $i => $item):
            $product = page($item->uri());
        ?>
        <div class="cart-item-grid bt-rule pt-30" data-qty-row>
            <img src="<?= html($product->images()->first()->crop(96)->url()) ?>" alt="<?= html($item->name()) ?>" class="db" style="width:96px;height:96px;object-fit:cover;filter:none">
            <div>
                <div class="spectral f-20 lh-13 ink-dark">
                    <a class="no-underline ink-dark hover-gold ttl" href="<?= html($product->url()) ?>"><?= html($item->name()) ?></a>
                    <?php if($item->variant()->isNotEmpty()): ?>
                        <span class="ink-subtle"> &mdash; <?= html($item->variant()) ?></span>
                    <?php endif ?>
                </div>
                <div class="spectral f-15 lh-14 ink-light mt1"><?= html($product->meta()->value()) ?></div>
            </div>
            <div class="tr spectral f-18 lh-1 ink-body">CAD&nbsp;<?= html($item->amount()->value) ?></div>
            <div class="flex items-center justify-end gap-14">
                <div class="flex items-center" style="border:1px solid var(--border)">
                    <button class="qty-btn" @click.prevent="decQty($event)">−</button>
                    <input @change="updateCart($event)"
                        class="qty-input"
                        data-variant="<?= html(esc($item->variant())) ?>"
                        id="<?= html($item->uri()) ?>::<?= html($item->suuid()) ?>"
                        value="<?= html($item->quantity()) ?>"
                        min="0"
                        max="<?= html(Cart::inStock($item->variant())) ?>"
                        data-sku="<?= html($item->suuid()) ?>"
                        data-amount="<?= html($item->amount()->value()) ?>"
                        data-name="<?= html($item->name()) ?>"
                        type="number">
                    <button class="qty-btn" @click.prevent="incQty($event)">+</button>
                </div>
                <form action="" method="post" class="dib">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= html($item->id()) ?>">
                    <button class="cart-remove-btn f7 br-100 flex items-center justify-center pointer" style="width:22px;height:22px;border-radius:50%" type="submit">&times;</button>
                </form>
            </div>
        </div>
        <input x-ref="inputCsrf" type="hidden" name="csrf" value="<?= csrf() ?>">
        <?php endforeach ?>

        <!-- Totals -->
        <div class="flex bt-rule mt-40 pt-30 flex-wrap" style="justify-content:space-between;align-items:flex-start;gap:30px">
            <a href="./prints" class="spectral f-18 lh-1 ink-subtle no-underline" style="white-space:nowrap">« continue shopping</a>
            <div style="margin-left:auto;text-align:right;min-width:260px">
                <div class="spectral ink-dark" style="font-size:25px;line-height:1">total CAD <span x-text="total"></span></div>
                <div class="spectral f-15 lh-1 ink-light mt2">≈ <span x-text="currencies"></span></div>
            </div>
        </div>

        <button class="btn-cart mt-44" @click.prevent="showShipping()">begin checkout</button>
    </div>
    </template>

    <!-- ===== STEP 2: SHIPPING ===== -->
    <template x-if="!inCart && inShipping">
    <div>
        <div style="display:grid;grid-template-columns:1fr 1fr;column-gap:32px;row-gap:26px;max-width:700px">
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">full name</label>
                <input x-model="name" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">email</label>
                <input x-model="email" type="email" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">address line 1</label>
                <input x-model="line1" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">address line 2 <span class="ink-light">— optional</span></label>
                <input x-model="line2" type="text" class="cart-input spectral">
            </div>
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">city</label>
                <input x-model="city" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">province / state</label>
                <input x-model="province" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">postal code</label>
                <input x-model="postcode" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">country</label>
                <select x-model="country" class="tic-select cart-input spectral">
                    <?php foreach(countryList() as $countryName): ?>
                        <option value="<?= html($countryName) ?>"><?= html($countryName) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <p class="spectral f-17 lh-16 mt-40 mb0" style="color:var(--muted);max-width:700px">
            By continuing to checkout, you agree to the general
            <a href="#" class="ink-dark no-underline bb bw1 b--accent pb1" @click.prevent="showTerms = !showTerms">terms</a>
            of the sale.
        </p>
        <p class="spectral f-17 lh-16 ink-copy" x-show="showTerms"><?= html($site->terms()) ?></p>

        <div class="flex items-center mt-40 flex-wrap gap-24">
            <a href="#" class="spectral f-18 lh-1 ink-subtle no-underline" @click.prevent="resetToCart()">« back to cart</a>
            <button class="btn-cart" style="margin-bottom:0"
                :disabled="shippingIncomplete"
                @click.prevent="showCheckout()">finish checkout</button>
            <input type="hidden" x-ref="checkoutCSRF" value="<?= csrf() ?>">
        </div>
    </div>
    </template>

    <!-- ===== STEP 3: PAYMENT ===== -->
    <template x-if="inCart && inCheckout">
    <div>
        <?php
        foreach($cartItemsArr as $i => $item):
            $product = page($item->uri());
        ?>
        <div class="flex items-center gap-28 mb-32">
            <img src="<?= html($product->images()->first()->crop(96)->url()) ?>" alt="<?= html($item->name()) ?>" class="db flex-none" style="width:96px;height:96px;object-fit:cover;filter:none">
            <div class="flex-auto">
                <div class="spectral f-20 lh-13 ink-dark">
                    <?= html($item->name()) ?>
                    <?php if($item->variant()->isNotEmpty()): ?>
                        <span class="ink-subtle"> &mdash; <?= html($item->variant()) ?></span>
                    <?php endif ?>
                </div>
                <div class="spectral f-15 lh-14 ink-light mt1"><?= html($product->meta()->value()) ?></div>
            </div>
            <div class="spectral f-18 lh-1 ink-body ml-auto" style="white-space:nowrap">CAD&nbsp;<?= html($item->amount()->value) ?> · ×<?= html($item->quantity()) ?></div>
        </div>
        <?php endforeach ?>

        <div class="flex bt-rule mt-44 pt-32 flex-wrap" style="justify-content:space-between;align-items:flex-start;gap:40px">
            <div class="spectral f-17 ink-copy" style="line-height:1.85">
                <div class="ink-dark"><span x-text="name"></span></div>
                <div><span x-text="email"></span></div>
                <div class="mt-14"><span x-text="line1"></span></div>
                <div x-show="line2"><span x-text="line2"></span></div>
                <div><span x-text="city"></span>, <span x-text="province"></span>&nbsp;&nbsp;<span x-text="postcode"></span></div>
                <div><span x-text="country"></span></div>
            </div>
            <div style="margin-left:auto;text-align:right;min-width:240px">
                <div class="flex mb-22" style="justify-content:flex-end;gap:64px">
                    <span class="spectral f-18 lh-1 ink-muted">shipping</span>
                    <span class="spectral f-18 lh-1 ink-body">CAD <span x-text="shipping"></span></span>
                </div>
                <div class="spectral ink-dark" style="font-size:25px;line-height:1">total CAD <span x-text="total"></span></div>
                <div class="spectral f-15 lh-1 ink-light mt2">≈ <span x-text="currencies"></span></div>
            </div>
        </div>

        <div class="flex items-center mt-48 flex-wrap gap-24">
            <a href="#" class="spectral f-18 lh-1 ink-subtle no-underline" @click.prevent="showShipping()">« shipping</a>
            <button class="btn-cart" style="margin-bottom:0" @click.prevent="redirectStripe()">credit card checkout</button>
        </div>
    </div>
    </template>
</div>
<?php endif ?>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
    <?php if(@option('env') == 'prod'): ?>
        <?= js('assets/dist/cart.min.js') ?>
        <?= js('assets/dist/alpine.min.js') ?>
    <?php else: ?>
        <?= js('assets/js/cart.js') ?>
        <?= js('assets/js/vendor/alpine.min.js') ?>
    <?php endif ?>
    <?= js('https://js.stripe.com/v3/', ['async' => true]) ?>
<?php endslot() ?>
<?php endsnippet() ?>
