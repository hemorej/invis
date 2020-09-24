var classname = document.getElementsByClassName("cover");

for (var i = 0; i < classname.length; i++) {
    classname[i].addEventListener('mouseover', setPreview, false);
}

function setPreview(){
    document.getElementById('cover').setAttribute("src", this.getAttribute('data-preview'))
    document.getElementById('cover').setAttribute("alt", this.getAttribute('data-title'))
}

var lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy"
});