let mix = require('laravel-mix');

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

mix.minify('assets/js/subs.js');
mix.combine([
    'assets/js/vendor/vue.min.js',
    'assets/js/vendor/axios.min.js',
    'assets/js/subs.min.js'], 'assets/dist/subs.min.js');

mix.minify('assets/js/app.js');
mix.combine(['assets/js/vendor/lazyload.min.js', 'assets/js/app.min.js'], 'assets/dist/app.min.js')

mix.minify('assets/js/consent.js');
mix.combine(['assets/js/vendor/cookieconsent.min.js', 'assets/js/consent.min.js'], 'assets/dist/consent.min.js');

mix.combine([
	'assets/css/vendor/tachyons.css',
	'assets/css/app.css'], 'assets/dist/app.min.css');

// export NODE_ENV=production; node_modules/.bin/webpack --config=node_modules/laravel-mix/setup/webpack.config.js