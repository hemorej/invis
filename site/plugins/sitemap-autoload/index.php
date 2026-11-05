<?php

// kenshodigital/kirby-sitemap is a composer-first plugin; this project has no
// root composer autoloader, so map its namespace to the submodule's src/ here.
spl_autoload_register(function (string $class): void {
	$prefix = 'Kensho\\Sitemap\\';
	if (str_starts_with($class, $prefix)) {
		$file = __DIR__ . '/../sitemap/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
		if (is_file($file)) {
			require $file;
		}
	}
});
