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


### license activation

1. Download license file  
2. Rename the file to .license (without extensions)  
3. Place it in the /site/config/ folder of your Kirby installation  