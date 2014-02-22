// TODO: this is a mess, need to refactor
var fs = require('fs');
var app = typeof slimer === 'undefined' ? phantom : slimer;
var readFileSync = fs.readFileSync || fs.read;

var VERBOSE = false;
var PAGE_LOAD_TIMEOUT = 120;

var PageFacade = function (webpage) {
    this.webpage = webpage;
    this.events = [];
    this.impl = {
        click: function (selector, callback) {
            var position = this._getPosition(selector);
            this.webpage.sendEvent('click', position.x, position.y);

            callback();
        },

        keypress: function (keys, callback) {
            this.webpage.sendEvent('keypress', keys);

            callback();
        },

        mousemove: function (selector, callback) {
            var position = this._getPosition(selector);
            this.webpage.sendEvent('mousemove', position.x, position.y);

            callback();
        },

        reload: function (callback) {
            this.webpage.reload();

            callback();
        },

        load: function (url, callback) {
            this.webpage.open(url, callback);
        }
    };
};

PageFacade.prototype = {
    click: function (selector, waitTime) {
        this.events.push(['click', waitTime || 1000, selector]);
    },

    sendKeys: function (selector, keys, waitTime) {
        this.events.push(['click', 100, selector]);
        this.events.push(['keypress', waitTime || 1000, keys]);
    },

    mouseMove: function (selector, waitTime) {
        this.events.push(['mousemove', waitTime || 1000, selector]);
    },

    reload: function (waitTime) {
        this.events.push(['reload', waitTime]);
    },

    load: function (url) {
        this.events.push(['load', 1000, url]);
    },

    executeEvents: function (callback, i) {
        i = i || 0;

        var evt = this.events[i];
        if (!evt) {
            callback();
            return;
        }

        var type = evt.shift(),
            waitTime = evt.shift();

        var self = this;
        evt.push(function () {
            self._waitForNextEvent(callback, i, waitTime);
        });

        this.impl[type].apply(this, evt);
    },

    getAjaxRequestCount: function () {
        return this.webpage.evaluate(function () {
            return window.globalAjaxQueue ? window.globalAjaxQueue.active : 0;
        });
    },

    _waitForNextEvent: function (callback, i, waitTime) {
        var self = this;
        setTimeout(function () {
            if (self.getAjaxRequestCount() == 0) {
                self.executeEvents(callback, i + 1);
            } else {
                self._waitForNextEvent(callback, i, waitTime);
            }
        }, waitTime);
    },

    _getPosition: function (selector) {
        var pos = this.webpage.evaluate(function (selector) {
            var element = window.jQuery(selector),
                offset = element.offset();

            return {
                x: offset.left + element.width() / 2,
                y: offset.top + element.height() / 2
            };
        }, selector);

        if (!pos) {
            console.log("ERROR: Cannot find element: " + selector);
            app.exit(1);
        }

        return pos;
    },

    evaluate: function (impl) {
        return this.webpage.evaluate(function (js) {
            var $ = window.jQuery;
            eval("(" + js + ")();");
        }, impl.toString());
    }
};

var PageRenderer = function(data) {
    this.start = new Date();
};

PageRenderer.prototype = {
    renderAll: function () {
        this._saveCurrentScreen();
    },

    _setNoAjaxCheckTimeout: function () {
        var url = this.url, self = this;

        // in case there are no ajax requests, try triggering after a couple secs
        setTimeout(function () {
            if (url == self.url) {
                self.webpage.evaluate(function () {
                    if (window.piwik
                        && window.piwik.ajaxRequestFinished
                    ) {
                        window.piwik.ajaxRequestFinished();
                    } else {
                        console.log("__AJAX_DONE__");
                    }
                });
            }
        }, 5000);
    },

    _setPageTimeouts: function () {
        var url = this.url, self = this;

        // only allowed at most one minute to load
        setTimeout(function () {
            if (url == self.url) {
                self.webpage.evaluate(function () {
                    console.log("__AJAX_DONE__");
                });
            }
        }, 1000 * PAGE_LOAD_TIMEOUT);
    },

    _setupWebpageEvents: function () {
        var self = this;
        this.webpage.onError = function (message, trace) {
            var msgStack = ['Webpage error: ' + message];
            if (trace && trace.length) {
                msgStack.push('trace:');
                trace.forEach(function(t) {
                    msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
                });
            }
            console.log(msgStack.join('\n'));
        };

        if (VERBOSE) {
            this.webpage.onResourceReceived = function (response) {
                console.log('Response (#' + response.id + ', stage "' + response.stage + '", size "' + response.bodySize +
                            '", status "' + response.status + '"): ' + response.url);
            };
        }

        this.webpage.onResourceError = function (resourceError) {
            console.log('Unable to load resource (#' + resourceError.id + 'URL:' + resourceError.url + ')');
            console.log('Error code: ' + resourceError.errorCode + '. Description: ' + resourceError.errorString);
        };
    },

    _setCorrectViewportSize: function () {
        this.webpage.viewportSize = {width:1350, height:768};
        var height = Math.max(768, this.webpage.evaluate(function() {
            return document.body.offsetHeight;
        }));
        this.webpage.viewportSize = {width:1350, height: height};
    },

    _getElapsedExecutionTime: function () {
        var now = new Date(),
            elapsed = now.getTime() - this.start.getTime();

        return (elapsed / 1000.0) + "s";
    },

    _setScriptTimeout: function () {
        setTimeout(function() {
            console.log("ERROR: Timed out!");
            app.exit(1);
        }, Math.max(1000 * 15 * this.screenshotCount, 1000 * 60 * 10));
    },

    _executeScreenJs: function (js, callback) {
        var page = new PageFacade(this.webpage);
        eval(js);
        page.executeEvents(callback || function () {});
    }
};

