@include('partials.header')
@include('partials.menu')

<section class="measure black-70 ma3 ma4-ns f3-ns f4-m f4">
  <article>
    @kirbytext($page->text())
  </article>
</section>

@include('partials.footer')