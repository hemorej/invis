window.addEventListener("load", function(){
var loc = document.querySelector('script[id="consent"][data-loc]').getAttribute('data-loc');

window.cookieconsent.initialise({
  "palette": {
    "popup": {
      "background": "#fafafa",
      "text": "#000"
    },
    "button": {
      "background": "transparent",
      "text": "#ffcd39",
      "border": "#ffcd39"
    }
  },
  "type": "opt-in",
  "regionalLaw": true,
  "law": {
      countryCode: loc,
  },
  "content": {
    "message": "This site uses cookies",
    "link": "(read more)",
    "href": "https://the-invisible-cities.com/cookies"
  },
  onInitialise: function (status) {
    // var didConsent = this.hasConsented();
  },
  onStatusChange: function(status, chosenBefore) {
    // if (status == 'deny')
  },
})});