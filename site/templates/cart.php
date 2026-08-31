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
<div id="cart">
    <!-- Loading overlay -->
    <div :class="[orderWaiting ? 'db' : 'dn']" class="fixed top-0 left-0 w-100 h-100 z-999" style="background:rgba(0,0,0,0.15)"></div>

    <!-- Hidden data refs -->
    <input ref="userLocation" type="hidden" value="<?= html($currentLocation) ?>" />
    <input ref="checkoutKey" type="hidden" value="<?= option('stripe_key_pub') ?>">
    <input ref="checkoutSessionID" type="hidden" value="<?= html($checkoutSessionId) ?>">
    <input ref="checkoutTotal" type="hidden" value="<?= html($total) ?>">
    <input ref="currencies" type="hidden" value="<?= html($currencies) ?>">
    <input ref="checkoutContent" type="hidden" value="<?= html($content) ?>">
    <input ref="ppCsrf" type="hidden" value="<?= csrf() ?>">

    <!-- Stock error banner -->
    <div :class="[error ? 'db' : 'dn']" class="spectral f-18 gold ba b--accent pa3 mb-32 tc">
        Sorry, there&rsquo;s only {{ leftInStock }} left in stock.
        <a class="ml2 gold no-underline pointer" v-on:click.prevent="error = false">&times;</a>
    </div>

    <!-- Step indicator (hidden on order confirmation) -->
    <div v-show="inCart || inShipping" class="flex items-baseline mb-64 flex-wrap">
        <a class="step-lnk spectral" :class="[inCart && !inCheckout ? 'step-on' : 'step-off']" href="#">
            <span class="gold f-15">01</span>&nbsp;&nbsp;cart
        </a>
        <span class="ink-rule2 spectral f-18 lh-1" style="margin:0 22px">———</span>
        <a class="step-lnk spectral" :class="[inShipping || inCheckout ? 'step-on' : 'step-off']" href="#">
            <span class="gold f-15">02</span>&nbsp;&nbsp;shipping
        </a>
        <span class="ink-rule2 spectral f-18 lh-1" style="margin:0 22px">———</span>
        <a class="step-lnk spectral" :class="[inCheckout ? 'step-on' : 'step-off']" href="#">
            <span class="gold f-15">03</span>&nbsp;&nbsp;payment
        </a>
    </div>

    <!-- ===== STEP 1: CART ===== -->
    <div v-if="inCart && !inCheckout">
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
                    <button class="qty-btn" v-on:click.prevent="decQty">−</button>
                    <input v-on:change="updateCart"
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
                    <button class="qty-btn" v-on:click.prevent="incQty">+</button>
                </div>
                <form action="" method="post" class="dib">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= html($item->id()) ?>">
                    <button class="cart-remove-btn f7 br-100 flex items-center justify-center pointer" style="width:22px;height:22px;border-radius:50%" type="submit">&times;</button>
                </form>
            </div>
        </div>
        <input ref="inputCsrf" type="hidden" name="csrf" value="<?= csrf() ?>">
        <?php endforeach ?>

        <!-- Totals -->
        <div class="flex bt-rule mt-40 pt-30 flex-wrap" style="justify-content:space-between;align-items:flex-start;gap:30px">
            <a href="./prints" class="spectral f-18 lh-1 ink-subtle no-underline" style="white-space:nowrap">« continue shopping</a>
            <div style="margin-left:auto;text-align:right;min-width:260px">
                <div class="spectral ink-dark" style="font-size:25px;line-height:1">total CAD {{ total }}</div>
                <div class="spectral f-15 lh-1 ink-light mt2">≈ {{ currencies }}</div>
            </div>
        </div>

        <button class="btn-cart mt-44" v-on:click.prevent="showShipping">begin checkout</button>
    </div>

    <!-- ===== STEP 2: SHIPPING ===== -->
    <div v-else-if="!inCart && inShipping">
        <div style="display:grid;grid-template-columns:1fr 1fr;column-gap:32px;row-gap:26px;max-width:700px">
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">full name</label>
                <input v-model="name" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">email</label>
                <input v-model="email" type="email" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">address line 1</label>
                <input v-model="line1" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">address line 2 <span class="ink-light">— optional</span></label>
                <input v-model="line2" type="text" class="cart-input spectral">
            </div>
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">city</label>
                <input v-model="city" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">province / state</label>
                <input v-model="province" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:1/2">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">postal code</label>
                <input v-model="postcode" type="text" required class="cart-input spectral">
            </div>
            <div style="grid-column:2/3">
                <label class="spectral f-15 lh-1 ink-subtle db mb2">country</label>
                <select v-model="country" class="tic-select cart-input spectral">
                    <?php foreach(countryList() as $countryName): ?>
                        <option value="<?= html($countryName) ?>"><?= html($countryName) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>

        <p class="spectral f-17 lh-16 mt-40 mb0" style="color:var(--muted);max-width:700px">
            By continuing to checkout, you agree to the general
            <a href="#" class="ink-dark no-underline bb bw1 b--accent pb1" v-on:click.prevent="showTerms = !showTerms">terms</a>
            of the sale.
        </p>
        <p class="spectral f-17 lh-16 ink-copy" v-show="showTerms"><?= html($site->terms()) ?></p>

        <div class="flex items-center mt-40 flex-wrap gap-24">
            <a href="#" class="spectral f-18 lh-1 ink-subtle no-underline" v-on:click.prevent="resetToCart">« back to cart</a>
            <button class="btn-cart" style="margin-bottom:0"
                :disabled="shippingIncomplete"
                v-on:click.prevent="showCheckout">finish checkout</button>
            <input type="hidden" ref="checkoutCSRF" value="<?= csrf() ?>">
        </div>
    </div>

    <!-- ===== STEP 3: PAYMENT ===== -->
    <div v-else-if="inCart && inCheckout">
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
                <div class="ink-dark">{{ name }}</div>
                <div>{{ email }}</div>
                <div class="mt-14">{{ line1 }}</div>
                <div v-show="line2">{{ line2 }}</div>
                <div>{{ city }}, {{ province }}&nbsp;&nbsp;{{ postcode }}</div>
                <div>{{ country }}</div>
            </div>
            <div style="margin-left:auto;text-align:right;min-width:240px">
                <div class="flex mb-22" style="justify-content:flex-end;gap:64px">
                    <span class="spectral f-18 lh-1 ink-muted">shipping</span>
                    <span class="spectral f-18 lh-1 ink-body">CAD {{ shipping }}</span>
                </div>
                <div class="spectral ink-dark" style="font-size:25px;line-height:1">total CAD {{ total }}</div>
                <div class="spectral f-15 lh-1 ink-light mt2">≈ {{ currencies }}</div>
            </div>
        </div>

        <div class="flex items-center mt-48 flex-wrap gap-24">
            <a href="#" class="spectral f-18 lh-1 ink-subtle no-underline" v-on:click.prevent="showShipping">« shipping</a>
            <button class="btn-cart" style="margin-bottom:0" v-on:click.prevent="redirectStripe">credit card checkout</button>
        </div>
    </div>
</div>
<?php endif ?>

<?php snippet('partials/footer', [], false, true) ?>
<?php slot('scripts') ?>
    <?php if(@option('env') == 'prod'): ?>
        <?= js('assets/dist/cart.min.js') ?>
    <?php else: ?>
        <?= js('https://unpkg.com/axios/dist/axios.min.js') ?>
        <?= js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js') ?>
        <?= js('assets/js/cart.js') ?>
    <?php endif ?>
    <?= js('https://js.stripe.com/v3/', ['async' => true]) ?>
<?php endslot() ?>
<?php endsnippet() ?>
