<?php
    use \Cart\Cart;

	$page = page();
    $image = $page->images()->first()->resize(null, 600);

    if( $page->title() == $page->uid()){
        $title = $page->parent()->slug();
    } else {
	    $title = $page->title();
    }

    // Books live under the prints section but present themselves as "books".
    $parentLabel = $page->type()->value() === 'book' ? 'books' : $page->parent()->title();

	$structuredData = [
        "@context" => "http://schema.org/",
        "@type" => "Product",
        "name" => $page->title()->toString(),
        "image" => $image->url(),
        "description" => $page->meta()->toString()
    ];
?>

<?php snippet('partials/header') ?>

<?php snippet('partials/menu') ?>

<?php if($page->type()->value() === 'print'): ?>
<noscript>
    <div class="spectral f-18 lh-17">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>
<?php endif ?>

<div id="prod"<?= $page->type()->value() === 'print' ? ' x-data="product"' : '' ?>>
    <div class="mb-48">
        <span class="spectral f-23 fw5 ink-dark ttl">
            <a class="lnk-nav spectral f-23 ttl"
                href="<?= page()->parent()->url() ?>"><?= html($parentLabel) ?></a>
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
                                        class="variant-lnk spectral f-19 <?= $loopIdx === 0 ? 'variant-on' : 'variant-off' ?>"
                                        data-option-variant='<?= html($variant->suuid()) ?>'
                                        data-option-product='<?= html($page->title() . $variant->name()) ?>'
                                        @click.prevent="makeActive($event)">
                                        <span><?= html($variant->name()) ?></span>
                                        <span class="ink-subtle">$<?= html($variant->price()) ?></span>
                                    </a>
                                <?php endif ?>
                            <?php $loopIdx++; endforeach ?>
                        </div>

                        <form id="cart-form" method="post" action="">
                            <input type="hidden" name="csrf" x-ref="csrf" value="<?= csrf() ?>">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="uri" x-ref="uri" value="<?= html($page->uri()) ?>">
                            <button
                                :disabled="submitting"
                                @click.prevent="addToCart()"
                                class="btn-cart"
                                :class="submitting ? 'ink-subtle' : ''">
                                <span x-show="submitting" x-cloak>adding&ensp;&hellip;</span>
                                <span x-show="!submitting">add to cart</span>
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
                <p class="ttl spectral f-19 lh-14 ink-subtle mb-34 mt0"><?= html($parentLabel) ?></p>

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
            <?= js('assets/dist/product.min.js', ['defer' => true]) ?>
            <?= js('assets/dist/alpine.min.js', ['defer' => true]) ?>
        <?php else: ?>
            <?= js('assets/js/product.js', ['defer' => true]) ?>
            <?= js('assets/js/vendor/alpine.min.js', ['defer' => true]) ?>
        <?php endif ?>
    <?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
