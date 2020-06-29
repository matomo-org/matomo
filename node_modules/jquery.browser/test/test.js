/*global casper, require, $ */

var test_url = "http://localhost:8008";

require("casperserver.js").create(casper);
casper.server.start();

casper.on("exit", function(status){
  casper.server.end();
});

var ua = {
  chrome: {
    windows: "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36",
    mac: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36",
    android: "Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Mobile Safari/537.36",
    linux: "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36",
    cros: "Mozilla/5.0 (X11; CrOS i686 14.811.2011) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.834.0 Safari/535.1",
    version: "32.0.1664.3",
    versionNumber: 32,
    chromeOsVersion: "14.0.834.0",
    chromeOsVersionNumber: 14,
    name: "chrome"
  },
  safari: {
    mac: "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Safari/537.71",
    ipad: "Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53",
    iphone: "Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53",
    ipod: "Mozilla/5.0 (iPod; CPU iPod OS 7_0 like Mac OS X) AppleWebKit/537.71 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53",
    version: "537.71",
    versionNumber: 7,
    name: "safari"
  },
  firefox: {
    windows: "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0",
    mac: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/20100101 Firefox/25.0",
    linux: "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0",
    version: "25.0",
    versionNumber: 25,
    name: "mozilla"
  },
  ie: {
    windows: {
      v_9: "Mozilla/4.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0)",
      v_10: "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
      v_11: "Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko"
    },
    win_phone: {
      v_10: "Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 1020)",
      v_11: "Mozilla/5.0 (Mobile; Windows Phone 8.1; Android 4.0; ARM; Trident/7.0; Touch; rv:11.0; IEMobile/11.0; NOKIA; Lumia 520) like iPhone OS 7_0_3 Mac OS X AppleWebKit/537 (KHTML, like Gecko) Mobile Safari/537"
    },
    name: "msie"
  },
  msedge: {
    windows: {
      v_12: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.0",
      v_13: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586"
    },
    win_phone: {
      v_13: "Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; NOKIA; Lumia 950) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Mobile Safari/537.36 Edge/13.10586"
    },
    name: "msedge"
  },
  opera: {
    v_15: {
      mac: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.20 Safari/537.36 OPR/15.0.1147.18",
      windows: "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.20 Safari/537.36 OPR/15.0.1147.18",
      version: "15.0.1147.18",
      versionNumber: 15
    },
    v_10: {
      mac: "Opera/9.80 (Macintosh; Intel Mac OS X; U; en) Presto/2.2.15 Version/10.00",
      windows: "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.6.30 Version/10.00",
      version: "10.00",
      versionNumber: 10
    },
    v_12: {
      mac: "Opera/9.80 (Macintosh; Intel Mac OS X; U; en) Presto/2.2.15 Version/12.11",
      windows: "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.6.30 Version/12.11",
      version: "12.11",
      versionNumber: 12
    },
    name: "opera"
  },
  android: {
    v_4_4: {
      android: "Mozilla/5.0 (Linux; Android 4.4.1; Nexus 5 Build/KOT49E) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Mobile Safari/537.36",
      version: "537.36",
      versionNumber: 4
    },
    name: "android"
  },
  kindle : {
    v_4: {
      kindle : "Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Kindle Fire Build/GINGERBREAD) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1",
      version: "533.1",
      versionNumber: 4
    },
    name: "kindle"
  },
  silk : {
    v_5: {
      silk : "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us; Silk/1.1.0-80) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16 Silk-Accelerated=true",
      version: "533.16",
      versionNumber: 5
    },
    name: "silk"
  },
  blackberry : {
    v_7: {
      blackberry : "Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+",
      version: "534.11",
      versionNumber: 7
    },
    name: "blackberry"
  },
  bb : {
    v_10: {
      bb : "Mozilla/5.0 (BB10; Touch) AppleWebKit/537.1 (KHTML, like Gecko) Version/10.0.0.1337 Mobile Safari/537.1",
      version: "537.1",
      versionNumber: 10
    },
    name: "bb"
  },
  playbook : {
    v_7: {
      playbook : "Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.1.0; en-US) AppleWebKit/536.2+ (KHTML, like Gecko) Version/7.2.1.0 Safari/536.2+",
      version: "536.2",
      versionNumber: 7
    },
    name: "playbook"
  }
};

