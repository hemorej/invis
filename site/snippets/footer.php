<?php if (!isset($noCopyright)): ?>
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-12 medium-centered columns low-contrast">
        <p><?php echo html::decode($site->copyright()->kirbytext()) ?></p>
      </footer>
    </div>
<?php endif; ?>

<?= js('assets/js/vendor/jquery.js') ?>
<?= js('assets/js/vendor/min.js') ?>
<?php //echo js('assets/js/foundation/foundation.min.js') ?>
<?php //echo js('assets/js/foundation/foundation.interchange.js') ?>
<?php //echo js('assets/js/foundation/foundation.topbar.js') ?>
<?php //echo js('assets/js/vendor/unveil.js') ?>
<?php snippet('ga') ?>
</body>

</html>
