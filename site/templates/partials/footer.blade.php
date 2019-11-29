@if(!isset($noCopyright))
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-12 medium-centered columns low-contrast">
        @kirbytext(@html($site->copyright()))
      </footer>
    </div>
@endif

@js('assets/js/min.js')
@include('partials.ga')
</body>

</html>