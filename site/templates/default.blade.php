@include('partials.header')
@include('partials.menu')

<section class="measure black-70 f3 f3-m f3-ns ph2">
    <article>
        @kirbytext($page->text())
    </article>
</section>

@include('partials.footer')