var IntegrationTestRenderer = function(data) {
    PageRenderer.call(this, data);

    this.outputPath = '';
    this.url = '';

    this.urlIndex = 0;
    this.urls = data;

    this.screenshotCount = this.urls.length;

    this._setScriptTimeout();
};

IntegrationTestRenderer.prototype = Object.create(PageRenderer.prototype);

IntegrationTestRenderer.prototype._saveCurrentScreen = function () {
    if (this.urlIndex >= this.urls.length) {
        app.exit();
        return;
    }

    this.outputPath = this.urls[this.urlIndex][0];
    this.url = this.urls[this.urlIndex][1];
    this.jsToTest = this.urls[this.urlIndex][2];

    console.log("SAVING " + this.url + " at " + this._getElapsedExecutionTime());

    if (this.webpage) {
        this.webpage.close();
    }

    this.webpage = require('webpage').create();
    this._setupWebpageEvents();

    this.webpage.viewportSize = {width:1350, height:768};

    var self = this;
    this.webpage.open(this.url, function () {
        if (self.jsToTest) {
            self._executeScreenJs(self.jsToTest);
        }

        self._setNoAjaxCheckTimeout();
    });
    this._setPageTimeouts();
};

IntegrationTestRenderer.prototype._setupWebpageEvents = function () {
    PageRenderer.prototype._setupWebpageEvents.call(this);

    var self = this;
    this.webpage.onConsoleMessage = function (message) {
        if (message == "__AJAX_DONE__") {
            try {
                self._setCorrectViewportSize();
                self.webpage.render(self.outputPath);

                self._renderNextUrl();
            } catch (e) {
                console.log("ERROR: " + e.message);
                app.exit(1);
            }
        } else {
            console.log("LOGGED: " + message);
        }
    };
};

IntegrationTestRenderer.prototype._renderNextUrl = function () {
    ++this.urlIndex;
    this._saveCurrentScreen();
};

var UnitTestRenderer = function(data) {
    PageRenderer.call(this, data);

    this.screenIndex = 0;
    this.url = data.url;
    this.screens = data.screens;

    this.screenshotCount = this.screens.length;

    this._setScriptTimeout();
};

UnitTestRenderer.prototype = Object.create(PageRenderer.prototype);

UnitTestRenderer.prototype.renderAll = function () {
    this.webpage = require('webpage').create();
    this._setupWebpageEvents();

    this.webpage.viewportSize = {width:1350, height:768};

    console.log("OPENING INITIAL URL: " + this.url);

    var self = this;
    this.webpage.open(this.url, function () {
        setTimeout(function () {
            self._saveCurrentScreen();
        }, 15 * 1000);
    });
};

UnitTestRenderer.prototype._saveCurrentScreen = function () {
    if (this.screenIndex >= this.screens.length) {
        app.exit();
        return;
    }

    var outputPath = this.screens[this.screenIndex][0],
        screenJs = this.screens[this.screenIndex][1];

    console.log("SAVING " + outputPath + " at " + this._getElapsedExecutionTime());

    var self = this;
    this._executeScreenJs(screenJs, function () {
        try {
            self._setCorrectViewportSize();
            self.webpage.render(outputPath);

            self._renderNextUrl();
        } catch (e) {
            console.log("ERROR: " + e.message + "\n" + (e.stack || ''));
            app.exit(1);
        }
    });
};

UnitTestRenderer.prototype._renderNextUrl = function () {
    ++this.screenIndex;
    this._saveCurrentScreen();
};

try {
    var data = JSON.parse(readFileSync('../../tmp/urls.txt'));

    if (data instanceof Array) {
        var renderer = new IntegrationTestRenderer(data);
    } else {
        var renderer = new UnitTestRenderer(data);
    }

    renderer.renderAll();
} catch (e) {
    console.log("ERROR: " + e.message);
    app.exit(1);
}