var classname = document.getElementsByClassName("cover");

for (var i = 0; i < classname.length; i++) {
    classname[i].addEventListener('mouseover', setPreview, false);
}

function setPreview(){
    document.getElementById('cover').setAttribute("src", this.getAttribute('data-preview'))
}