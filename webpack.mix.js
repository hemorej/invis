let mix = require('laravel-mix');
let path = require('path');
require('laravel-mix-purgecss');

mix.minify('assets/js/product.js');
mix.combine([
	'assets/js/vendor/vue.min.js',
	'assets/js/vendor/axios.min.js',
	'assets/js/product.min.js'], 'assets/dist/product.min.js');

mix.minify('assets/js/cart.js');
mix.combine([
	'assets/js/vendor/vue.min.js',
	'assets/js/vendor/axios.min.js',
	'assets/js/cart.min.js'], 'assets/dist/cart.min.js');

mix.minify('assets/js/app.js');
mix.combine(['assets/js/vendor/lazyload.min.js', 'assets/js/app.min.js'], 'assets/dist/app.min.js')

mix.minify('assets/js/consent.js');
mix.combine(['assets/js/vendor/cookieconsent.min.js', 'assets/js/consent.min.js'], 'assets/dist/consent.min.js');

mix.postCss('assets/css/vendor/tachyons.css', 'assets/css/tachyons.purged.css')
	.purgeCss({
		content: [
			path.join(__dirname, 'site/**/*.php'),
			path.join(__dirname, 'assets/js/**/*.js'),
		],
		// Kirby/Vue templates only ever use plain, literal tachyons class
		// names (see product.js variant-on/off toggling, product.php,
		// article.php, etc.) so the default extractor is sufficient. This
		// safelist is a safety net for responsive/state suffixes in case a
		// class is ever assembled dynamically rather than written literally.
		safelist: [/-ns$/, /-m$/, /-l$/, /^hover-/, /^dim$/],
	});

mix.minify('assets/css/app.css');
mix.combine([
	'assets/css/tachyons.purged.css',
	'assets/css/app.min.css'], 'assets/dist/app.min.css');

// export NODE_ENV=production; node_modules/.bin/webpack --config=node_modules/laravel-mix/setup/webpack.config.js