casper.test.begin("when using Chrome on Windows", 7, function(test) {
  casper.userAgent(ua.chrome.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "String version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Number version should be " + ua.chrome.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Chrome on Mac", 7, function(test) {
  casper.userAgent(ua.chrome.mac);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "Version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Number version should be " + ua.chrome.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Chrome on an Android device", 7, function(test) {
  casper.userAgent(ua.chrome.android);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "Version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Version should be " + ua.chrome.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.android, "Platform should be Android");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Chrome on Linux", 7, function(test) {
  casper.userAgent(ua.chrome.linux);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "Version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Version should be " + ua.chrome.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.linux, "Platform should be Linux");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Chrome on Chrome OS", 7, function(test) {
  casper.userAgent(ua.chrome.cros);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.chromeOsVersion, "Version should be " + ua.chrome.chromeOsVersion);
    test.assertEquals(browser.versionNumber, ua.chrome.chromeOsVersionNumber, "Version should be " + ua.chrome.chromeOsVersionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.cros, "Platform should be Chrome OS");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Firefox on Windows", 7, function(test) {
  casper.userAgent(ua.firefox.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.mozilla, "Browser should be Mozilla");
    test.assertEquals(browser.name, ua.firefox.name,"Browser name should be " + ua.firefox.name);

    test.assertEquals(browser.version, ua.firefox.version, "Version should be " + ua.firefox.version);
    test.assertEquals(browser.versionNumber, ua.firefox.versionNumber, "Version should be " + ua.firefox.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Firefox on Mac", 7, function(test) {
  casper.userAgent(ua.firefox.mac);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.mozilla, "Browser should be Mozilla");
    test.assertEquals(browser.name, ua.firefox.name,"Browser name should be " + ua.firefox.name);

    test.assertEquals(browser.version, ua.firefox.version, "Version should be " + ua.firefox.version);
    test.assertEquals(browser.versionNumber, ua.firefox.versionNumber, "Version should be " + ua.firefox.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Firefox on Linux", 7, function(test) {
  casper.userAgent(ua.firefox.linux);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.mozilla, "Browser should be Mozilla");
    test.assertEquals(browser.name, ua.firefox.name,"Browser name should be " + ua.firefox.name);

    test.assertEquals(browser.version, ua.firefox.version, "Version should be " + ua.firefox.version);
    test.assertEquals(browser.versionNumber, ua.firefox.versionNumber, "Version should be " + ua.firefox.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.linux, "Platform should be Linux");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Safari on Mac", 7, function(test) {
  casper.userAgent(ua.safari.mac);

  casper.start(test_url).then(function(){

   var browser = casper.evaluate(function(){
    return $.browser;
   });

    test.assert(browser.safari, "Browser should be Safari");
    test.assertEquals(browser.name, ua.safari.name,"Browser name should be " + ua.safari.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.safari.version, "Version should be " + ua.safari.version);
    test.assertEquals(browser.versionNumber, ua.safari.versionNumber, "Version should be " + ua.safari.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Safari on iPad", 7, function(test) {
  casper.userAgent(ua.safari.ipad);

  casper.start(test_url).then(function(){

  var browser = casper.evaluate(function(){
    return $.browser;
  });

  test.assert(browser.safari, "Browser should be Safari");
  test.assertEquals(browser.name, ua.safari.name,"Browser name should be " + ua.safari.name);

  test.assert(browser.webkit, "Browser should be WebKit based");
  test.assertEquals(browser.version, ua.safari.version, "Version should be " + ua.safari.version);
  test.assertEquals(browser.versionNumber, ua.safari.versionNumber, "Version number should be " + ua.safari.versionNumber);

  test.assert(browser.mobile, "Browser platform should be mobile");
  test.assert(browser.ipad, "Platform should be iPad");

  }).run(function(){
   test.done();
  });
});

casper.test.begin("when using Safari on iPhone", 7, function(test) {
  casper.userAgent(ua.safari.iphone);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.safari, "Browser should be Safari");
    test.assertEquals(browser.name, ua.safari.name,"Browser name should be " + ua.safari.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.safari.version, "Version should be " + ua.safari.version);
    test.assertEquals(browser.versionNumber, ua.safari.versionNumber, "Version number should be " + ua.safari.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.iphone, "Platform should be iPhone");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Safari on iPod", 7, function(test) {
  casper.userAgent(ua.safari.ipod);

  casper.start(test_url).then(function(){
   
    var browser = casper.evaluate(function(){
      return $.browser;
    });
   
    test.assert(browser.safari, "Browser should be Safari");
    test.assertEquals(browser.name, ua.safari.name,"Browser name should be " + ua.safari.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.safari.version, "Version should be " + ua.safari.version);
    test.assertEquals(browser.versionNumber, ua.safari.versionNumber, "Version number should be " + ua.safari.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.ipod, "Platform should be iPod");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using IE9", 7, function(test) {
  casper.userAgent(ua.ie.windows.v_9);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msie, "Browser should be IE");
    test.assertEquals(browser.name, ua.ie.name,"Browser name should be " + ua.ie.name);

    test.assertEquals(browser.version, "9.0", "Version should be 9.0");
    test.assertEquals(browser.versionNumber, 9, "Version should be 9");

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using IE10", 7, function(test) {
  casper.userAgent(ua.ie.windows.v_10);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msie, "Browser should be IE");
    test.assertEquals(browser.name, ua.ie.name,"Browser name should be " + ua.ie.name);

    test.assertEquals(browser.version, "10.0", "Version should be 10");
    test.assertEquals(browser.versionNumber, 10, "Version should be 10");

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using IE10 on a Windows Phone", 7, function(test) {
  casper.userAgent(ua.ie.win_phone.v_10);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msie, "Browser should be IE");
    test.assertEquals(browser.name, ua.ie.name,"Browser name should be " + ua.ie.name);

    test.assertEquals(browser.version, "10.0", "Version should be 10.0");
    test.assertEquals(browser.versionNumber, 10, "Version should be 10");

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser["windows phone"], "Platform should be Windows Phone");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using IE11", 7, function(test) {
  casper.userAgent(ua.ie.windows.v_11);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msie, "Browser should be IE");
    test.assertEquals(browser.name, ua.ie.name,"Browser name should be " + ua.ie.name);

    test.assertEquals(browser.version, "11.0", "Version should be 11.0");
    test.assertEquals(browser.versionNumber, 11, "Version should be 11");

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using IE11 on a Windows Phone", 7, function(test) {
  casper.userAgent(ua.ie.win_phone.v_11);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msie, "Browser should be IE");
    test.assertEquals(browser.name, ua.ie.name,"Browser name should be " + ua.ie.name);

    test.assertEquals(browser.version, "11.0", "Version should be 11.0");
    test.assertEquals(browser.versionNumber, 11, "Version should be 11");

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser["windows phone"], "Platform should be Windows Phone");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Microsoft Edge 12", 7, function(test) {
  casper.userAgent(ua.msedge.windows.v_12);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msedge, "Browser should be MS Edge");
    test.assertEquals(browser.name, ua.msedge.name,"Browser name should be " + ua.msedge.name);

    test.assertEquals(browser.version, "12.0", "Version should be 12.0");
    test.assertEquals(browser.versionNumber, 12, "Version should be 12");

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
   test.done();
  });
});

