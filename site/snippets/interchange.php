<?php

if(!isset($alt) || $alt == ''){$alt = 'photograph';}
if(!isset($title) || $title == ''){$title = $alt;}

if($image->isPortrait()){
    $small = thumb($image, array('height' => 600))->url() ;
    $medium = thumb($image, array('height' => 800))->url() ; 
    // $large = thumb($image, array('height' => 1024))->url() ; 
}
else{
    $small = thumb($image, array('width' => 600))->url() ;
    $medium = thumb($image, array('width' => 800))->url() ; 
    // $large = thumb($image, array('width' => 1200))->url() ; 
}

// for f in ./* ; do if [[ $f == *"jpg"* ]] ; then file=`find /Volumes/LACIE/Leica-M9/  -iname $(basename $f) | grep -i published` ; \cp "${file}" ./"$(basename $f)" ; fi; done
?>

<?php if(isset($caption)): ?>
    <div class="caption"><?php echo $caption ?></div>
<?php endif ?>

<img  <?php echo "alt='$alt' title='$title'" ?> data-interchange="[<?php echo $medium; ?>, (default)], [<?php echo $small; ?>, (small)], [<?php echo $medium; ?>, (medium)]">
<noscript><img src="<?php echo $medium; ?>"></noscript>

