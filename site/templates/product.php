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
    <meta property="product:price.amount" content="<?= html(page()->variants()->toStructure()->first()->price()) ?>">
    <meta property="product:price.currency" content="CAD">
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('partials/menu') ?>

<noscript>
    <div class="spectral f-18 lh-17">
        <h2>This page requires Javascript, please enable it and try again</h2>
    </div>
</noscript>

<div id="prod">
    <div class="mb-34">
		<span class="spectral f-23 fw5 ink-dark ttl"><?= html($page->parent()->title()) ?></span>
		<span class="spectral f-23 ink-subtle ttl">&nbsp;<?= html($page->title()) ?></span>
	</div>

    <div class="flex flex-wrap items-start gap-60">
        <div style="flex:1;min-width:300px;display:flex;flex-direction:column;gap:28px;">
            <?php foreach($page->images() as $img): ?>
                <img class="db w-100" alt="product pictures for <?= html($page->title()) ?>" srcset="<?= html($img->srcset([600, 800, 1200])) ?>">
            <?php endforeach ?>
        </div>

        <div style="flex:1;min-width:280px;">
            <section class="variants">
                <?php
                $variants = $page->variants()->toStructure();
                $stock = 0;
                foreach($variants as $variant)
                    $stock += $variant->stock()->value();
                ?>
                <?php if(count($variants) == 0 || $stock == 0): ?>
                    <span class="spectral f-18 ink-muted">Out of stock</span>
                <?php else: ?>
                    <ul class="list pv2 pl0">
                        <?php $loopIdx = 0; foreach($variants as $variant): ?>
                            <?php if(Cart::inStock($variant)): ?>
                                <li class="<?= $loopIdx === 0 ? 'dib pl0' : 'dib pt3' ?>">
                                <a
                                    <?php if($loopIdx === 0): ?>ref="active"<?php endif ?>
                                    class="spectral f-19 no-underline ink-body <?= $loopIdx === 0 ? 'bb bw1 b--gold pb-10' : 'ink-subtle' ?>"
                                    data-option-variant='<?= html($variant->suuid()) ?>'
                                    data-option-product='<?= html($page->title() . $variant->name()) ?>'
                                    v-on:click.prevent='makeActive'>
                                    <?= html($variant->name()) ?> &mdash; $<?= html($variant->price()) ?>
                                </a>
                                </li>&nbsp;
                            <?php endif ?>
                        <?php $loopIdx++; endforeach ?>
                    </ul>

                    <form id="cart-form" method="post" action="">
                        <input type="hidden" name="csrf" ref="csrf" value="<?= csrf() ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="uri" ref="uri" value="<?= html($page->uri()) ?>">
                        <div class="mt3">
                            <button
                                :disabled="submitting == true"
                                v-on:click.prevent='addToCart'
                                class="btn-cart"
                                :class="[submitting == true ? 'ink-subtle' : '']">
                                <span v-if="submitting == true">adding&ensp;&hellip;</span>
                                <span v-else>add to cart</span>
                            </button>
                        </div>
                    </form>
                    <div class="spectral f-19 ink-body mb-28"><?= html($page->title()) ?>, <?= html($page->parent()->title()) ?></div>
                    <div class="spectral f-19 ink-subtle mb-28">—</div>
                    <p class="spectral f-18 lh-165" style="color:#777;max-width:46ch;margin:0;">
                        <?= $page->description()->kirbytext() ?>
                    </p>
                <?php endif ?>
            </section>
        </div>
    </div>
</div>

<nav class="flex items-baseline mt-56" style="justify-content:space-between;">
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
    <?php if(@option('env') == 'prod'): ?>
        <?= js('assets/dist/product.min.js') ?>
    <?php else: ?>
        <?= js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js') ?>
        <?= js('https://unpkg.com/axios/dist/axios.min.js') ?>
        <?= js('assets/js/product.js') ?>
    <?php endif ?>
<?php endslot() ?>
<?php endsnippet() ?>
