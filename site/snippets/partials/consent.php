<?php $loc = location(); ?>

<?php if(!empty($loc)): ?>
    <?= css('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.css') ?>
    <?php if(@option('env') == 'prod'): ?>
       <?= js('assets/dist/consent.min.js', ['id' => 'consent', 'data-loc' => $loc->country_code]) ?>
    <?php else: ?>
       <?= js('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.1/cookieconsent.min.js') ?>
       <?= js('assets/js/consent.js', ['id' => 'consent', 'data-loc' => $loc->country_code]) ?>
    <?php endif ?>
<?php endif ?>
