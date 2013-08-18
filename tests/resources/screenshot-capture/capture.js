var fs = require('fs');
var app = typeof slimer === 'undefined' ? phantom : slimer;
var readFileSync = fs.readFileSync || fs.read;

var PageRenderer = function() {
    this.start = new Date();

    this.urlIndex = 0;
    this.urls = JSON.parse(readFileSync('../../tmp/urls.txt'));

    this.webpage = require('webpage').create();
    this.outputPath = '';
    this.url = '';

    this._setupWebpageEvents();
    this._setScriptTimeout();
};

PageRenderer.prototype = {
    renderAll: function () {
        this._saveCurrentUrl();
    },

    _saveCurrentUrl: function () {
        if (this.urlIndex >= this.urls.length) {
            app.exit();
            return;
        }

        this.outputPath = this.urls[this.urlIndex][0];
        this.url = this.urls[this.urlIndex][1];

        console.log("SAVING " + this.url + " at " + this._getElapsedExecutionTime());

        this.webpage.viewportSize = {width:1350, height:768};
        this.webpage.open(this.url);

        this._setPageTimeouts();
    },

    _setPageTimeouts: function () {
        var url = this.url, self = this;
        this.webpage.onLoadFinished = function () {
            // check that we're still on the same url
            if (url != self.url) {
                return;
            }

            // in case there are no ajax requests, try triggering after a sec
            setTimeout(function () {
                if (url == self.url) {
                    self.webpage.evaluate(function () {
                        window.piwik.ajaxRequestFinished();
                    });
                }
            }, 1000)

            // only allowed at most one minute to load
            setTimeout(function () {
                if (url == self.url) {
                    self.webpage.evaluate(function () {
                        window.piwik._triggerRenderInsane();
                    });
                }
            }, 1000 * 60);
        };
    },

    _setupWebpageEvents: function () {
        this.webpage.onError = function (message) {
            console.log("Webpage error: " + message);
        };

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
    },

    _renderNextUrl: function () {
        ++this.urlIndex;
        this._saveCurrentUrl();
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
        }, Math.max(1000 * 15 * this.urls.length, 1000 * 60 * 5));
    },
};

try {
    var renderer = new PageRenderer();
    renderer.renderAll();
} catch (e) {
    console.log("ERROR: " + e.message);
    app.exit(1);
}