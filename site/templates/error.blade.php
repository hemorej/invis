@include('partials.header')
@include('partials.menu')

<div class="row medium-space-top">
    <section class="small-12 small-centered medium-12 columns">
      <article>
        @kirbytext($page->text())
      </article>
    </section>
</div>

@include('partials.footer')