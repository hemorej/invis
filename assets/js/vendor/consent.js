window.addEventListener("load", function(){
var loc = document.querySelector('script[id="consent"][data-loc]').getAttribute('data-loc');
var gaCode = document.querySelector('script[id="consent"][data-ga]').getAttribute('data-ga');
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
    "href": "//cookies"
  },
  onInitialise: function (status) {
    var didConsent = this.hasConsented();

    if (!didConsent) {
      window['ga-disable-' + gaCode] = true;
    }
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', gaCode, 'auto');
    ga('send', 'pageview');
  },
  onStatusChange: function(status, chosenBefore) {
    
    if (status == 'deny') {
      window['ga-disable-' + gaCode] = true;
      document.cookie = '_ga=; Path=/;Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
      document.cookie = '_gid=; Path=/;Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
      document.cookie = '_gat=; Path=/;Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }else{
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', gaCode, 'auto');
      ga('send', 'pageview');
    }
  },
})});