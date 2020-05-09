@include('partials.header')
@include('partials.menu')
<script>
	sessionStorage.removeItem('cart');
</script>

@if($state == 'complete')
	<div class="measure black-70 f4 f4-ns f3-l ph2 mt4">
    	<h4>Order confirmation</h4>
	</div>
	<span class='db mb2'></span>
	<div class="black-70 f4 f4-ns f3-ns ph2 measure-wide lh-copy">
		<span class="db">Thank you for your order</span>
		Your order number is {{ $order->autoid() }}. You will receive an email confirmation shortly. If you have questions about your order or would like to make changes contact us at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;
	</div>

	@php
        $items = array();
        $total = 0;
        foreach(Yaml::decode($order->products()) as $product)
        {   
            $items[] = array('variant' => $product['variant'], 'name' => $product['name'], 'quantity' => $product['quantity'], 'price' => $product['amount']);
            $total += intval($product['quantity'] * $product['amount']);
        }
        $discount = Yaml::decode($order->discount());
        if(!empty($discount['amount'])){
            $total = $total * (1 - intval($discount['amount'])/100);
        }
    @endphp

    <section class="mw9 center ph2 black-70 f4 f4-ns">
	  <div class="cf">
	    <div class="fl w-100 w-70-l">
	      <h3>Order details</h3>
			<table>
        	@foreach($items as $item)
              <tr>
                <td>{{ $item['name'] }}: <em>{{ $item['variant'] }} x{{ $item['quantity'] }}</em></td>
                <td>— ${{ $item['price']*$item['quantity'] }}</td>
              </tr>
          	@endforeach
            @if(!empty($discount))
                <tr>
                    <td>Discount ({{$discount['code']}})</td>
                    <td>&nbsp;-{{$discount['amount']}}%</td>
                </tr>
            @endif
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
	    <div class="fl w-100 w-30-l ph2-l">
	    	<h3>Shipping details</h3>
			@php
				$customer = \Yaml::decode($order->customer());
				$address = $customer['address'];
			@endphp

			{{ $customer['name'] }}<br/> {{ $address['address_line_1'] }} {{ $address['address_line_2'] }}<br /> {{ $address['city'] }}, {{ $address['state'] }} <br /> {{ $address['country'] }}<br/> {{ $address['postal_code'] }}<br/> {{ $customer['email'] }}<br/>
	    </div>
	  </div>
	</section>
	
@include('partials.footer')
@elseif($state == 'error')
	<div class="measure black-70 f4 f4-ns f3-l ph2 mt4">
    	<span class="f4 f4-ns f3-l measure lh-copy db">Order error</span>
	</div>
	<span class='db mb2'></span>
	<div class="measure black-70 f4 f4-ns f3-l ph2 mt4">
		I'm sorry but there's a problem with your order. {{ $message }}<br />
		Please contact me with your session ID ({{ $kirby->session()->startTime() . $kirby->session()->expiryTime()}}) at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D; to resolve this issue. 
	</div>
@else
	{{ @go('/prints') }}
@endif