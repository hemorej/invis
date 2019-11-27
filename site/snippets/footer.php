<?php if (!isset($noCopyright)): ?>
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-12 medium-centered columns low-contrast">
        <p><?= html::decode($site->copyright()->kirbytext()) ?></p>
      </footer>
    </div>
<?php endif; ?>

<?= js('assets/js/min.js') ?>
<?php snippet('ga') ?>
</body>

</html>