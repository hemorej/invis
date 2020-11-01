@include('partials.header')
@include('partials.menu')

  <noscript>
    <div class="db measure lh-copy ph2">
      <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
  </noscript>

  <div class="black-70 ph2">
    <span class="f4 f3-ns black-70 db mb3">{{ $page->parent()->title() | lower }}&nbsp;<a class="f4 f3-ns link black-60 hover-white hover-bg-gold pa1" href="{{ $page->url() }}">{{ $page->title() | lower }}</a></span>


    @if(@get('canceled'))
    <div class="measure black-70 f4 f3-l ph2 mt4">
      <h4>Subscription cancelled</h4>
    </div>
    <span class='db mb2'></span>
    <div class="black-70 f4 f3-ns ph2 measure-wide lh-copy">
      Your transaction was cancelled. Go back to the <a class="pa1-l link black-60 hover-white hover-bg-gold" href="/prints">store</a>
    </div>
    @elseif(@get('success'))
    <div class="measure black-70 f4 f4-ns f3-l ph2 mt4">
        <h4>Subscription confirmation</h4>
    </div>
    <span class='db mb2'></span>
    <div class="black-70 f4 f4-ns ph2 measure-wide lh-copy">
      <span class="db">Thank you for your support</span>
        You will receive an email confirmation shortly. If you have questions about your subscription contact us at &#x6A;&#x65;&#x72;&#x6F;&#x6D;&#x65;&#x40;&#x74;&#x68;&#x65;&#x2D;&#x69;&#x6E;&#x76;&#x69;&#x73;&#x69;&#x62;&#x6C;&#x65;&#x2D;&#x63;&#x69;&#x74;&#x69;&#x65;&#x73;&#x2E;&#x63;&#x6F;&#x6D;. If would like to make changes, use the Manage Subscription section on the <a class="pa1-l link black-60 hover-white hover-bg-gold" href="{{$page->url()}}">subscription page</a>
    </div>
    @else
      <div id="sub">
        <input ref="checkoutKey" type="hidden" name="key" value="@option('stripe_key_pub')">
        @foreach($page->tiers()->toStructure() as $tier)
          <div class="mw8 center db">
            <div class="flex flex-column flex-row-l">
              <div class="w-50-ns w-100-m f3-ns pr4-ns pb4-ns tracked-tight">
                @php
                  $image = $page->image($tier->image()->toFile()->filename())
                @endphp
                <img class="db" alt="product pictures for {{ $page->title() }}" srcset="{{ $image->srcset([600, 800, 1200]) }}">
              </div>
              <div class="flex flex-column w-50-ns w-100-m">
                <div class="w-70-ns w-100-m pl2-ns f3-ns pt3 pt0-ns f4 tracked-tight">
                  {{$tier->description()}}
                </div>
                <button @click.prevent="subscribe" data-shipping='{{$tier->require_shipping()}}' data-plan-id='{{$tier->plan_id()}}' class="w-70-ns w-100-m fr bg-white f4 no-underline black hover-white bg-animate b--gold ba pa2 ml2-ns ml0-m border-box hover-bg-gold mt4">${{$tier->price()}} <span class="mt2 f6">per month</span></button>
              </div>
            </div>
          </div>
        <hr class="mb4-ns"/>
        @endforeach
        <div class="mw8 center db">
          <div class="cf">
            <div class="fl f3 w-90-ns w-100 tracked-tight">
              <button v-on:click.prevent='showManage()' class="w-100 w-50-ns bg-white f5 f4-ns no-underline black bg-animate b--gold pa3-ns pa2 ba border-box"><span>Manage your subscription</span></button>
            </div>
            <transition name="fade" mode="out-in">
              <div v-show="manage == true" class="mt2 fl w-100">
                <input class="measure input-reset ba b--black-20 pa2 fl w-90-ns w-70" placeholder="Enter the email you used when you subscribed" v-model="email" type="email" name="email" required/>
                <input type="hidden" ref="inputCsrf" value="@csrf()">
                <button 
                  :disabled="!validEmail(email)" 
                  @click.prevent='redirectManage(email)' 
                  :class="[validEmail(email) == false ? 'fr gray b--gray f5 no-underline black pa2 ml2 ba border-box' : 'fr bg-white f5 no-underline black bg-animate b--gold pa2 ml2 ba border-box hover-bg-gold']">
                    <span>Manage</span>
                </button>
              </div>
            </transition>
          </div>
        </div>
    </div>
    @endif
  </div>

  <span class="cf db mt4"></span>
  <nav class="cf mt4 ph2">
      @php
          $articles = $page->siblings()->listed()->flip();
      @endphp

      @if($page->hasPrevListed($articles))
          <p class="fl">
              <a class="pa1-l f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="{{ $page->prev($articles)->url() }}">&laquo; {{ $page->prev($articles)->title() }}</a>
          </p>
      @endif

      @if($page->hasNextListed($articles))
          <p class="fr">
              <a class="pa1-l f5 f4-m f4-ns link black-60 hover-white hover-bg-gold" href="{{ $page->next($articles)->url() }}">{{ $page->next($articles)->title() }} &raquo;</a>
          </p>
      @endif
  </nav>

     @extends('partials.footer')
     @section('scripts')
        @js('https://js.stripe.com/v3/')
        @if(@option('env') == 'prod')
            @js('assets/dist/subs.min.js')
        @else
            @js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
            @js('https://unpkg.com/axios/dist/axios.min.js')
            @js('assets/js/subs.js')
        @endif
    @endsection
  </body>
</html>
