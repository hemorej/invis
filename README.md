The Invisible Cities
====================
 
This is the code for my [website](https://the-invisible-cities.com) of the same name. I wanted to open source it and have this repo as a code and content backup. 
It's a custom made theme for [Kirby](https://getkirby.com) using [Tachyons](https://tachyons.io/) and [Lazyload](https://github.com/verlok/lazyload) for lazy loading srcset images using the IntersectionObserver API
 
You can read all about the whole design process [here](https://jerome-arfouche.com/blog)  




### Local setup and start  
`ddev config --php-version=8.3 --omit-containers=db`  
`ddev start`  
`ddev launch`  

### Submodule update

`cd <submodule>`  
`git checkout tag`    
`cd ..`  
`git commit -m ''`  
`git submodule update --init --recursive`  
