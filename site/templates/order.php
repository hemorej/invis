<?php snippet('header') ?>
<?php snippet('menu') ?>

<?php if($state == 'complete'): ?>
	<div class="row medium-space-top">
    	<span class="high-contrast">Order confirmation</span>
	</div>
	<span></span>
	<div class="row">
		<div class="small-12 medium-12 columns">
			<h2>Thank you for your order</h2>
			Your order number is <?= $order->order_id() ?>. You will receive an email confirmation shortly. If you have questions about your order or would like to make changes contact us at &#105;&#110;&#102;&#111;&#064;&#116;&#104;&#101;&#045;&#105;&#110;&#118;&#105;&#115;&#098;&#108;&#101;&#045;&#099;&#105;&#116;&#105;&#101;&#115;&#046;&#099;&#111;&#109;
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
			An error occurred,
			<?php if(s::get('txn')): ?>
				 please try again or
			<?php endif ?> contact us with your session ID (<?= s::id() ?>) at &#105;&#110;&#102;&#111;&#064;&#116;&#104;&#101;&#045;&#105;&#110;&#118;&#105;&#115;&#098;&#108;&#101;&#045;&#099;&#105;&#116;&#105;&#101;&#115;&#046;&#099;&#111;&#109;
		</div>
	</div>
<?php else:
	go('/prints');
endif ?>