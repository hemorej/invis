The Invisible Cities
====================
 
This is the code for my [website](https://the-invisible-cities.com) of the same name. I wanted to open source it and have this repo as a code and content backup. 
It's a custom made theme for [Kirby](https://getkirby.com) using [Tachyons](https://tachyons.io/) and [Lazyload](https://github.com/verlok/lazyload) for lazy loading srcset images using the IntersectionObserver API
 
You can read all about the whole design process [here](https://jerome-arfouche.com/blog)  


### local setup and start  
`ddev config --php-version=8.3 --omit-containers=db`
`ddev xdebug`  
`ddev start`  
`ddev launch`  

### submodule update

`cd <submodule>`  
`git checkout tag`    
`cd ..`  
`git commit -m ''`  
`git submodule update --init --recursive`  


### docker

The app runs on `php:8.5-apache`. Submodule files must be present before building because the Dockerfile uses `COPY . .` — initialize them once after cloning:

```bash
git submodule update --init --recursive
```

**Build and run with docker compose:**

```bash
cp .env.example .env          # fill in your environment variables
docker compose up --build -d
```

**Or with plain docker:**

```bash
docker build -t invis .
docker run -d \
  --env-file .env.production \
  -v "$(pwd)/content:/var/www/html/content" \
  -p 80:80 \
  invis
```

**Deploying (CI/CD):**  
Always check out submodules before building. With GitHub Actions:

```yaml
- uses: actions/checkout@v4
  with:
    submodules: recursive
```

Submodule versions are pegged via the parent repo's tree — the exact commits recorded in git are what get checked out, so there is no version drift between deploys.

**Environment variables** are passed via `--env-file` (docker) or the `env_file` directive (docker compose). Copy `.env.example` and fill in values; never commit the populated file.

The `content/` directory is mounted as a volume so it persists across image rebuilds and can be managed independently of the application code.


### license activation

1. Download license file  
2. Rename the file to .license (without extensions)  
3. Place it in the /site/config/ folder of your Kirby installation  