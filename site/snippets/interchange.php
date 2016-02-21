<?php
$thumbDirectory = '/thumbs';
$placeholder = $site->url() . '/assets/images/ph.svg';

$loop = 0 ;

foreach($images as $image):

    $alt = $image->alt() ;
    $caption = $image->caption() ;

    if(!isset($alt) || $alt == ''){$alt = 'photograph';}
    if(!isset($title) || $title == ''){$title = $alt;}

    $options = array() ;
    if($page->isVisible()){
        $destination = $image->dir() . $thumbDirectory;
        $url = $page->url() . $thumbDirectory ;
        dir::make($destination);
        $options = array('root' => $destination, 'url' => $url);
    }

    if($image->isPortrait()){
        $small = thumb($image, a::merge(array('height' => 600), $options))->url() ; 
        $medium = thumb($image, a::merge(array('height' => 800), $options))->url() ; 
    }
    else{
        $small = thumb($image, a::merge(array('width' => 600), $options))->url() ; 
        $medium = thumb($image, a::merge(array('width' => 800), $options))->url() ; 
    }

    ?>

    <?php if(isset($caption)): ?>
        <div class="caption"><?php echo $caption ?></div>
    <?php endif ?>

    <?php if($loop > 0): ?>
        <img class="lazy" <?php echo "alt='$alt' title='$title'" ?> data-original="[<?php echo $medium; ?>, (default)], [<?php echo $small; ?>, (small)], [<?php echo $medium; ?>, (medium)]" src="<?php echo $placeholder ?>" >
        <noscript><img src="<?php echo $medium; ?>"></noscript>
    <?php else: ?>
        <img <?php echo "alt='$alt' title='$title'" ?> data-interchange="[<?php echo $medium; ?>, (default)], [<?php echo $small; ?>, (small)], [<?php echo $medium; ?>, (medium)]" src="<?php echo $medium; ?>" >
        <?php ++$loop ; ?>
    <?php endif ?>

<?php endforeach ?>