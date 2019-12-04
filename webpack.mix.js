let mix = require('laravel-mix');

mix.minify('assets/js/product.js', 'assets/dist/product.min.js');
mix.combine([
	'assets/js/vendor/vue.min.js',
	'assets/js/vendor/vue-carousel.min.js',
	'assets/js/vendor/axios.min.js',
	'assets/dist/product.min.js'], 'assets/dist/product.min.js');

mix.minify('assets/js/cart.js', 'assets/dist/cart.min.js');
mix.combine([
	'assets/js/vendor/vue.min.js',
	'assets/js/vendor/axios.min.js',
	'assets/dist/cart.min.js'], 'assets/dist/cart.min.js');

mix.minify('assets/js/app.js', 'assets/dist/app.min.js');
mix.combine(['assets/js/vendor/lazyload.min.js', 'assets/dist/app.min.js'], 'assets/dist/app.min.js')

mix.minify('assets/js/consent.js', 'assets/dist/consent.min.js');
mix.combine(['assets/js/vendor/cookieconsent.min.js', 'assets/dist/consent.min.js'], 'assets/dist/consent.min.js');

mix.combine([
	'assets/css/vendor/tachyons.css',
	'assets/css/app.css'], 'assets/dist/app.min.css');

// export NODE_ENV=production; node_modules/.bin/webpack --config=node_modules/laravel-mix/setup/webpack.config.js