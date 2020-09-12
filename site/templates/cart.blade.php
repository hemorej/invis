@include('partials.header')
@include('partials.menu')
@php use \Cart\Cart; @endphp
<style>
.loading{margin:auto}.loading:before{content:'';display:block;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.3)}.loading:not(:required){font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.loading:not(:required):after{content:'';display:block;font-size:10px;width:.5em;height:.5em;margin-top:-.5em;-webkit-animation:spinner 1500ms infinite linear;-moz-animation:spinner 1500ms infinite linear;-ms-animation:spinner 1500ms infinite linear;-o-animation:spinner 1500ms infinite linear;animation:spinner 1500ms infinite linear;border-radius:.5em;-webkit-box-shadow:rgba(0,0,0,0.75) 1.5em 0 0 0,rgba(0,0,0,0.75) 1.1em 1.1em 0 0,rgba(0,0,0,0.75) 0 1.5em 0 0,rgba(0,0,0,0.75) -1.1em 1.1em 0 0,rgba(0,0,0,0.5) -1.5em 0 0 0,rgba(0,0,0,0.5) -1.1em -1.1em 0 0,rgba(0,0,0,0.75) 0 -1.5em 0 0,rgba(0,0,0,0.75) 1.1em -1.1em 0 0;box-shadow:rgba(0,0,0,0.75) 1.5em 0 0 0,rgba(0,0,0,0.75) 1.1em 1.1em 0 0,rgba(0,0,0,0.75) 0 1.5em 0 0,rgba(0,0,0,0.75) -1.1em 1.1em 0 0,rgba(0,0,0,0.75) -1.5em 0 0 0,rgba(0,0,0,0.75) -1.1em -1.1em 0 0,rgba(0,0,0,0.75) 0 -1.5em 0 0,rgba(0,0,0,0.75) 1.1em -1.1em 0 0}@-webkit-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-moz-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-o-keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes spinner{0%{-webkit-transform:rotate(0deg);-moz-transform:rotate(0deg);-ms-transform:rotate(0deg);-o-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}
</style>

<noscript>
    <div class="db measure lh-copy ph2 f3">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>

@if(!$kirby->session()->get('txn') or $txn->products()->toStructure()->count() === 0)
    <section class="f5 f4-m f3-ns black-70 db ph2">
        Your cart is empty. Would you like to look at some <a class="f5 f4-m f3-ns pa2 link black-60 hover-white hover-bg-gold" href="./">prints</a>?
    </section>
@else
    <div id="cart" class="black-70 ph2">
        <div :class="[orderWaiting == true ? 'ds' : 'dn']" class="loading fixed z-999 h2 w2 overflow-visible top-0 left-0 bottom-0 right-0">Loading&#8230;</div>
        <span class="f4 f4-m f3-ns black-70 db">@{{ step }}</span>
        <span class='db mb3'></span>

        <span  v-if="error == true" class="gold b--gold f4 f4-ns lh-copy pa2 ba border-box db mb3 tc">Sorry, there's only @{{ leftInStock }} left in stock&nbsp;<a class="ml3 link gold" href="#" v-on:click.prevent="error = false">&times;</a></span>

        <input ref="userLocation" type="hidden" value="{{ $currentLocation }}" />
        <input ref="checkoutKey" type="hidden" name="key" value="@option('stripe_key_pub')">
        <input ref="checkoutPPKey" type="hidden" name="key" value="@option('paypal_client_id')">
        <input ref="checkoutSessionID" type="hidden" name="key" value="{{ $checkoutSessionId }}">
        <input ref="checkoutTotal" type="hidden" name="total" value="{{$total}}">
        <input ref="checkoutContent" type="hidden" name="content" value="{{ $content }}">
        <input ref="ppCsrf" type="hidden" name="csrf" value="@csrf()">
        <input ref="ppEnv" type="hidden" name="csrf" value="@option('paypal_environment')">

        <transition name="fade" mode="out-in">
        <div v-if="inCart == true" key="cart">
            <div class="mw7 center dn db-ns">
                <div class="cf ph2-ns">
                    <div class="fl dn ds-ns w-10-ns db-ns">&nbsp;</div>
                    <div class="fl f3 w-50 w-60-ns pl3-ns tracked-tight">
                        description
                    </div>
                    <div class="fl dn ds-ns w-10-ns db-ns">&nbsp;</div>
                    <div class="fl f3 w-50 w-20-ns tr tracked-tight">
                        quantity
                    </div>
                </div>
            </div>
            @foreach($cartItems as $i => $item)
            <div class="mw7 center">
                <div class="cf {{ e($loop->first, 'mt4') }}">
                    <div class="fl w-20 w-10-ns">
                        @php $product = page($item->uri()) @endphp
                        <img src="{{ $product->images()->first()->crop(100)->url() }}" title="{{ $item->name() }}">
                    </div>
                    <div class="fl w-80 w-60-ns pl3">
                        <a class="black-80 pv2 f4 hover-bg-gold hover-white link" href="{{ $product->url() }}">
                        {{ $item->name() }}&nbsp;&mdash;&nbsp;{{ e($item->variant()->isNotEmpty(), $item->variant()) }}
                        </a>
                        <span class="db pt2 f6 gray">{{ $product->meta()->value() }}</span>
                    </div>
                    <div class="fl w-80 pt0-ns pt1 w-10-ns">
                        <span class="dib">CAD{{ $item->amount()->value * $item->quantity()->value }}</span>
                    </div>
                    <div class="fl w-20 w-20-ns tr-ns tc">
                        <form action="" method="post" class="dib">
                            <input type="hidden" name="csrf" value="@csrf()">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="{{ $item->id() }}">
                            <button class="b--silver ba bg-animate bg-white silver border-box f7 no-underline br-100 db h1 w1 pl1" type="submit" type="submit">x</button>
                        </form>

                        <input v-on:change="updateCart" class="b--black-20 di input-reset w-20 f5 mr0 ba tc" data-variant="{{ esc($item->variant()) }}" id="{{ $item->uri() }}::{{ $item->autoid() }}" value="{{ $item->quantity() }}" min="0" max="{{ Cart::inStock($item->id()) }}" data-sku="{{ $item->autoid() }}" data-amount="{{ $item->amount()->value() }}" data-name="{{ $item->name() }}" type="number">
                        <input ref="inputCsrf" type="hidden" name="csrf" value="@csrf()">
                    </div>
                </div>
                <hr>
            </div>
            @endforeach
            <div class="mw7 center">
                <div class="cf tr f5">
                    <div class="fl w-100 w-10-ns dn ds-ns">&nbsp;</div>
                    <div class="fl w-80 w-90-ns">discount</div>
                    @if(empty($discount))
                        <div class="fl w-20 w-10-ns">
                            <input v-model="discount" :disabled="disableDiscount" v-on:change="applyDiscount" type="text" class="b--black-20 di input-reset w-80 f5 mb2 p2 ba tc" placeholder="code" name="discount">
                            <input type="hidden" ref="discountCSRF" value="@csrf()">
                        </div>
                    @else
                        <div class="fl w-20 w-10-ns">
                            -{{ $discount['amount'] }}%
                        </div>
                    @endif
                </div>
            </div>

            <div class="mw7 center">
                <div class="cf tr f5">
                    <div class="fl w-100 w-10-ns dn ds-ns">&nbsp;</div>
                    <div class="fl w-80 w-90-ns">
                        <span>shipping</span>
                    </div>
                    <div class="fl w-20 w-10-ns">
                        <span class="tr">included</span>
                    </div>
                </div>
            </div>

            <div class="mw7 center">
                <div class="cf tr f4 pt2">
                    <div class="fl w-50 w-70-m w-70-ns">&nbsp;</div>
                    <div class="fl w-50 w-30-m w-30-ns tr">
                        <input type="hidden" ref="total" value="{{$total}}" >
                        <span>total CAD @{{ total }}</span>
                    </div>
                </div>
            </div>

            <div class="mw7 center">
                <div class="cf tr f7 pt2">
                    <div class="fl w-50 w-70-m w-70-ns">&nbsp;</div>
                    <div class="fl w-50 w-30-m w-30-ns tr">
                        <input type="hidden" ref="currencies" value="{{$currencies}}" >
                        <span>Approximately @{{ currencies }}</span><br />
                    </div>
                </div>
            </div>
            
            <button class="bg-white f5 no-underline hover-bg-gold hover-white black bg-animate b--gold pa2 pa3-l ba border-box umami--click--begin-checkout" v-on:click.prevent="showShipping">Begin checkout</button>
        </div>

        <div v-else-if="inCart == false && inShipping == true" key="address">
            <section class="mw7 center mt4">
                <div class="cf">
                    <label for="full-name" class="fl w-30 lh-copy">Full name</label>
                    <input class="measure input-reset ba b--black-20 pa2 mb2 fl w-60" v-model="name" type="text" name="full-name" required/>
                    
                    <label for="email" class="fl w-30 lh-copy">Email</label>
                    <input class="measure input-reset ba b--black-20 pa2 mb2 fl w-60" v-model="email" type="email" name="email" required/>
                </div>

                <div class="cf pt2">
                    <label for="full-name" class="fl w-30 lh-copy">Address line 1</label>
                    <input class="input-reset ba b--black-20 pa2 mb2 fl w-60" v-model="line1" type="text" required/>
                    
                    <label for="full-name" class="fl w-30 lh-copy">Address line 2</label>
                    <input class="input-reset ba b--black-20 pa2 mb2 fl w-60" v-model="line2" type="text" />
                </div>
                <div class="cf pt2">
                    <label for="city" class="fl w-30 lh-copy">City</label>
                    <input class="input-reset ba b--black-20 pa2 mb2 fl w-20-ns w-60" v-model="city" type="text" name="city" required/>
                    
                    <label for="province" class="fl w-20-ns w-30 lh-copy pl2-ns tc">Province/State</label>
                    <input class="input-reset ba b--black-20 mb2 pa2 fl w-20-ns w-60" v-model="province" type="text" name="province" required/>
                </div>
                <div class="cf pt2">
                    <label for="postcode" class="fl w-30 lh-copy">Postal Code</label>
                    <input class="input-reset ba b--black-20 pa2 mb2 fl w-20-ns w-60" v-model="postcode" type="text" name="postcode" required/>

                    <label for="country" class="fl w-20-ns w-30 lh-copy pl2-ns tc">Country</label>
                    <select class="input-reset ba b--black-20 pa2 mb2 fl w-20-ns w-60" v-model="country" name="country">
                        @foreach(countryList() as $countryName)
                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                        @endforeach
                    </select>
                </div>
            </section>

           <section class="mw7 center mt4">
                <span>By continuing to checkout, you agree to the general<a href="#" class="f5 pa1 link black-60 bb b--gold bw2" v-on:click.prevent="showTerms = !showTerms">terms</a>of the sale.</span>
                <p class="black-60 lh-copy f5" v-show="showTerms == true">{{ $site->terms() }}</p>
            </section>

            <button v-show="inCheckout == false"
                class="mt3 bg-white f5 no-underline" 
                :class="[shippingIncomplete == true ? 'gray b--gray pa2 pa3-l' : 'black bg-animate b--gold hover-bg-gold hover-white pa2 pa3-l ba border-box']"
                :disabled="shippingIncomplete" 
                v-on:click.prevent="showCheckout">Finish checkout</button>
            <input type="hidden" ref="checkoutCSRF" value="@csrf()">
        </div>
        </transition>

        <transition name="fade" mode="out-in" v-on:after-enter="initPaypal">
        <div v-if="inCart == false && inCheckout == true" key="checkout">
            <div class="mw7 center mt4">
                <div class="cf">
                    <button class="fl w-50 pa3-l pb3-l ph2 pv2 bg-white f5 no-underline black bg-animate b--gold hover-bg-gold hover-white ba border-box" v-on:click.prevent="redirectStripe">credit card checkout</button>
                    <div class="fl w-50 pb1 pb0-ns b--gold ba bg-animate bg-light-gray black border-box" id="paypal-button-container"></div>
                </div>
            </div>
        </div>
    </div>
@endif

@extends('partials.footer')
@section('scripts')
    @if(@option('env') == 'prod')
        @js('assets/dist/cart.min.js')
    @else
        @js('https://unpkg.com/axios/dist/axios.min.js')
        @js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
        @js('assets/js/cart.js')
    @endif
    @js('https://js.stripe.com/v3/', ['async' => true])
    @js('https://www.paypal.com/sdk/js?currency=CAD&client-id='.option('paypal_client_id'), ['async' => true])
@endsection