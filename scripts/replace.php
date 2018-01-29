<?php

$dirs = array('01-travels', '02-projects', '04-journal');

foreach($dirs as $dir){
	$cmd = 'find ' . $dir . ' -type f -iname "*.jpg"';
	exec($cmd, $files);

	foreach($files as $file){
		$original = '';
		$filename = preg_replace('/^(hr\.|\d\d_)/', '', basename($file));
		$cmd = 'find ~/Pictures/Photography -type f -iname "' . $filename . '*" | grep -vi original';
		exec($cmd, $original);

		if(empty($original[0])){
			echo basename(dirname($file)) ."/$filename not found!\n";
		}else{
			rename($file, $file.".bak");
			copy($original[0], dirname($file)."/$filename");
		}
	}

}