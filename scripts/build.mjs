import { transform } from 'esbuild'
import { PurgeCSS } from 'purgecss'
import { copyFileSync, readFileSync, writeFileSync } from 'fs'
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
	const parts = vendorPaths.map(read)
	parts.push(await minify(read(sourcePath), 'js'))
	write(outPath, parts.join('\n'))
}

// Alpine is shared by the cart and product pages: shipped as its own file so
// the browser caches it once instead of once per page bundle.
function copyAlpine() {
	copyFileSync(path.join(root, 'assets/js/vendor/alpine.min.js'), path.join(root, 'assets/dist/alpine.min.js'))
}

async function buildCss() {
	const purgeResult = await new PurgeCSS().purge({
		content: ['site/**/*.php', 'assets/js/**/*.js'].map((p) => path.join(root, p)),
		css: [{ raw: read('assets/css/vendor/tachyons.css') }],
	})
	const purgedTachyons = purgeResult[0].css
	const app = await minify(read('assets/css/app.css'), 'css')
	write('assets/dist/app.min.css', purgedTachyons + '\n' + app)
}

async function main() {
	await Promise.all([
		buildJsBundle(['assets/js/vendor/lazyload.min.js'], 'assets/js/app.js', 'assets/dist/app.min.js'),
		buildJsBundle([], 'assets/js/cart.js', 'assets/dist/cart.min.js'),
		buildJsBundle([], 'assets/js/product.js', 'assets/dist/product.min.js'),
		buildCss(),
		copyAlpine(),
	])
}

main()
