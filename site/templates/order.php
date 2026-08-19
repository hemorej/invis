<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>
<script>sessionStorage.removeItem('cart');</script>

<?php if($state == 'complete'): ?>

    <?php
    $items = [];
    $subtotal = 0;
    foreach($order->products()->yaml() as $product) {
        $items[] = [
            'variant'  => $product['variant'],
            'name'     => $product['name'],
            'quantity' => $product['quantity'],
            'price'    => $product['amount'],
        ];
        $subtotal += intval($product['quantity'] * $product['amount']);
    }
    $shipping   = (float) $order->shipping()->value;
    $grandTotal = $subtotal + $shipping;
    $customer   = $order->customer()->yaml();
    $address    = $customer['address'];
    ?>

    <div class="gold-lbl mb-20">— order confirmed</div>
    <h1 class="spectral fw3 f-38 lh-108 ink-dark ls-tight mt0 mb-22">thank you, <?= html($customer['name']) ?></h1>
    <p class="spectral f-18 lh-172 ink-copy mt0" style="max-width:58ch">
        Your order number is <span class="ink-dark"><?= html($order->suuid()->value()) ?></span>.
        You&rsquo;ll receive an email confirmation shortly. If you have questions about your order or would like to make changes, contact us at
        <a href="mailto:&#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;" class="gold no-underline">&#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;</a>.
    </p>

    <div class="flex mt-68 flex-wrap gap-80">
        <div style="flex:1.3;min-width:320px">
            <div class="gold-lbl mb-26">— order details</div>
            <div style="display:grid;grid-template-columns:1fr auto;column-gap:48px;row-gap:15px;max-width:440px;align-items:baseline">
                <?php foreach($items as $item): ?>
                    <span class="spectral f-18 lh-copy ink-body">
                        <?= html($item['name']) ?>
                        <span class="ink-subtle"> — <?= html($item['variant']) ?> &middot; &times;<?= html($item['quantity']) ?></span>
                    </span>
                    <span class="spectral f-18 lh-copy ink-body tr" style="white-space:nowrap">CAD&nbsp;<?= html($item['price'] * $item['quantity']) ?></span>
                <?php endforeach ?>
                <span class="spectral f-18 lh-copy ink-muted">shipping</span>
                <span class="spectral f-18 lh-copy ink-muted tr" style="white-space:nowrap">CAD&nbsp;<?= html($shipping) ?></span>
                <div style="grid-column:1/3;border-top:1px solid var(--rule);margin:8px 0 4px"></div>
                <span class="spectral fw5 f-19 lh-14 ink-dark">total</span>
                <span class="spectral fw5 f-19 lh-14 ink-dark tr" style="white-space:nowrap">CAD&nbsp;<?= html(intval($grandTotal)) ?></span>
            </div>
        </div>

        <div style="flex:1;min-width:260px">
            <div class="gold-lbl mb-26">— shipping details</div>
            <div class="spectral f-17 ink-copy" style="line-height:1.85">
                <div class="ink-dark"><?= html($customer['name']) ?></div>
                <div><?= html($address['address_line_1']) ?></div>
                <?php if(!empty($address['address_line_2'])): ?>
                    <div><?= html($address['address_line_2']) ?></div>
                <?php endif ?>
                <div><?= html($address['city']) ?>, <?= html($address['state']) ?>&nbsp;&nbsp;<?= html($address['postal_code']) ?></div>
                <div><?= html($address['country']) ?></div>
                <div class="mt-14"><?= html($customer['email']) ?></div>
            </div>
        </div>
    </div>

    <a href="./prints" class="spectral f-18 lh-1 ink-subtle no-underline mt-72 dib">« continue shopping</a>

<?php snippet('partials/footer') ?>

<?php elseif($state == 'error'): ?>

    <div class="gold-lbl mb-20">— order error</div>
    <h1 class="spectral fw3 f-38 lh-108 ink-dark ls-tight mt0 mb-22">something went wrong</h1>
    <p class="spectral f-18 lh-172 ink-copy mt0" style="max-width:58ch">
        <?= html($message) ?> Please contact me with your session ID
        (<span class="ink-subtle"><?= html($kirby->session()->startTime() . $kirby->session()->expiryTime()) ?></span>)
        at <a href="mailto:&#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;" class="gold no-underline">&#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;</a> to resolve this issue.
    </p>

<?php snippet('partials/footer') ?>

<?php else: ?>
    <?= @go('/prints') ?>
<?php endif ?>
