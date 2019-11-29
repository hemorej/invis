@snippet('header')
@snippet('menu')
@php use \Cart\Cart; @endphp

<div class="alert-box row">
    <div class="medium-12 columns">
      <h2>Sorry, there's only <span id="stock-error"></span> left in stock</h2><a href="#" class="close">&times;</a>
    </div>
</div>

<noscript>
<div class="alert-box row" style="display:block">
    <div class="medium-12 columns">
      <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</div>
</noscript>

<div class="row medium-space-top">
    <h3><span class="high-contrast">
        {{ $page->title()->html() }}
    </h3></span>

@kirbytext($page->text())

@if(!$kirby->session()->get('txn') or $txn->products()->toStructure()->count() === 0)
    <section class="small-12 medium-8 columns">
        <article>
            Your cart is empty. Would you like to look at some<a href="./">prints</a>?
        </article>
    </section>
@else
    <div id="cart">
        <input type="hidden" ref="userLocation" value="{{ $currentLocation }}" />
        <transition name="fade" mode="out-in" v-on:after-enter="initPaypal">
        <div v-if="inCart == true" key="cart">
            <div class="row show-for-landscape">
                <div class="small-2 medium-2 columns">&nbsp;</div>
                <div class="small-8 medium-8 columns">description</div>
                <div class="small-2 medium-2 columns text-right">quantity</div>
            </div>
            @foreach($cartItems as $i => $item)
                <div class="show-for-landscape">
                    <div class="row cart {{ e($loop->first, 'medium-space-top') }}">
                        <div class="small-2 medium-2 columns">
                            @php $product = page($item->uri()) @endphp
                            <img src="{{ $product->images()->first()->resize(100, 100, 90)->url() }}" title="{{ $item->name() }}">
                        </div>
                        <div class="small-8 medium-6 columns">
                            <a class="cart-prod" href="{{ $product->url() }}">
                            {{ $item->name() }}&nbsp;&mdash;&nbsp;{{ e($item->variant()->isNotEmpty(), $item->variant()) }}
                            </a><br />
                            <span class="meta">{{ $product->meta()->value() }}</span>
                        </div>
                        <div class="small-2 medium-4 columns">
                            <span class="right">CAD{{ $item->amount()->value * $item->quantity()->value }}</span>
                            <br class="show-for-small-only"/>
                            <form action="" method="post">
                                <input type="hidden" name="csrf" value="@csrf()">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="{{ $item->id() }}">
                                <button class="right show-for-small-only" type="submit">x</button>
                                <button class="hide-for-small" type="submit">delete</button>
                            </form>

                            <input class="input-qty" data-variant="{{ esc($item->variant()) }}" id="{{ $item->uri() }}::{{ $item->autoid() }}" value="{{ $item->quantity() }}" min="0" max="{{ Cart::inStock($item->id()) }}" data-sku="{{ $item->autoid() }}" data-amount="{{ $item->amount()->value() }}" data-name="{{ $item->name() }}" type="number">
                            <input id="input-csrf" type="hidden" name="csrf" value="@csrf()">
                        </div>
                    </div>
                    <hr>
                </div>
            @endforeach

            <div class="row medium-space-top">
                <div class="small-8 medium-10 columns text-right">
                    <span>shipping</span>
                </div>
                <div class="small-2 medium-2 columns">
                    <span class="right">included</span>
                </div>
            </div>

            <div class="row">
                <div class="small-8 medium-10 columns text-right">
                    <h2>total</h2>
                </div>
                <div class="small-4 medium-2 columns text-right">
                    <h2 class="right">CAD{{ $total }}</h2>
                </div>
            </div>
            
            <button @click="showShipping()">Begin checkout</button>
        </div>

        <div v-else-if="inCart == false && inShipping == true" key="address">
            <label>Full name
                <input v-model="name" type="text" required/>
            </label>
            <label>Email
                <input v-model="email" type="email" required/>
            </label>
            <fieldset>
                <legend>Address</legend>
                <label>Address line 1
                    <input v-model="line1" type="text" required/>
                </label>
                <label>Address line 2
                    <input v-model="line2" type="text" />
                </label>
                <label>City
                    <input v-model="city" type="text" required/>
                </label>
                <label>Province / State
                    <input v-model="province" type="text" required/>
                </label>
                <label>Postal Code
                    <input v-model="postcode" type="text" required/>
                </label>
                <label>Country
                    <select v-model="country" name="country">
                        @foreach(countryList() as $countryName)
                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                        @endforeach
                    </select>
                </label>
            </fieldset>
            <button :disabled="shippingIncomplete" @click="showCheckout()">Finish checkout</button>
            <input type="hidden" ref="checkoutCSRF" value="@csrf()">
        </div>

        <div v-else="inCart == false && inCheckout == true" key="checkout">
            <div class="row">
                <div class="small-12 medium-12 columns low-contrast text-right" id="currencies">
                    <span>Approximately {{ $currencies }}</span><br />
                    <span>By continuing to checkout, you agree to the general<a id="terms" href="#">terms</a>of the sale.</span>
                    <p id="term-details">{{ $site->terms() }}</p>
                </div>

                <div>
                    <div class="row">
                        <div class="small-6 medium-6 columns text-right">
                            <button class="right" @click="redirectStripe">credit card checkout <div id="card"></div></button>
                        </div>
                        <div class="small-6 medium-6 columns"><div id="paypal-button-container"></div></div>
                    </div>
                </div>
            </div>
        </div>
        </transition>
    </div>

    <input id="checkout-key" type="hidden" name="key" value="@option('stripe_key_pub')">
    <input id="checkout-pp-key" type="hidden" name="key" value="@option('paypal_client_id')">
    <input id="checkout-session-id" type="hidden" name="key" value="{{ $checkoutSessionId }}">
    <input id="checkout-total" type="hidden" name="total" value="{{ $total }}">
    <input id="checkout-content" type="hidden" name="content" value="{{ $content }}">
    <input id="pp-csrf" type="hidden" name="csrf" value="@csrf()">
    <input id="pp-env" type="hidden" name="csrf" value="@option('paypal_environment')">

@endif

@snippet('footer')
@js('https://unpkg.com/axios/dist/axios.min.js')
@if(@option('env') == 'prod')
    @js('https://cdn.jsdelivr.net/npm/vue')
    @js('assets/js/cart.min.js')
@else
    @js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
    @js('assets/js/cart.js')
@endif
@js('https://js.stripe.com/v3/')
@js('https://www.paypal.com/sdk/js?currency=CAD&client-id='.option('paypal_client_id'), ['async' => true])