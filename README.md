The Invisible Cities
====================

This is the code for my [website](https://the-invisible-cities.com) of the same name. I wanted to open source it and have this repo as a code and content backup. 
It's a custom made theme for [Kirby](https://getkirby.com) using [Tachyons](https://tachyons.io/) and [Lazyload](https://github.com/verlok/lazyload) for lazy loading srcset images using the IntersectionObserver API

It also includes a Stripe-backed print store. See `CLAUDE.md` for the architecture.

You can read all about the whole design process [here](https://jerome-arfouche.com/blog)  


### local setup and start

This project uses [DDEV](https://ddev.readthedocs.io/) for local development. No database is required (flat-file CMS).

```sh
ddev start        # start the local environment
ddev launch       # open in browser (https://invis.ddev.site)
ddev xdebug        # toggle Xdebug
ddev stop
```

#### checking the page cache locally

Pages are cached by [Staticache](https://github.com/getkirby/staticache) and, as in production, nginx serves
the cached files without starting PHP (`.ddev/nginx_full/nginx-site.conf`; run `ddev restart` after changing it).
Copy the `cache` block from `site/config/config.base` into your `site/config/config.php`.

```sh
rm -rf site/cache/invis.ddev.site/pages/journal           # force a miss
curl -sI https://invis.ddev.site/journal/ | grep -iE 'etag|last-modified'   # miss: Kirby renders it, no output
curl -sI https://invis.ddev.site/journal/ | grep -iE 'etag|last-modified'   # hit: nginx sent the file, so ETag + Last-Modified
```

`home` and `prints/*` are deliberately never cached. See `infra/README.md` for the details and the rollout.

### frontend build

Requires [pnpm](https://pnpm.io) (v11+).

```sh
pnpm install
pnpm run build     # compile and minify JS/CSS (esbuild + PurgeCSS) → assets/dist/
```

There is no dev/watch mode; run `pnpm run build` after every CSS/JS change.

### submodule update

`cd <submodule>`  
`git checkout tag`    
`cd ..`  
`git commit -m ''`  
`git submodule update --init --recursive`  


### license activation

1. Download license file  
2. Rename the file to .license (without extensions)  
3. Place it in the /site/config/ folder of your Kirby installation  