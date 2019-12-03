@include('partials.header')
@include('partials.menu')
@php use \Cart\Cart; @endphp

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
        <span class="f5 f4-m f3-ns black-70 db">@{{ step }}</span>
        <span class='db mb3'></span>

        <div v-if="error == true" class="black-70">
            <span class="gold b--gold f3 lh-copy measure pa2 pa3-l ba border-box">Sorry, there's only @{{ leftInStock }} left in stock&nbsp;<a href="#" v-on:click.prevent="error = false">&times;</a></span>
        </div>

        <input ref="userLocation" type="hidden" value="{{ $currentLocation }}" />
        <input ref="checkoutKey" type="hidden" name="key" value="@option('stripe_key_pub')">
        <input ref="checkoutPPKey" type="hidden" name="key" value="@option('paypal_client_id')">
        <input ref="checkoutSessionID" type="hidden" name="key" value="{{ $checkoutSessionId }}">
        <input ref="checkoutTotal" type="hidden" name="total" value="{{ $total }}">
        <input ref="checkoutContent" type="hidden" name="content" value="{{ $content }}">
        <input ref="ppCsrf" type="hidden" name="csrf" value="@csrf()">
        <input ref="ppEnv" type="hidden" name="csrf" value="@option('paypal_environment')">

        <transition name="fade" mode="out-in" v-on:after-enter="initPaypal">
        <div v-if="inCart == true" key="cart">
            <div class="mw9 center">
                <div class="cf ph2-ns">
                    <div class="fl w-100 w-10-ns db">&nbsp;</div>
                    <div class="fl f3 w-100 w-60-ns pl3 tracked-tight">
                        description
                    </div>
                    <div class="fl w-100 w-10-ns db">&nbsp;</div>
                    <div class="fl f3 w-100 w-20-ns tr tracked-tight">
                        quantity
                    </div>
                </div>
            </div>
            @foreach($cartItems as $i => $item)
            <div class="mw9 center">
                <div class="cf {{ e($loop->first, 'mt4') }}">
                    <div class="fl w-100 w-10-ns">
                        @php $product = page($item->uri()) @endphp
                        <img src="{{ $product->images()->first()->crop(100)->url() }}" title="{{ $item->name() }}">
                    </div>
                    <div class="fl w-100 w-60-ns pl3">
                        <a class="f4 link black-80 hover-white hover-bg-gold db" href="{{ $product->url() }}">
                        {{ $item->name() }}&nbsp;&mdash;&nbsp;{{ e($item->variant()->isNotEmpty(), $item->variant()) }}
                        </a>
                        <span class="f6 gray">{{ $product->meta()->value() }}</span>
                    </div>
                    <div class="fl w-100 w-10-ns">
                        <span class="dib">CAD{{ $item->amount()->value * $item->quantity()->value }}</span>
                    </div>
                    <div class="fl w-100 w-20-ns tr">
                        <form action="" method="post" class="dib">
                            <input type="hidden" name="csrf" value="@csrf()">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="{{ $item->id() }}">
                            <button class="bg-white f5 no-underline black bg-animate b--gray pa2 pa1-l ba border-box" type="submit">delete</button>
                        </form>

                        <input class="b--black-20 di input-reset w-20 f5 mr0 ba tc" data-variant="{{ esc($item->variant()) }}" id="{{ $item->uri() }}::{{ $item->autoid() }}" value="{{ $item->quantity() }}" min="0" max="{{ Cart::inStock($item->id()) }}" data-sku="{{ $item->autoid() }}" data-amount="{{ $item->amount()->value() }}" data-name="{{ $item->name() }}" type="number">
                        <input ref="inputCsrf" type="hidden" name="csrf" value="@csrf()">
                    </div>
                </div>
                <hr>
            </div>
            @endforeach

            <div class="mw9 center">
                <div class="cf tr f5">
                    <div class="fl w-100 w-10-ns">&nbsp;</div>
                    <div class="fl w-100 w-80-ns">
                        <span>shipping</span>
                    </div>
                    <div class="fl w-100 w-10-ns">
                        <span class="tr">included</span>
                    </div>
                </div>
            </div>

            <div class="mw9 center">
                <div class="cf tr f4">
                    <div class="fl w-100 w-10-ns">&nbsp;</div>
                    <div class="fl w-100 w-80-ns">
                        <span>total</span>
                    </div>
                    <div class="fl w-100 w-10-ns tr">
                        <span class="right">CAD{{ $total }}</span>
                    </div>
                </div>
            </div>
            
            <button class="bg-white f5 no-underline hover-bg-gold hover-white black bg-animate b--gold pa2 pa3-l ba border-box" v-on:click.prevent="showShipping">Begin checkout</button>
        </div>

        <div v-else-if="inCart == false && inShipping == true" key="address">
            <section class="mw9 center">
                <div class="cf">
                    <label>Full name
                        <input class="measure input-reset ba b--black-20 pa2 mb2 dib w-40" v-model="name" type="text" required/>
                    </label>
                    <label>Email
                        <input class="measure input-reset ba b--black-20 pa2 mb2 dib w-40" v-model="email" type="email" required/>
                    </label>
                </div>
            </section>
            <section class="mw9 center">
                <div class="cf">
                    <fieldset>
                        <legend class="pb2">Address</legend>
                        <div class="pt2 db">
                            <label>Address line 1
                                <input class="input-reset ba b--black-20 pa2 mb2 dib w-100" v-model="line1" type="text" required/>
                            </label>
                            <label>Address line 2
                                <input class="input-reset ba b--black-20 pa2 mb2 dib w-100" v-model="line2" type="text" />
                            </label>
                        </div>
                        <div class="pt2 db">
                            <label>City
                                <input class="input-reset ba b--black-20 pa2 mb2 dib w-20" v-model="city" type="text" required/>
                            </label>
                            <label class="pl2-ns">Province/State
                                <input class="input-reset ba b--black-20 pa2 mb2 dib w-20" v-model="province" type="text" required/>
                            </label>
                            <label class="pl2-ns">Postal Code
                                <input class="input-reset ba b--black-20 pa2 mb2 dib w-20" v-model="postcode" type="text" required/>
                            </label>
                        </div>
                        <div class="pt2 db">
                            <label>Country
                                <select class="input-reset ba b--black-20 pa2 mb2 dib w-40" v-model="country" name="country">
                                    @foreach(countryList() as $countryName)
                                        <option value="{{ $countryName }}">{{ $countryName }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </section>
            <button 
                class="mt3 bg-white f5 no-underline" 
                :class="[shippingIncomplete == true ? 'gray b--gray pa2 pa3-l' : 'black bg-animate b--gold hover-bg-gold hover-white pa2 pa3-l ba border-box']"
                :disabled="shippingIncomplete" 
                v-on:click.prevent="showCheckout">Finish checkout</button>
            <input type="hidden" ref="checkoutCSRF" value="@csrf()">
        </div>

        <div v-else="inCart == false && inCheckout == true" key="checkout">
            <div class="row">
                <div class="small-12 medium-12 columns low-contrast text-right" id="currencies">
                    <span>Approximately {{ $currencies }}</span><br />
                    <span>By continuing to checkout, you agree to the general<a v-on:click.prevent="showTerms = !showTerms">terms</a>of the sale.</span>
                    <p v-show="showTerms == true">{{ $site->terms() }}</p>
                </div>

                <div>
                    <div class="row">
                        <div class="small-6 medium-6 columns text-right">
                            <button class="right" v-on:click.prevent="redirectStripe">credit card checkout <div id="card"></div></button>
                        </div>
                        <div class="small-6 medium-6 columns"><div id="paypal-button-container"></div></div>
                    </div>
                </div>
            </div>
        </div>
        </transition>
    </div>


@endif

@include('partials.footer')
@if(@option('env') == 'prod')
    @js('assets/js/prod/cart.min.js')
@else
    @js('https://unpkg.com/axios/dist/axios.min.js')
    @js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
    @js('assets/js/cart.js')
@endif
@js('https://js.stripe.com/v3/', ['async' => true])
@js('https://www.paypal.com/sdk/js?currency=CAD&client-id='.option('paypal_client_id'), ['async' => true])