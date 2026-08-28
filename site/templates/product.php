<?php
    use \Cart\Cart;

	$page = page();
    $image = $page->images()->first()->resize(null, 600);

    if( $page->title() == $page->uid()){
        $title = $page->parent()->slug();
    } else {
	    $title = $page->title();
    }

	$structuredData = [
        "@context" => "http://schema.org/",
        "@type" => "Product",
        "name" => $page->title()->toString(),
        "image" => $image->url(),
        "description" => $page->meta()->toString()
    ];
?>

<?php snippet('partials/header', [], false, true) ?>
<?php slot('meta') ?>
    <meta property="og:type" content="product">
    <meta property="og:title" content="<?= html($title) ?>">
    <meta property="og:url" content="<?= html(page()->url()) ?>">
    <meta property="og:image" content="<?= html($image->url()) ?>">
    <meta property="og:description" content="<?= html($page->meta()->toString()) ?>">
    <?php if($page->type()->value() === 'print'): ?>
    <meta property="product:price.amount" content="<?= html(page()->variants()->toStructure()->first()->price()) ?>">
    <meta property="product:price.currency" content="CAD">
    <?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<?php if($page->type()->value() === 'print'): ?>
<noscript>
    <div class="spectral f-18 lh-17">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>
<?php endif ?>

