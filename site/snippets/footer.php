<?php if (!isset($noCopyright)): ?>
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-10 medium-offset-2 medium-centered columns low-contrast">
        <?php echo kirbytext($site->copyright()) ?>
      </footer>
    </div>
<?php endif; ?>

<body <?php if (isset($error)) echo 'class="error"' ?>>

<?php echo js('assets/js/vendor/jquery.js') ?>
<?php //echo js('assets/js/foundation/foundation.min.js') ?>
<?php //echo js('assets/js/foundation/foundation.interchange.js') ?>
<?php //echo js('assets/js/foundation/foundation.topbar.js') ?>
<?php //echo js('assets/js/vendor/unveil.js') ?>
<?php echo js('assets/js/vendor/min.js') ?>
<script>

    $(document).foundation();
    $("img.lazy").show() ;
    function reflow(){
        $(this).removeAttr('data-original');
        $(document).foundation('interchange', 'reflow');
    }
    $(document).ready(function() {
        $("img.lazy").unveil(500, reflow) ;
    });

</script>
</body>

</html>