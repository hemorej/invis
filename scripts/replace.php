<?php

$dirs = array('01-travels', '02-projects', '04-journal');

foreach($dirs as $dir){
	$files = array();
	$cmd = 'find /Applications/MAMP/htdocs/invis/content/' . $dir . ' -type f -iname "*.jpg"';
	exec($cmd, $files);

	foreach($files as $file){
		if(strstr($file, 'discard') != false)
			continue;

		$filename = preg_replace('/^(hr\.|\d{2}_|\d{2}-)/', '', basename($file));
		$filename = preg_replace('/(__\d).jpg$/', '.jpg', basename($filename));
		$filename = preg_replace('/^(m6_)/', 'm6-', basename($filename));
		$filename = preg_replace('/_small/', '', basename($filename));
		$original = findFile($filename);

		if(empty($original)){
			$filename = preg_replace('/-(\d{2,3})/', '_$1', $filename);
			$original = findFile($filename);

			if(empty($original)){
				$original = findFile($filename, '/Volumes/Backup/Photography');
				if(empty($original)){
					echo basename(dirname($file)) ."/$filename not found!\n";
				}else{
					rename($file, $file.".bak");
					copy($original, dirname($file)."/$filename");
				}
			}else{
				rename($file, $file.".bak");
				copy($original, dirname($file)."/$filename");
			}
		}else{
			rename($file, $file.".bak");
			copy($original, dirname($file)."/$filename");
		}
	}
}

function findFile($name, $path='~/Pictures/Photography'){
	$cmd = 'find ' . $path . ' -type f -iname "' . $name . '*" | grep -vi original';
	exec($cmd, $original);

	return $original[0];
}