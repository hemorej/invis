The Invisible Cities
====================

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F3bb5f071-9553-4336-b0c7-b049571c89e7&style=plastic)](https://forge.laravel.com/jerome-zpm/snowy-briars-j49/3264932)

This is the code for my [website](https://the-invisible-cities.com) of the same name. I wanted to open source it and have this repo as a code and content backup. 
It's a custom made theme for [Kirby](https://getkirby.com) using [Tachyons](https://tachyons.io/) and [Lazyload](https://github.com/verlok/lazyload) for lazy loading srcset images using the IntersectionObserver API
 
You can read all about the whole design process [here](https://jerome-arfouche.com/blog)  


### local setup and start

This project uses [DDEV](https://ddev.readthedocs.io/) for local development. No database is required (flat-file CMS).

```sh
ddev start        # start the local environment
ddev launch       # open in browser (https://invis.ddev.site)
ddev xdebug        # toggle Xdebug
ddev stop
```

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