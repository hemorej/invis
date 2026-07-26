import { transform } from 'esbuild'
import { PurgeCSS } from 'purgecss'
import { readFileSync, writeFileSync } from 'fs'
import { fileURLToPath } from 'url'
import path from 'path'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const read = (p) => readFileSync(path.join(root, p), 'utf8')
const write = (p, contents) => writeFileSync(path.join(root, p), contents)

async function minify(code, loader) {
	const result = await transform(code, { loader, minify: true })
	return result.code
}

async function buildJsBundle(vendorPaths, sourcePath, outPath) {
	const vendor = vendorPaths.map(read).join('\n')
	const source = await minify(read(sourcePath), 'js')
	write(outPath, vendor + '\n' + source)
}

async function buildCss() {
	const purgeResult = await new PurgeCSS().purge({
		content: ['site/**/*.php', 'assets/js/**/*.js'].map((p) => path.join(root, p)),
		css: [{ raw: read('assets/css/vendor/tachyons.css') }],
		// Kirby/Vue templates only ever use plain, literal tachyons class
		// names (see product.js variant-on/off toggling, product.php,
		// article.php, etc.) so the default extractor is sufficient. This
		// safelist is a safety net for responsive/state suffixes in case a
		// class is ever assembled dynamically rather than written literally.
		safelist: [/-ns$/, /-m$/, /-l$/, /^hover-/, /^dim$/],
	})
	const purgedTachyons = purgeResult[0].css
	const app = await minify(read('assets/css/app.css'), 'css')
	write('assets/dist/app.min.css', purgedTachyons + '\n' + app)
}

async function main() {
	await Promise.all([
		buildJsBundle(['assets/js/vendor/lazyload.min.js'], 'assets/js/app.js', 'assets/dist/app.min.js'),
		buildJsBundle(['assets/js/vendor/vue.min.js', 'assets/js/vendor/axios.min.js'], 'assets/js/cart.js', 'assets/dist/cart.min.js'),
		buildJsBundle(['assets/js/vendor/vue.min.js', 'assets/js/vendor/axios.min.js'], 'assets/js/product.js', 'assets/dist/product.min.js'),
		buildCss(),
	])
}

main()
