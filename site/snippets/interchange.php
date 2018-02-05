<?php
$thumbDirectory = '/thumbs';
$placeholder = $site->url() . '/assets/images/ph.svg';

$loop = 0;
foreach($images as $image):

    $alt = $image->alt();
    $caption = $image->caption();

    if(!isset($alt) || $alt == ''){$alt = 'photograph';}
    if(!isset($title) || $title == ''){$title = $alt;}

    $options = array() ;
    if($page->isVisible()){
        $destination = $image->dir() . $thumbDirectory;
        $url = $page->url() . $thumbDirectory ;
        $options = array('root' => $destination, 'url' => $url);
    }

    if($image->isPortrait()){
        $small = thumb($image, a::merge(array('height' => 600), $options))->url() ; 
        $medium = thumb($image, a::merge(array('height' => 600), $options))->url() ; 
        $large = thumb($image, a::merge(array('height' => 800), $options))->url() ; 
    }
    else{
        $small = thumb($image, a::merge(array('width' => 600), $options))->url() ; 
        $medium = thumb($image, a::merge(array('width' => 800), $options))->url() ; 
        $large = thumb($image, a::merge(array('width' => 1200), $options))->url() ; 
    }

    ?>

    <?php if(!empty($caption->value)): ?>
        <div class="caption"><?php echo $caption->value ?></div>
    <?php endif ?>

    <?php if($loop == 0): ?>
        <img <?= "alt='$alt' title='$title'" ?> data-original="[<?= $small; ?>, (small)],
         [<?= $medium; ?>, (only screen and (min-width: 800px))], 
         [<?= $large; ?>, (only screen and (min-width: 1200px))]" 
         src="<?= $large ?>" >
        <noscript><img src="<?= $medium; ?>"></noscript>
    <?php else: ?>
        <img class="lazy" <?= "alt='$alt' title='$title'" ?> data-original="[<?= $small; ?>, (small)],
         [<?= $medium; ?>, (only screen and (min-width: 800px))], 
         [<?= $large; ?>, (only screen and (min-width: 1200px))]" 
         src="<?= $placeholder ?>" >
        <noscript><img src="<?= $medium; ?>"></noscript>
    <?php endif ?>
    <?php $loop++;?>
<?php endforeach ?>