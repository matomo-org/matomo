// ignore certain console.log errors from jsdom
const oldEmit = window._virtualConsole.emit;
window._virtualConsole.emit = (message) => {
  if (/navigation \(except hash changes\)/.test(message)) {
    return;
  }
  return oldEmit.call(window._virtualConsole, ...arguments);
};

// setup jquery and jquery-ui
window.$ = require('jquery');
window.jQuery = window.$;
require('jquery-ui-dist/jquery-ui');

// piwik and other globals
window.piwik = {};
window._pk_translate = (name) => name;

require('angular/angular.js');

require('../../plugins/CoreHome/javascripts/broadcast');
require('../../plugins/Morpheus/javascripts/piwikHelper');

angular.module('piwikApp.service', []);
angular.module('piwikApp', [
  'piwikApp.service',
]);

angular.element(() => {
  angular.bootstrap(document, ['piwikApp']);
});
