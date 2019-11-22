@css("//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick.css")
@css("//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick-theme.css")

@php 
    $image = $page->images()->first()->resize(null, 600);
    $title = $page->title();
    if( $page->title() == $page->uid())
        $title = $page->parent()->slug();

    $meta = array('url' => $page->url(), 'image' => $image->url());
@endphp

@snippet('header', array('meta' => $meta))
@snippet('menu')

<noscript>
<div class="alert-box row" style="display:block">
    <div class="medium-12 columns">
      <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</div>
</noscript>

@php 
    if($page->parent()->title() != 'journal'){
        $headline = $page->title()->lower();
    }else{
        $published = $page->published()->toString();
        if(!empty($published)){
           if(strpos($published, ',') != false){
                $headline = $published ;
            }else{
                $headline = date('F d, Y', strtotime($published));
            }
        }else if( $page->title() != $page->uid()){
            $headline = "_".$page->title()->lower();
        }
    }
@endphp

<div class="row medium-space-top">
    <h3><span class="high-contrast">{{ $page->parent()->title() | lower }}</span><a href="{{ $page->url() }}">{{ $headline | lower }}</a></h3>
    <div class="small-12 medium-8 columns">
        <div class="slick">
            @foreach($page->images() as $image)
                <div><img src="{{ $image->url() }}"></div>
            @endforeach
        </div>
    </div>
        <section class="small-12 medium-4 columns variants">
            @php
                $variants = $page->variants()->toStructure();

                $stock = 0;
                foreach($variants as $variant){
                    $stock += $variant->stock()->value();
                }
            @endphp
            @if(count($variants) == 0 || $stock == 0):
                'Out of stock'
            @else
                <ul class="inline-list">
                @php
                    $first = true;
                    $activeSku = 0;
                @endphp
                @foreach ($variants as $variant)
                    @if(inStock($variant))
                        <li {{ $first == true ? 'class="active variant"' : 'class="variant"' }}>
                            <a href="#" data-option-variant='{{ $variant->sku() }}' data-option-price="{{ $variant->price }}">{{ $variant->name() }} &mdash; ${{ $variant->price }}</a>
                        </li>&nbsp;
                        @php 
                            if($first == true){
                                $activeSku = $variant->sku();
                                $first = false;
                            }
                        @endphp
                    @endif
                @endforeach
                </ul>

                <form id="cart-form" method="post" action="">
                    <div class="description">
                        <input type="hidden" name="csrf" value="@csrf()">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="uri" value="{{ $page->uri() }}">
                        <input type="hidden" name="variant" value='{{ $activeSku }}'>
                    </div>

                    <div class="action">
                        <button id="add-cart" type="submit">add to cart</button>
                    </div>
                </form>
                @kirbytext($page->description())
            @endif
        </section>
    </div>
    <div class="row medium-space-top">
        <div class="small-12 medium-12 columns">
        @if($page->hasPrevListed())
            <span class="left">
                <a href="<?= $page->prev()->url() ?>">&laquo; <?= $page->prev()->title() ?></a>
            </span>
        @endif
        @if($page->hasNextListed())
            <span class="right">
                <a href="<?= $page->next()->url() ?>"><?= $page->next()->title() ?> &raquo;</a>
            </span>
        @endif
        </div>
    </div>  
</div>

@snippet('footer')

@js("//cdn.jsdelivr.net/gh/kenwheeler/slick/slick/slick.min.js")
<script type="text/javascript">
$( document ).ready(function() {
    $('.slick').slick({
      dots: true,
      arrows: true,
      infinite: true,
      speed: 300,
      slidesToShow: 1,
      adaptiveHeight: true
    });
});
</script>
@if(@option('env') == 'prod')
    @js('assets/js/vendor/cart.min.js')
@else
    @js('assets/js/vendor/cart.js')
@endif
