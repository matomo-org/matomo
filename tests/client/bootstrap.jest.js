// ignore certain console.log errors from jsdom
const oldEmit = window._virtualConsole.emit;
window._virtualConsole.emit = function emitOverride(message, ...args) {
  if (/navigation \(except hash changes\)/.test(message)) {
    return undefined;
  }
  return oldEmit.call(window._virtualConsole, message, ...args);
};

// setup jquery and jquery-ui
window.$ = require('jquery');
window.jQuery = window.$;
require('jquery-ui-dist/jquery-ui');

// jsdom does not implement ResizeObserver, which components use to size themselves from their
// measured container. Every created observer is recorded on window.__resizeObservers so a spec can
// grab the last one and deliver entries with trigger(); specs that only need mounting to not throw
// can ignore it. Deliberately plain (no vi.fn) so the stub behaves the same however a spec
// configures its mocks.
class ResizeObserverStub {
  constructor(callback) {
    this.callback = callback;
    this.observed = [];
    this.disconnectCount = 0;
    window.__resizeObservers.push(this);
  }

  observe(target) {
    this.observed.push(target);
  }

  unobserve(target) {
    this.observed = this.observed.filter((observed) => observed !== target);
  }

  disconnect() {
    this.observed = [];
    this.disconnectCount += 1;
  }

  // Test helper, not part of the browser API: delivers entries as the browser would.
  trigger(entries) {
    this.callback(entries, this);
  }
}

window.__resizeObservers = [];
window.ResizeObserver = ResizeObserverStub;

// piwik and other globals
window.piwik = {};
window._pk_translate = (name) => name;

require('../../plugins/CoreHome/javascripts/broadcast');
require('../../plugins/Morpheus/javascripts/piwikHelper');
