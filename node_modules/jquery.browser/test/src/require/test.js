// Emulate the browser
global.window = {
  navigator: {
    userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.45 Safari/537.36'
  }
};

var browser = require('../jquery.browser.js');

var should = require('chai').should();

describe('require jQuery browser', function() {
  it('should have the correct properties for a Chrome browser on a Mac', function(done) {
    browser.webkit.should.be.ok;
    browser.mac.should.be.ok;
    browser.desktop.should.be.ok;

    done();
  });
})

