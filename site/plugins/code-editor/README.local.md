# Local fork notes

Vendored from https://github.com/sylvainjule/kirby-code-editor (tag 1.1.0), rebuilt with one patch:

- `src/components/field/CodeEditor.vue` imports `prismjs/components/prism-markup` so `language: html` has a grammar to highlight against. Upstream only ships css/js/json/less/php/python/ruby/scss/yaml — `html` throws a "no grammar" error client-side without this.

To rebuild after further changes: `npm install && npm run build` in this folder (needs `kirbyup`); commit the regenerated `index.js`/`index.css` alongside the `src/` change.
