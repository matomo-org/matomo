/*!
 * Piwik - Web Analytics
 *
 * PageRenderer class for screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var VERBOSE = false;

var PageRenderer = function (baseUrl) {
    this.webpage = null;

    this.queuedEvents = [];
    this.pageLogs = [];
    this.aborted = false;
    this.baseUrl = baseUrl;

    this.defaultWaitTime = 1000;
    this._isLoading = false;
};

PageRenderer.prototype._recreateWebPage = function () {
    if (this.webpage) {
        this.webpage.close();
    }

    this.webpage = require('webpage').create();
    this.webpage.viewportSize = {width:1350, height:768};
    this._setupWebpageEvents();
};

PageRenderer.prototype.getCurrentUrl = function () {
    return this.webpage.url;
};

// event queueing functions
PageRenderer.prototype.click = function () {
    var selector = arguments[0],
        waitTime = null,
        modifiers = [];

    for (var i = 1; i != arguments.length; ++i) {
        if (arguments[i] instanceof Array) {
            modifiers = arguments[i];
        } else {
            waitTime = arguments[i];
        }
    }

    this.queuedEvents.push([this._click, waitTime, selector, modifiers]);
};

PageRenderer.prototype.sendKeys = function (selector, keys, waitTime) {
    this.click(selector, 100);
    this.queuedEvents.push([this._keypress, waitTime, keys]);
};

PageRenderer.prototype.mouseMove = function (selector, waitTime) {
    this.queuedEvents.push([this._mousemove, waitTime, selector]);
};

PageRenderer.prototype.reload = function (waitTime) {
    this.queuedEvents.push([this._reload, waitTime]);
};

PageRenderer.prototype.load = function (url, waitTime) {
    this.queuedEvents.push([this._load, waitTime, url]);
};

PageRenderer.prototype.evaluate = function (impl, waitTime) {
    this.queuedEvents.push([this._evaluate, waitTime, impl]);
};

// event impl functions
PageRenderer.prototype._click = function (selector, modifiers, callback) {
    var position = this._getPosition(selector);

    if (modifiers.length) {
        var self = this;
        modifiers = modifiers.reduce(function (previous, mStr) {
            return self.webpage.event.modifier[mStr] | previous;
        }, 0);

        this.webpage.sendEvent('mousedown', position.x, position.y, 'left', modifiers);
        this.webpage.sendEvent('mouseup', position.x, position.y, 'left', modifiers);
    } else {
        this.webpage.sendEvent('click', position.x, position.y);
    }

    callback();
};

PageRenderer.prototype._keypress = function (keys, callback) {
    this.webpage.sendEvent('keypress', keys);

    callback();
};

PageRenderer.prototype._mousemove = function (selector, callback) {
    var position = this._getPosition(selector);
    this.webpage.sendEvent('mousemove', position.x, position.y);

    callback();
};

PageRenderer.prototype._reload = function (callback) {
    this.webpage.reload();

    callback();
};

PageRenderer.prototype._load = function (url, callback) {
    if (url.indexOf("://") === -1) {
        url = path.join(this.baseUrl, url);
    }

    this._recreateWebPage(); // calling open a second time never calls the callback
    this.webpage.open(url, callback);
};

PageRenderer.prototype._evaluate = function (impl, callback) {
    this.webpage.evaluate(function (js) {
        var $ = window.jQuery;
        eval("(" + js + ")();");
    }, impl.toString());

    callback();
};

PageRenderer.prototype._getPosition = function (selector) {
    var pos = this.webpage.evaluate(function (selector) {
        var element = window.jQuery(selector),
            offset = element.offset();

        if (!offset
            || !element.length
        ) {
            return null;
        }

        return {
            x: offset.left + element.width() / 2,
            y: offset.top + element.height() / 2
        };
    }, selector);

    if (!pos) {
        throw new Error("Cannot find element " + selector);
    }

    return pos;
};

// main capturing function
PageRenderer.prototype.capture = function (outputPath, callback) {
    if (this.webpage === null) {
        this._recreateWebPage();
    }

    var events = this.queuedEvents;
    this.queuedEvents = [];
    this.pageLogs = [];
    this.aborted = false;

    var self = this;
    this._executeEvents(events, function () {
        try {
            self._setCorrectViewportSize();
            self.webpage.render(outputPath);

            callback();
        } catch (e) {
            callback(e);
        }
    });
};

PageRenderer.prototype.abort = function () {
    this.aborted = true;
    this.webpage.stop();
};

PageRenderer.prototype._executeEvents = function (events, callback, i) {
    i = i || 0;

    var evt = events[i];
    if (!evt) {
        callback();
        return;
    }

    var impl = evt.shift(),
        waitTime = evt.shift() || this.defaultWaitTime;

    var self = this,
        waitForNextEvent = function () {
            self._waitForNextEvent(events, callback, i, waitTime);
        };

    evt.push(waitForNextEvent);

    try {
        impl.apply(this, evt);
    } catch (err) {
        self.pageLogs.push("Error: " + err.stack);
        waitForNextEvent();
    }
};

PageRenderer.prototype._getAjaxRequestCount = function () {
    return this.webpage.evaluate(function () {
        return window.globalAjaxQueue ? window.globalAjaxQueue.active : 0;
    });
};

PageRenderer.prototype._getImageLoadingCount = function () {
    return this.webpage.evaluate(function () {
        var count = 0;

        // check <img> elements
        var imgs = document.getElementsByTagName('img');
        for (var i = 0; i != imgs.length; ++i) {
            var element = imgs.item(i);
            if (element.complete === false) {
                count = count + 1;
            }
        }

        return count;
    });
};

PageRenderer.prototype._waitForNextEvent = function (events, callback, i, waitTime) {
    var self = this;
    setTimeout(function () {
        if (self._getAjaxRequestCount() == 0
            && self._getImageLoadingCount() == 0
            && !self._isLoading
        ) {
            self._executeEvents(events, callback, i + 1);
        } else {
            self._waitForNextEvent(events, callback, i, waitTime);
        }
    }, waitTime);
};

PageRenderer.prototype._setCorrectViewportSize = function () {
    this.webpage.viewportSize = {width:1350, height:768};
    var height = Math.max(768, this.webpage.evaluate(function() {
        return document.body.offsetHeight;
    }));
    this.webpage.viewportSize = {width:1350, height: height};
};

PageRenderer.prototype._setupWebpageEvents = function () {
    var self = this;
    this.webpage.onError = function (message, trace) {
        var msgStack = ['Webpage error: ' + message];
        if (trace && trace.length) {
            msgStack.push('trace:');
            trace.forEach(function(t) {
                msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
            });
        }

        self.pageLogs.push(msgStack.join('\n'));
    };

    if (VERBOSE) {
        this.webpage.onResourceReceived = function (response) {
            self.pageLogs.push('Response (#' + response.id + ', stage "' + response.stage + '", size "' +
                               response.bodySize + '", status "' + response.status + '"): ' + response.url);
        };
    }

    this.webpage.onResourceError = function (resourceError) {
        if (!self.aborted) {
            self.pageLogs.push('Unable to load resource (#' + resourceError.id + 'URL:' + resourceError.url + ')');
            self.pageLogs.push('Error code: ' + resourceError.errorCode + '. Description: ' + resourceError.errorString);
        }
    };

    this.webpage.onConsoleMessage = function (message) {
        self.pageLogs.push('Log: ' + message);
    };

    this.webpage.onLoadStarted = function () {
        self._isLoading = true;
    };

    this.webpage.onLoadFinished = function () {
        self._isLoading = false;
    };
};

exports.PageRenderer = PageRenderer;