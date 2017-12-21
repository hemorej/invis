<?php $price = number_format((float)$values->price(), 2, '.', ','); ?>
<span class="badge"><?= '$'.$price ?></span>
<?= $values->name() ?> [<?= $values->stock() ?> left]