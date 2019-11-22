<?php snippet('header') ?>
<?php snippet('menu') ?>
<script>
	sessionStorage.removeItem('cart');
</script>

<?php if($state == 'complete'): ?>
	<div class="row medium-space-top">
    	<span class="high-contrast">Order confirmation</span>
	</div>
	<span></span>
	<div class="row">
		<div class="small-12 medium-12 columns">
			<h2>Thank you for your order</h2>
			Your order number is <?= $order->order_id() ?>. You will receive an email confirmation shortly. If you have questions about your order or would like to make changes contact us at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;
		</div>
	</div>

	<?php
        $items = array();
        $total = 0;
        foreach(yaml::decode($order->products()) as $product)
        {   
            $items[] = array('variant' => $product['variant'], 'name' => $product['name'], 'quantity' => $product['quantity'], 'price' => $product['amount']);
            $total += intval($product['quantity'] * $product['amount']);
        }
    ?>

	<div class="row medium-space-top">
		<div class="small-12 medium-6 columns">
			<h2>Order details</h2>
			<table>
        	<?php foreach($items as $item): ?>
              <tr>
                <td><?= $item['name'] ?>: <em><?= $item['variant']?> x<?= $item['quantity'] ?></em></td>
                <td>— $<?= $item['price']*$item['quantity'] ?></td>
              </tr>
          	<?php endforeach ?>
              <tr>
                <td>Shipping</td>
                <td>— included</td>
              </tr>
              <tr>
                <td><b>Total</b></td>
                <td>— <b>$<?= intval($total) ?></b></td>
              </tr>
          </table>
		</div>
		<div class="small-12 medium-6 columns">
			<h2>Shipping details</h2>
			<?php $customer = $order->customer()->toStructure();
			$address = $customer->address(); ?>
			<?= $customer->name()->value() ?><br/> <?= $address->street()->value() ?>, <?= $address->city()->value() ?><br/> <?= $address->province()->value() ?> <?= $address->country()->value() ?><br/> <?= $address->postal_code()->value() ?><br/> <?= $customer->email()->value() ?><br/>
		</div>
	</div>
<?php snippet('footer') ?>
<?php elseif($state == 'error'): ?>
	<div class="row medium-space-top">
    	<span class="high-contrast">Order error</span>
	</div>
	<span></span>
	<div class="row">
		<div class="small-12 medium-12 columns">
			I'm sorry but there's a problem with your order. <?= $message ?><br />
			Please contact me with your session ID (<?= s::id() ?>) at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D; to resolve this issue. 
		</div>
	</div>
<?php else:
	go('/prints');
endif ?>