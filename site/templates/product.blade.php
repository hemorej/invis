@php 
    use \Cart\Cart;
    
    $image = $page->images()->first()->resize(null, 600);
    $title = $page->title();
    if( $page->title() == $page->uid())
        $title = $page->parent()->slug();

    $meta = array('url' => $page->url(), 'image' => $image->url());
@endphp

@include('partials.header', ['meta' => $meta])
@include('partials.menu')

<noscript>
    <div class="db measure lh-copy ph2">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>

<div id="prod" class="black-70 ph2">
    <span class="f5 f4-m f3-ns black-70 db mb3">{{ $page->parent()->title() | lower }}&nbsp;<a class="f5 f4-m f3-ns link black-60 hover-white hover-bg-gold pa1" href="{{ $page->url() }}">{{ $page->title() | lower }}</a></span>

    <div class="mw9 center">
        <div class="cf">
            <div class="fl w-100 w-60-ns">
                <carousel :per-page="1" :pagination-size="4" :adjustable-height="false" :loop="true">
                    @foreach($page->images() as $image)
                        <slide><img class="db" alt="product pictures for {{ $page->title() }}" srcset="{{ $image->srcset([600, 800, 1200]) }}"></slide>
                    @endforeach
                </carousel>
            </div>
        
            <div class="fl w-100 w-40-ns pa4-ns">
                <section class="variants">
                    @php
                    $variants = $page->variants()->toStructure();

                    $stock = 0;
                    foreach($variants as $variant)
                        $stock += $variant->stock()->value();

                    @endphp
                    @if(count($variants) == 0 || $stock == 0)
                        Out of stock
                    @else
                        <ul class="list pv2 pl0">
                            @foreach($variants as $variant)
                                @if(Cart::inStock($variant))
                                    <li class='{{ e($loop->first, 'dib pl0', 'dib pt3') }}'>
                                    <a {{ e($loop->first, 'ref="active"') }} 
                                    class="f4 link black-60 hover-white hover-bg-gold pa1-l {{ e($loop->first, 'bb b--gold bw2')}}"
                                    data-option-variant='{{ $variant->autoid() }}'
                                    data-option-product='{{ $page->title() . $variant->name() }}'
                                    v-on:click.prevent='makeActive'>
                                        {{ $variant->name() }} &mdash; ${{ $variant->price() }}
                                    </a>
                                    </li>&nbsp;
                                @endif
                            @endforeach
                        </ul>

                        <form id="cart-form" method="post" action="">
                            <div class="description">
                                <input type="hidden" name="csrf" ref="csrf" value="@csrf()">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="uri" ref="uri" value="{{ $page->uri() }}">
                            </div>

                            <div class="action mb4">
                                <button 
                                    :disabled="submitting == true"
                                    v-on:click.prevent='addToCart'
                                    class="bg-white f5 no-underline" 
                                    :class="[submitting == true ? 'gray b--gray pa2 pa3-l' : 'black bg-animate b--gold pa2 pa3-l ba border-box']">
                                    <span v-if="submitting == true">adding&ensp;&hellip;</span>
                                    <span v-else>add to cart</span>
                                </button>
                            </div>
                        </form>
                        <span class="measure-narrow lh-copy black-70 f4">
                            {{ $page->description() }}
                        </span>
                    @endif
                </section>
            </div>
        </div>
    </div>
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
    @if(@option('env') == 'prod')
        @js('assets/dist/product.min.js')
    @else
        @js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
        @js('https://cdn.jsdelivr.net/npm/vue-carousel@0.18.0/dist/vue-carousel.min.js')
        @js('https://unpkg.com/axios/dist/axios.min.js')
        @js('assets/js/product.js')
    @endif
@endsection