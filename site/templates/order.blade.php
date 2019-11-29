@include('partials.header')
@include('partials.menu')
<script>
	sessionStorage.removeItem('cart');
</script>

@if($state == 'complete')
	<div class="row medium-space-top">
    	<span class="high-contrast">Order confirmation</span>
	</div>
	<span></span>
	<div class="row">
		<div class="small-12 medium-12 columns">
			<h2>Thank you for your order</h2>
			Your order number is {{ $order->autoid() }}. You will receive an email confirmation shortly. If you have questions about your order or would like to make changes contact us at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;
		</div>
	</div>

	@php
        $items = array();
        $total = 0;
        foreach(Yaml::decode($order->products()) as $product)
        {   
            $items[] = array('variant' => $product['variant'], 'name' => $product['name'], 'quantity' => $product['quantity'], 'price' => $product['amount']);
            $total += intval($product['quantity'] * $product['amount']);
        }
    @endphp

	<div class="row medium-space-top">
		<div class="small-12 medium-6 columns">
			<h2>Order details</h2>
			<table>
        	@foreach($items as $item)
              <tr>
                <td>{{ $item['name'] }}: <em>{{ $item['variant'] }} x{{ $item['quantity'] }}</em></td>
                <td>— ${{ $item['price']*$item['quantity'] }}</td>
              </tr>
          	@endforeach
              <tr>
                <td>Shipping</td>
                <td>— included</td>
              </tr>
              <tr>
                <td><b>Total</b></td>
                <td>— <b>${{ intval($total) }}</b></td>
              </tr>
          </table>
		</div>
		<div class="small-12 medium-6 columns">
			<h2>Shipping details</h2>
			@php
				$customer = \Yaml::decode($order->customer());
				$address = $customer['address'];
			@endphp

			{{ $customer['name'] }}<br/> {{ $address['address_line_1'] }} {{ $address['address_line_2'] }}<br /> {{ $address['city'] }}, {{ $address['state'] }} <br /> {{ $address['country'] }}<br/> {{ $address['postal_code'] }}<br/> {{ $customer['email'] }}<br/>
		</div>
	</div>
	
@include('partials.footer')
@elseif($state == 'error')
	<div class="row medium-space-top">
    	<span class="high-contrast">Order error</span>
	</div>
	<span></span>
	<div class="row">
		<div class="small-12 medium-12 columns">
			I'm sorry but there's a problem with your order. {{ $message }}<br />
			Please contact me with your session ID ({{ $kirby->session()->startTime() . $kirby->session()->expiryTime()}}) at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D; to resolve this issue. 
		</div>
	</div>
@else
	{{ @go('/prints') }}
@endif