<?php
$thumbDirectory = '/thumbs';
$placeholder = 'data:image/gif;base64,R0lGODdhWAKQAcIAAMzMzJaWlsXFxbGxsaOjo5ycnKqqqgAAACwAAAAAWAKQAQAD/gi63P4wykmrvTjrzbv/YCiOZGmeaKqubOu+cCzPdG3feK7vfO//wKBwSCwaj8ikcslsOp/QqHRKrVqv2Kx2y+16v+CweEwum8/otHrNbrvf8Lh8Tq/b7/i8fs/v+/+AgYKDhIWGh4iJiouMjY6PkJGSk5SVlpeYmZqbnJ2en6ChoqOkpaanqKmqq6ytrq+wsbKztLW2t7i5uru8vb6/wMHCw8TFxsfIycrLzM3Oz9DR0tPU1dbX2Nna29zd3t/g4eLj5OXm5+jp6uvs7e7v8PHy8/T19vf4+fr7/P3+/wADChxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzI/rGjx48gQ4ocSbKkyZMoU6pcybKly5cwY8qcSbOmzZs4c+rcybOnz59AgwodSrSo0aNIkypdyrSp06dQo0qdSrWq1atYs2rdyrWr169gw4odS7as2bNo06pdy7at27dw48qdS7eu3bt48+rdy7ev37+AAwseTLiw4cOIEytezLix48eQI0ueTLmy5cuYM2vezLmz58+gQ4seTbq06dOoU6tezbq169ewY8ueTbu27du4c+vezbu379/AgwsfTry48ePIkytfzry58+fQo0ufTr269evYs2vfzr279+/gw4sfT768+fPo0/8SMIBAgQABCAwQoJ7OAPj48w+oD0cA/oH8AMJHAH38sfFfgAESUOAaBiDo4H4LniGAgxQSGCEZ91GIIIQXjnGghgAq2OEYICJYwIgklhggimKouCKLYLznooAwgpHhjBzWyMWEMwZgoY5cNOiiAUDGqOKJRX4hgIwUFvBjkl0IiSCRUIrB3ofyPVnlllx26eWXYIYp5phklmnmmWimqeaabLbp5ptwxinnnHTWaeedeOap55589unnn4AGKuighBZq6KGIJqrooow26uijkEYq6aSUVmrppZhmqummnHbq6aeghirqqKSWauqpqKaq6qqsturqq7DGKuustNZq66245qrrrrz26uuvwAYr7LDEFmvsscgmZavsssw26+yz0EYr7bTUVmvttdhmq+223Hbr7bfghivuuOSWa+656Kar7rrstuvuu/DGK++89NZr77345qvvvvz26++/AAcs8MAEF2zwwQgnrPDCDDfs8MMQRyzxxBRXbPHFhyQAADs=';

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

<img class="lazy" <?php echo "alt='$alt' title='$title'" ?> data-original="[<?php echo $medium; ?>, (default)], [<?php echo $small; ?>, (small)], [<?php echo $medium; ?>, (medium)]" src="<?php echo $placeholder ?>" >
<noscript><img src="<?php echo $medium; ?>"></noscript>

