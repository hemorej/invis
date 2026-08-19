var classname = document.getElementsByClassName("cover");

for (var i = 0; i < classname.length; i++) {
    classname[i].addEventListener('mouseover', setPreview, false);
}

function setPreview(){
    var cover = document.getElementById('cover')
    var loader = cover.closest('.img-loader')
    if (loader) loader.classList.remove('is-loaded')
    cover.setAttribute("src", this.getAttribute('data-preview'))
    cover.setAttribute("alt", this.getAttribute('data-title'))
}

var loaderImages = document.querySelectorAll(".img-loader img");

for (var j = 0; j < loaderImages.length; j++) {
    (function(img){
        function markLoaded(){
            var loader = img.closest('.img-loader')
            if (loader) loader.classList.add('is-loaded')
        }
        if (img.complete && img.naturalWidth) markLoaded()
        img.addEventListener('load', markLoaded)
    })(loaderImages[j]);
}

if (typeof LazyLoad !== 'undefined') {
    var lazyLoadInstance = new LazyLoad({
        elements_selector: ".lazy"
    });
}