casper.test.begin("when using Microsoft Edge 13", 7, function(test) {
  casper.userAgent(ua.msedge.windows.v_13);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msedge, "Browser should be MS Edge");
    test.assertEquals(browser.name, ua.msedge.name,"Browser name should be " + ua.msedge.name);

    test.assertEquals(browser.version, "13.10586", "Version should be 13.10586");
    test.assertEquals(browser.versionNumber, 13, "Version should be 13");

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
   test.done();
  });
});

casper.test.begin("when using Microsoft Edge v13 on a Windows Phone", 7, function(test) {
  casper.userAgent(ua.msedge.win_phone.v_13);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.msedge, "Browser should be MS Edge");
    test.assertEquals(browser.name, ua.msedge.name,"Browser name should be " + ua.msedge.name);

    test.assertEquals(browser.version, "13.10586", "Version should be 13.10586");
    test.assertEquals(browser.versionNumber, 13, "Version should be 13");

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser["windows phone"], "Platform should be Windows Phone");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});


casper.test.begin("when using Opera 15+ on Windows", 7, function(test) {
  casper.userAgent(ua.opera.v_15.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_15.version, "Version should be " + ua.opera.v_15.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_15.versionNumber, "Version number should be " + ua.opera.v_15.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Opera 15+ on Mac", 7, function(test) {
  casper.userAgent(ua.opera.v_15.mac);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_15.version, "Version should be " + ua.opera.v_15.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_15.versionNumber, "Version number should be " + ua.opera.v_15.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Opera 10 on Windows", 7, function(test) {
  casper.userAgent(ua.opera.v_10.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_10.version, "Version should be " + ua.opera.v_10.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_10.versionNumber, "Version number should be " + ua.opera.v_10.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Opera 10 on Mac", 7, function(test) {
  casper.userAgent(ua.opera.v_10.mac);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_10.version, "Version should be " + ua.opera.v_10.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_10.versionNumber, "Version number should be " + ua.opera.v_10.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Opera 12.11 on Windows", 7, function(test) {
  casper.userAgent(ua.opera.v_12.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_12.version, "Version should be " + ua.opera.v_12.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_12.versionNumber, "Version number should be " + ua.opera.v_12.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Opera 12.11 on Mac", 7, function(test) {
  casper.userAgent(ua.opera.v_12.mac);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.opera, "Browser should be Opera");
    test.assertEquals(browser.name, ua.opera.name,"Browser name should be " + ua.opera.name);

    test.assertEquals(browser.version, ua.opera.v_12.version, "Version should be " + ua.opera.v_12.version);
    test.assertEquals(browser.versionNumber, ua.opera.v_12.versionNumber, "Version number should be " + ua.opera.v_12.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.mac, "Platform should be Mac");

    test.assertFalsy(browser.webkit, "Browser should NOT be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Android 4.4 stock browser on Android", 6, function(test) {
  casper.userAgent(ua.android.v_4_4.android);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });

    test.assert(browser.android, "Browser should be Android");
    test.assertEquals(browser.name, ua.android.name,"Browser name should be " + ua.android.name);

    test.assertEquals(browser.version, ua.android.v_4_4.version, "Version should be " + ua.android.v_4_4.version);
    test.assertEquals(browser.versionNumber, ua.android.v_4_4.versionNumber, "Version number should be " + ua.android.v_4_4.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Kindle 4 stock browser", 6, function(test) {
  casper.userAgent(ua.kindle.v_4.kindle);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });
    
    test.assert(browser.kindle, "Browser should be Kindle");
    test.assertEquals(browser.name, ua.kindle.name,"Browser name should be " + ua.kindle.name);

    test.assertEquals(browser.version, ua.kindle.v_4.version, "Version should be " + ua.kindle.v_4.version);
    test.assertEquals(browser.versionNumber, ua.kindle.v_4.versionNumber, "Version number should be " + ua.kindle.v_4.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using Kindle Silk 5 browser", 6, function(test) {
  casper.userAgent(ua.silk.v_5.silk);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });
    
    test.assert(browser.silk, "Browser should be Silk");
    test.assertEquals(browser.name, ua.silk.name,"Browser name should be " + ua.silk.name);

    test.assertEquals(browser.version, ua.silk.v_5.version, "Version should be " + ua.silk.v_5.version);
    test.assertEquals(browser.versionNumber, ua.silk.v_5.versionNumber, "Version number should be " + ua.silk.v_5.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using BlackBerry 7 stock browser", 6, function(test) {
  casper.userAgent(ua.blackberry.v_7.blackberry);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });
    
    test.assert(browser.blackberry, "Browser should be BlackBerry");
    test.assertEquals(browser.name, ua.blackberry.name,"Browser name should be " + ua.blackberry.name);

    test.assertEquals(browser.version, ua.blackberry.v_7.version, "Version should be " + ua.blackberry.v_7.version);
    test.assertEquals(browser.versionNumber, ua.blackberry.v_7.versionNumber, "Version number should be " + ua.blackberry.v_7.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using BB10 stock browser", 6, function(test) {
  casper.userAgent(ua.bb.v_10.bb);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });
    
    test.assert(browser.blackberry, "Browser should be BlackBerry");
    test.assertEquals(browser.name, ua.blackberry.name,"Browser name should be " + ua.blackberry.name);

    test.assertEquals(browser.version, ua.bb.v_10.version, "Version should be " + ua.bb.v_10.version);
    test.assertEquals(browser.versionNumber, ua.bb.v_10.versionNumber, "Version number should be " + ua.bb.v_10.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when using BlackBerry PlayBook stock browser", 6, function(test) {
  casper.userAgent(ua.playbook.v_7.playbook);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return $.browser;
    });
    
    test.assert(browser.playbook, "Browser should be BlackBerry PlayBook");
    test.assertEquals(browser.name, ua.playbook.name,"Browser name should be " + ua.playbook.name);

    test.assertEquals(browser.version, ua.playbook.v_7.version, "Version should be " + ua.playbook.v_7.version);
    test.assertEquals(browser.versionNumber, ua.playbook.v_7.versionNumber, "Version number should be " + ua.playbook.v_7.versionNumber);

    test.assert(browser.mobile, "Browser platform should be mobile");
    test.assert(browser.webkit, "Browser should be WebKit based");

  }).run(function(){
    test.done();
    casper.exit();
  });
});

casper.test.begin("when using Chrome on Windows w/o jQuery", 7, function(test) {
  casper.userAgent(ua.chrome.windows);

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return window.jQBrowser;
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "String version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Number version should be " + ua.chrome.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

  }).run(function(){
    test.done();
  });
});

casper.test.begin("when trying to match a browser that is not the browser used by the user", 7, function(test) {
  casper.userAgent(ua.chrome.mac); // Use the Mac Chrome browser

  casper.start(test_url).then(function(){

    var browser = casper.evaluate(function(){
      return window.jQBrowser.uaMatch(ua.chrome.windows); // Match the Windows Chrome browser
    });

    test.assert(browser.chrome, "Browser should be Chrome");
    test.assertEquals(browser.name, ua.chrome.name,"Browser name should be " + ua.chrome.name);

    test.assert(browser.webkit, "Browser should be WebKit based");
    test.assertEquals(browser.version, ua.chrome.version, "String version should be " + ua.chrome.version);
    test.assertEquals(browser.versionNumber, ua.chrome.versionNumber, "Number version should be " + ua.chrome.versionNumber);

    test.assert(browser.desktop, "Browser platform should be desktop");
    test.assert(browser.win, "Platform should be Windows");

  }).run(function(){
    test.done();
  });
});
