<?php

use Kirby\Cms\Pages;
use Kirby\Cms\Site;

/**
 * Pages listed in /sitemap.xml (consumed by the kenshodigital/kirby-sitemap plugin).
 */
return function (Site $site): Pages {
	// unlisted pages that should still be in the sitemap
	$include = ['home', 'travels', 'journal-series'];

	// listed pages that should not
	$exclude = ['19', 'buenos-aires', 'frankfurt', 'iceland', 'ottawa', 'color', 'portfolio', 'second-floor-window', 'four-book-bundle'];

	return $site->index()
		->filter(fn ($page) => $page->isListed() || in_array($page->slug(), $include, true))
		->filter(fn ($page) => !in_array($page->slug(), $exclude, true));
};