<div id="prod">
    <div class="mb-48">
        <span class="spectral f-23 fw5 ink-dark ttl">
            <a class="lnk-nav spectral f-23 ttl"
                href="<?= page()->parent()->url() ?>"><?= html(page()->parent()->title()) ?></a>
        </span>
        <span class="spectral f-23 ink-light">&nbsp;&nbsp;/&nbsp;&nbsp;</span>
        <span class="spectral f-23 ink-subtle ttl"><?= html($page->title()) ?></span>
    </div>

    <div class="flex flex-wrap items-start gap-72">
        <div style="flex:1.05;min-width:300px;display:flex;flex-direction:column;gap:24px;">
            <?php foreach($page->images() as $img): ?>
                <img class="db w-100" alt="product pictures for <?= html($page->title()) ?>" width="<?= $img->width() ?>" height="<?= $img->height() ?>" sizes="(min-width: 700px) 50vw, 100vw" srcset="<?= html($img->srcset([600, 800, 1200])) ?>" style="filter:none;height:auto">
            <?php endforeach ?>
        </div>

        <div style="flex:1;min-width:300px;position:sticky;top:48px;align-self:flex-start;">
            <?php if($page->type()->value() === 'print'): ?>
                <?php
                $variants = $page->variants()->toStructure();
                $stock = 0;
                foreach($variants as $variant)
                    $stock += $variant->stock()->value();
                ?>

                <h1 class="spectral fw3 f-38 lh-108 ink-dark ls-tight mt0 mb-4"><?= html($page->title()) ?></h1>
                <p class="ttl spectral f-19 lh-14 ink-subtle mb-34 mt0"><?= html($page->parent()->title()) ?></p>

                <?php if(count($variants) == 0 || $stock == 0): ?>
                    <span class="spectral f-18 ink-muted">Out of stock</span>
                <?php else: ?>
                    <section class="variants">
                        <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:32px;">
                            <?php $loopIdx = 0; foreach($variants as $variant): ?>
                                <?php if(Cart::inStock($variant)): ?>
                                    <a
                                        <?php if($loopIdx === 0): ?>ref="active"<?php endif ?>
                                        class="variant-lnk spectral f-19 <?= $loopIdx === 0 ? 'variant-on' : 'variant-off' ?>"
                                        data-option-variant='<?= html($variant->suuid()) ?>'
                                        data-option-product='<?= html($page->title() . $variant->name()) ?>'
                                        v-on:click.prevent='makeActive'>
                                        <span><?= html($variant->name()) ?></span>
                                        <span class="ink-subtle">$<?= html($variant->price()) ?></span>
                                    </a>
                                <?php endif ?>
                            <?php $loopIdx++; endforeach ?>
                        </div>

                        <form id="cart-form" method="post" action="">
                            <input type="hidden" name="csrf" ref="csrf" value="<?= csrf() ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="uri" ref="uri" value="<?= html($page->uri()) ?>">
                            <button
                                :disabled="submitting == true"
                                v-on:click.prevent='addToCart'
                                class="btn-cart"
                                :class="[submitting == true ? 'ink-subtle' : '']">
                                <span v-if="submitting == true">adding&ensp;&hellip;</span>
                                <span v-else>add to cart</span>
                            </button>
                        </form>
                    </section>

                    <div class="bt-rule mt-38 mb-30"></div>

                    <div class="prod-desc">
                        <?= $page->description()->kirbytext() ?>
                    </div>

                    <?php
                    $attrs = [];
                    if ($page->edition()->isNotEmpty())   $attrs['edition']  = $page->edition();
                    if ($page->paper()->isNotEmpty())     $attrs['paper']    = $page->paper();
                    if ($page->process()->isNotEmpty())   $attrs['process']  = $page->process();
                    ?>
                    <?php if (!empty($attrs)): ?>
                    <div class="prod-attrs">
                        <?php foreach ($attrs as $label => $value): ?>
                            <span class="prod-attr-lbl"><?= html($label) ?></span>
                            <span class="prod-attr-val"><?= html($value) ?></span>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>
                <?php endif ?>
            <?php else: ?>
                <?php /* Books are display-only: no variants, stock or purchase action. */ ?>
                <h1 class="spectral fw3 f-38 lh-108 ink-dark ls-tight mt0 mb-4"><?= html($page->title()) ?></h1>
                <p class="ttl spectral f-19 lh-14 ink-subtle mb-34 mt0"><?= html($page->parent()->title()) ?></p>

                <div class="prod-desc">
                    <?= $page->description()->kirbytext() ?>
                </div>

                <?php
                $attrs = [];
                if ($page->edition()->isNotEmpty())    $attrs['edition']    = $page->edition();
                if ($page->dimensions()->isNotEmpty()) $attrs['dimensions'] = $page->dimensions();
                if ($page->paper()->isNotEmpty())      $attrs['paper']      = $page->paper();
                if ($page->binding()->isNotEmpty())    $attrs['binding']    = $page->binding();
                ?>
                <?php if (!empty($attrs)): ?>
                <div class="prod-attrs">
                    <?php foreach ($attrs as $label => $value): ?>
                        <span class="prod-attr-lbl"><?= html($label) ?></span>
                        <span class="prod-attr-val"><?= html($value) ?></span>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</div>

<nav class="flex items-baseline mt-72" style="justify-content:space-between;">
    <?php $articles = $page->siblings()->listed()->flip(); ?>
    <div>
        <?php if($page->hasPrevListed($articles)): ?>
            <a class="lnk-nav spectral f-19 ttl" href="<?= html($page->prev($articles)->url()) ?>">&laquo; <?= html($page->prev($articles)->title()) ?></a>
        <?php endif ?>
    </div>
    <div>
        <?php if($page->hasNextListed($articles)): ?>
            <a class="lnk-nav spectral f-19 ttl" href="<?= html($page->next($articles)->url()) ?>"><?= html($page->next($articles)->title()) ?> &raquo;</a>
        <?php endif ?>
    </div>
</nav>

<?php snippet('partials/footer', ['ldjson' => $structuredData], false, true) ?>
<?php slot('scripts') ?>
    <?php if($page->type()->value() === 'print'): ?>
        <?php if(@option('env') == 'prod'): ?>
            <?= js('assets/dist/product.min.js') ?>
        <?php else: ?>
            <?= js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js') ?>
            <?= js('https://unpkg.com/axios/dist/axios.min.js') ?>
            <?= js('assets/js/product.js') ?>
        <?php endif ?>
    <?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
