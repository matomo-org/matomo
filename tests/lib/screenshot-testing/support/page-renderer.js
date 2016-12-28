/*!
 * Piwik - free/libre analytics platform
 *
 * PageRenderer class for screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var VERBOSE = false;

// TODO: should refactor, move all event queueing logic to PageAutomation class and add .frame method to change context
var PageRenderer = function (baseUrl) {
    this.webpage = null;
    this.userAgent = null;

    this.queuedEvents = [];
    this.pageLogs = [];
    this.aborted = false;
    this.baseUrl = baseUrl;
    this.currentFrame = null;

    this.defaultWaitTime = 1000;
    this._isLoading = false;
    this._isInitializing = false;
    this._isNavigationRequested = false;
    this._requestedUrl = 'about:blank';
    this._resourcesRequested = {};

    if (this.baseUrl.substring(-1) != '/') {
        this.baseUrl = this.baseUrl + '/';
    }
};

PageRenderer.prototype._recreateWebPage = function () {
    if (this.webpage) {
        this.webpage.close();
    }

    this.downloadedContents = null;

    this.webpage = require('webpage').create();
    this._setCorrectViewportSize();
    if (this.userAgent) {
        this.webpage.settings.userAgent = this.userAgent;
    }

    this._setupWebpageEvents();
};

PageRenderer.prototype.setViewportSize = function (w, h) {
    this._viewportSizeOverride = {width: w, height: h};
};

PageRenderer.prototype.getCurrentUrl = function () {
    return this.webpage ? this.webpage.url : null;
};

// event queueing functions
PageRenderer.prototype.wait = function (waitTime) {
    this.queuedEvents.push([this._wait, waitTime]);
};

PageRenderer.prototype.sendMouseEvent = function (type, pos, waitTime) {
    this.queuedEvents.push([this._sendMouseEvent, waitTime, type, pos]);
};

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

PageRenderer.prototype.mousedown = function (selector, waitTime) {
    this.queuedEvents.push([this._mousedown, waitTime, selector]);
};

PageRenderer.prototype.mouseup = function (selector, waitTime) {
    this.queuedEvents.push([this._mouseup, waitTime, selector]);
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

// like .evaluate() but doesn't call `impl` in context of the webpage. Useful if you want to change eg a testEnvironment
// before a click. Makes sure this callback `impl` will be executed just before the next action instead of immediately
PageRenderer.prototype.execCallback = function (callback, waitTime) {
    this.queuedEvents.push([this._execCallback, waitTime, callback]);
};

PageRenderer.prototype.downloadLink = function (selector, waitTime) {
    this.queuedEvents.push([this._downloadLink, waitTime, selector]);
};

PageRenderer.prototype.downloadUrl = function (url, waitTime) {
    this.queuedEvents.push([this._downloadUrl, waitTime, url]);
};

PageRenderer.prototype.dragDrop = function (startSelector, endSelector, waitTime) {
    this.mousedown(startSelector, waitTime);
    this.mouseMove(endSelector, waitTime);
    this.mouseup(endSelector, waitTime);
};

// event impl functions
PageRenderer.prototype._wait = function (callback) {
    callback();
};

PageRenderer.prototype._sendMouseEvent = function (type, pos, callback) {
    this.webpage.sendEvent(type, pos.x, pos.y);
    callback();
};

PageRenderer.prototype._click = function (selector, modifiers, callback) {
    var position = this._getPosition(selector);

    this._makeSurePositionIsInViewPort(position.x, position.y);

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

PageRenderer.prototype._makeSurePositionIsInViewPort = function (width, height) {

    var currentWidth  = this._getViewportWidth();
    var currentHeight = this._getViewportHeight();

    var update = false;

    if (width && width > 0 && width >= currentWidth) {
        currentWidth = width + 50;
        update = true;
    }

    if (height && height > 0 && height >= currentHeight) {
        currentHeight = height + 50;
        update = true;
    }

    if (update) {
        this._setCorrectViewportSize({width: currentWidth, height: currentHeight});
    }
};

PageRenderer.prototype._getViewportWidth = function () {
    var width = 1350;
    if (this._viewportSizeOverride && this._viewportSizeOverride.width) {
        width = this._viewportSizeOverride.width;
    }

    return width;
};

PageRenderer.prototype._getViewportHeight = function () {
    var height = 768;
    if (this._viewportSizeOverride && this._viewportSizeOverride.height) {
        height = this._viewportSizeOverride.height;
    }

    return height;
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

PageRenderer.prototype._mousedown = function (selector, callback) {
    var position = this._getPosition(selector);
    this.webpage.sendEvent('mousedown', position.x, position.y);

    callback();
};

PageRenderer.prototype._mouseup = function (selector, callback) {
    var position = this._getPosition(selector);
    this.webpage.sendEvent('mouseup', position.x, position.y);

    callback();
};

PageRenderer.prototype._reload = function (callback) {
    this.webpage.reload();

    callback();
};

PageRenderer.prototype._load = function (url, callback) {
    if (url.indexOf("://") === -1) {
        url = this.baseUrl + url;
    }

    this._recreateWebPage(); // calling open a second time never calls the callback

    this._requestedUrl   = url;
    this._isInitializing = true;
    this._resourcesRequested = {};

    var self = this;
    this.webpage.open(url, function (status) {

        if (VERBOSE) {
            self._logMessage('Webpage open event');
        }

        self._isInitializing = false;
        self._isLoading = false;

        this.evaluate(function () {
            var $ = window.jQuery;

            if ($) {
                $('html').addClass('uiTest');
                $.fx.off = true;

                var css = document.createElement('style');
                css.type = 'text/css';
                css.innerHTML = '* { -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }';
                document.body.appendChild(css);
            }
        });

        if (callback) {
            callback(status);
        }
    });
};

PageRenderer.prototype._evaluate = function (impl, callback) {
    this.webpage.evaluate(function (js) {
        var $ = window.jQuery;
        eval("(" + js + ")();");
    }, impl.toString());

    callback();
};

PageRenderer.prototype._execCallback = function (actualCallback, callback) {
    actualCallback();
    callback();
};

PageRenderer.prototype._downloadLink = function (str, callback) {
    var url = this.webpage.evaluate(function (selector) {
        return $(selector).attr('href');
    }, str);

    this._downloadUrl(url, callback);
};

PageRenderer.prototype._downloadUrl = function (url, callback) {
    var response = this.webpage.evaluate(function (url) {
        var $ = window.jQuery;

        return $.ajax({
            type: "GET",
            url: url,
            async: false
        }).responseText;
    }, url);

    this.downloadedContents = response;

    callback();
};

PageRenderer.prototype._getPosition = function (selector) {
    if (selector.x && selector.y) {
        return selector;
    }

    var pos = this.webpage.evaluate(function (selector) {
        var element = window.jQuery(selector),
            offset = element.offset();

        if (!offset
            || !element.length
        ) {
            // TODO: this should get captured and outputted as part of the web page logs failure info, but
            //       at the moment it doesn't
            console.log("ERROR: Cannot find element '" + selector + "'.");

            return null;
        }

        return {
            x: offset.left + element.width() / 2,
            y: offset.top + element.height() / 2
        };
    }, selector);

    return pos;
};

PageRenderer.prototype.contains = function (selector) {
    return this.webpage.evaluate(function (selector) {
        return $(selector).length != 0;
    }, selector);
};

// main capturing function
PageRenderer.prototype.capture = function (outputPath, callback, selector) {
    var self = this,
        timeout = setTimeout(function () {
            var timeoutDetails = "";
            timeoutDetails += "Page is loading: " + self._isLoading + "\n";
            timeoutDetails += "Initializing: " + self._isInitializing + "\n";
            timeoutDetails += "Navigation requested: " + self._isNavigationRequested + "\n";
            timeoutDetails += "Pending AJAX request count: " + self._getAjaxRequestCount() + "\n";
            timeoutDetails += "Loading images count: " + self._getImageLoadingCount() + "\n";
            timeoutDetails += "Remaining resources: " + JSON.stringify(self._resourcesRequested) + "\n";

            self.abort();

            callback(new Error("Screenshot load timeout. Details:\n" + timeoutDetails));
        }, 240 * 1000);

    if (this.webpage === null) {
        this._recreateWebPage();
    }

    var events = this.queuedEvents;
    this.queuedEvents = [];
    this.pageLogs = [];
    this.aborted = false;

    function setClipRect (page, selector) {
        if (!selector) {
            return;
        }

        if (self.aborted) {
            return false;
        }

        var result = page.evaluate(function(selector) {
            var docWidth = $(document).width(),
                docHeight = $(document).height();

            function isInvalidBoundingRect (rect) {
                return !rect.width || !rect.height
                    || (rect.left < 0 && rect.right < 0)
                    || (rect.left > docWidth && rect.right > docWidth)
                    || (rect.top < 0 && rect.bottom < 0)
                    || (rect.top > docHeight && rect.bottom > docHeight);
            }

            var element = window.jQuery(selector);

            if (element && element.length) {
                var clipRect = {bottom: null, height: null, left: null, right: null, top: null, width: null};

                element.each(function (index, node) {
                    if (!$(node).is(':visible')) {
                        return;
                    }

                    var rect = $(node).offset();
                    rect.width = $(node).outerWidth();
                    rect.height = $(node).outerHeight();
                    rect.right = rect.left + rect.width;
                    rect.bottom = rect.top + rect.height;

                    if (isInvalidBoundingRect(rect)) {
                        // element is not visible
                        return;
                    }

                    if (null === clipRect.left || rect.left < clipRect.left) {
                        clipRect.left = rect.left;
                    }
                    if (null === clipRect.top || rect.top < clipRect.top) {
                        clipRect.top = rect.top;
                    }
                    if (null === clipRect.right || rect.right > clipRect.right) {
                        clipRect.right = rect.right;
                    }
                    if (null === clipRect.bottom || rect.bottom > clipRect.bottom) {
                        clipRect.bottom = rect.bottom;
                    }
                });

                clipRect.width  = clipRect.right - clipRect.left;
                clipRect.height = clipRect.bottom - clipRect.top;

                return clipRect;
            }

        }, selector);

        if (!result) {
            console.log("Cannot find element " + selector);
            return;
        }

        if (result && result.__isCallError) {
            throw new Error("Error while detecting element clipRect " + selector + ": " + result.message);
        }

        if (null === result.left
            || null === result.top
            || null === result.bottom
            || null === result.right
        ) {
            console.log("Element(s) " + selector + " found but none is visible");
            return;
        }

        page.clipRect = result;
    }

    this._executeEvents(events, function () {

        clearTimeout(timeout);

        if (self.aborted) {
            return;
        }

        try {
            if (outputPath) {

                self._setCorrectViewportSize();

                // _setCorrectViewportSize might cause a re-render. We should wait for a while for the re-render to
                // finish before capturing a screenshot to avoid possible random failures.
                var timeInMsToWaitForReRenderToFinish = 500;
                setTimeout(function () {
                    var previousClipRect = self.webpage.clipRect;

                    try {
                        if (self.aborted) {
                            return;
                        }

                        setClipRect(self.webpage, selector);

                        self.webpage.render(outputPath);
                        self._viewportSizeOverride = null;
                        self.webpage.clipRect = previousClipRect;

                        if (!self.aborted) {
                            callback();
                        }

                    } catch (e) {
                        if (previousClipRect) {
                            self.webpage.clipRect = previousClipRect;
                        }
                    }

                }, timeInMsToWaitForReRenderToFinish);

            } else {
                callback();
            }

        } catch (e) {

            if (self.aborted) {
                return;
            }

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
    if (!evt || this.aborted) {
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
        self._logMessage("Error: " + err.stack);
        waitForNextEvent();
    }
};

PageRenderer.prototype._getAjaxRequestCount = function () {
    return this.webpage.evaluate(function () {
        var active = window.globalAjaxQueue ? window.globalAjaxQueue.active : 0;

        if ('undefined' !== (typeof angular)
            && angular && document && angular.element(document)
            && angular.element(document).injector()) {
            var $http = angular.element(document).injector().get('$http');
            if ($http && $http.pendingRequests) {
                active += $http.pendingRequests.length;
            }
        }

        return active;
    });
};

PageRenderer.prototype._getImageLoadingCount = function () {
    return this.webpage.evaluate(function () {
        var count = 0;

        var cssImageProperties = ['backgroundImage', 'listStyleImage', 'borderImage', 'borderCornerImage', 'cursor'],
            matchUrl = /url\(\s*(['"]?)(.*?)\1\s*\)/g;

        if (!window._pendingImages) {
            window._pendingImages = {};
        }

        // check <img> elements and background URLs
        var elements = document.getElementsByTagName('*');
        for (var i = 0; i != elements.length; ++i) {
            var element = elements.item(i);
            if (element.tagName.toLowerCase() == 'img' // handle <img> elements
                && element.complete === false
            ) {
                count = count + 1;
            }

            if (typeof $ === "undefined") { // waiting for CSS depends on jQuery
                continue;
            }

            for (var j = 0; j != cssImageProperties.length; ++j) { // handle CSS image URLs
                var prop = $(element).css(cssImageProperties[j]);

                if (!prop) {
                    continue;
                }

                while (match = matchUrl.exec(prop)) {
                    var src = match[2];
                    if (window._pendingImages[src]) {
                        continue;
                    }

                    var img = new Image();

                    img.addEventListener('load', function () {
                        window._pendingImages[this.src] = true;
                    });

                    window._pendingImages[src] = img;
                    img.src = src;
                }
            }
        }

        for (var url in window._pendingImages) {
            if (typeof window._pendingImages[url] === 'object') {
                count = count + 1;
            }
        }

        return count;
    });
};

PageRenderer.prototype._waitForNextEvent = function (events, callback, i, waitTime) {

    if (this.aborted) {
        return;
    }

    function hasPendingResources(self)
    {
        function isEmpty(obj) {
            for (var key in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, key)) return false;
            }

            return true;
        }

        var hasPhantomPendingResources = !isEmpty(self._resourcesRequested);

        if (!hasPhantomPendingResources) {
            // why isEmpty(self._resourcesRequested) || !self._getAjaxRequestCount()) ?
            // if someone sends a sync XHR we only get a resourceRequested event but not a responseEvent so we need
            // to fall back for ajaxRequestCount as a safety net. See https://github.com/ariya/phantomjs/issues/11284
            return false;
        }

        var hasPendingResourcesInCore = (self._getAjaxRequestCount() || self._getImageLoadingCount());

        return hasPendingResourcesInCore;
    }

    var self = this;

    setTimeout(function () {
        if (self.aborted) {
            // call execute events one more time so it can trigger its callback and finish the test
            self._executeEvents(events, callback, i + 1);
            return;
        }

        if (!self._isLoading && !self._isInitializing && !self._isNavigationRequested && !hasPendingResources(self)) {
            self._executeEvents(events, callback, i + 1);
        } else {
            self._waitForNextEvent(events, callback, i, waitTime);
        }
    }, waitTime);
};

PageRenderer.prototype._setCorrectViewportSize = function (viewportSize) {
    if (!viewportSize) {
        viewportSize = {width: this._getViewportWidth(), height: this._getViewportHeight()};
    }

    this.webpage.viewportSize = viewportSize;
    var height = Math.max(viewportSize.height, this.webpage.evaluate(function() {
        return document.body.offsetHeight;
    }));
    this.webpage.viewportSize = {width: viewportSize.width, height: height};
};

PageRenderer.prototype._logMessage = function (message) {
    this.pageLogs.push(message);
};

PageRenderer.prototype._isUrlThatWeCareAbout = function (url) {

    return -1 === url.indexOf('proxy/misc/user/favicon.png?r=') && -1 === url.indexOf('proxy/misc/user/logo.png?r=');
};

PageRenderer.prototype._addUrlToQueue = function (url) {
    if (this._resourcesRequested[url]){
        this._resourcesRequested[url]++;
    } else {
        this._resourcesRequested[url] = 1;
    }
};

PageRenderer.prototype._removeUrlFromQueue = function (url) {
    if (this._resourcesRequested[url]){
        this._resourcesRequested[url]--;
        if (0 === this._resourcesRequested[url]) {
            delete this._resourcesRequested[url];
        }
    }
};

var linkObject = document.createElement('a');
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

        self._logMessage(msgStack.join('\n'));
    };

    linkObject.setAttribute('href', config.piwikUrl);
    var piwikHost = linkObject.hostname,
        piwikPort = linkObject.port;

    this.webpage.onResourceRequested = function (requestData, networkRequest) {
        var url = requestData.url;

        // replaces the requested URL to the piwik URL w/ a port, if it does not have one.  This allows us to run UI
        // tests when Piwik is on a port, w/o having to have different UI screenshots. (This is one half of the
        // solution, the other half is in config/environment/ui-test.php, where we remove all ports from Piwik URLs.)
        if (piwikPort && piwikPort != 0) {
            linkObject.setAttribute('href', url);

            if (linkObject.hostname == piwikHost && (!linkObject.port || linkObject.port == 0 || linkObject.port == 80)) {
                linkObject.port = piwikPort;
                url = linkObject.href;

                networkRequest.changeUrl(url);
            }
        }

        self._addUrlToQueue(url);

        if (VERBOSE) {
            self._logMessage('Requesting resource (#' + requestData.id + 'URL:' + url + ')');
        }
    };

    this.webpage.onResourceTimeout = function (request) {
        self._removeUrlFromQueue(request.url);

        if (!self.aborted && VERBOSE) {
            self._logMessage('Unable to load resource because of timeout (#' + request.id + 'URL:' + request.url + ')');
            self._logMessage('Error code: ' + request.errorCode + '. Description: ' + request.errorString);
        }
    };

    this.webpage.onResourceReceived = function (response) {
        var isStartStage = (response.stage === 'start');

        if (!isStartStage){
            self._removeUrlFromQueue(response.url);
        }

        if (VERBOSE || (isStartStage && response.status >= 400 && self._isUrlThatWeCareAbout(response.url))) {
            var message = 'Response (#' + response.id + ', stage "' + response.stage + '", size "' +
                response.bodySize + '", status "' + response.status + '"): ' + response.url;
            self._logMessage(message);
        }
    };

    this.webpage.onResourceError = function (resourceError) {
        self._removeUrlFromQueue(resourceError.url);

        if (!self.aborted && self._isUrlThatWeCareAbout(resourceError.url)) {
            self._logMessage('Unable to load resource (#' + resourceError.id + 'URL:' + resourceError.url + ')');
            self._logMessage('Error code: ' + resourceError.errorCode + '. Description: ' + resourceError.errorString);
        }
    };

    this.webpage.onConsoleMessage = function (message) {
        self._logMessage('Log: ' + message);
    };

    this.webpage.onAlert = function (message) {
        self._logMessage('Alert: ' + message);
    };

    this.webpage.onLoadStarted = function () {
        if (VERBOSE) {
            self._logMessage('onLoadStarted');
        }

        self._isInitializing = false;
        self._isLoading = true;
    };

    this.webpage.onPageCreated = function onPageCreated(popupPage) {
        if (VERBOSE) {
            self._logMessage('onPageCreated');
        }

        popupPage.onLoadFinished = function onLoadFinished() {
            self._isNavigationRequested = false;
        };
    };

    this.webpage.onUrlChanged = function onUrlChanged(url) {
        if (VERBOSE) {
            self._logMessage('onUrlChanged: ' + url);
        }
        self._isNavigationRequested = false;
    };

    this.webpage.onNavigationRequested = function (url, type, willNavigate, isMainFrame) {
        if (VERBOSE) {
            self._logMessage('onNavigationRequested: ' + url);
        }

        self._isInitializing = false;

        if (isMainFrame && self._requestedUrl !== url && willNavigate) {
            var currentUrl = self._requestedUrl;
            var newUrl = url;
            var pos = currentUrl.indexOf('#');
            if (pos !== -1) {
                currentUrl = currentUrl.substring(0, pos);
            }
            pos = newUrl.indexOf('#');
            if (pos !== -1) {
                newUrl = newUrl.substring(0, pos);
            }
            if (currentUrl !== newUrl) {
                self._isNavigationRequested = true;
            }

            self._requestedUrl = url;
        }
    }

    this.webpage.onLoadFinished = function (status) {
        if (status !== 'success' && VERBOSE) {
            self._logMessage('Page did not load successfully (it could be on purpose if a tests wants to test this behaviour): ' + status);
        } else if (VERBOSE) {
            self._logMessage('onLoadFinished: ' + status);
        }

        self._isInitializing = false;
        self._isLoading = false;
    };
};

PageRenderer.prototype.getPageContents = function () {
    var result = this.downloadedContents || this.webpage.content;

    if (/^<html><head><\/head><body>/.test(result)) {
        result = result.substring('<html><head></head><body>'.length);
    }

    if (/<\/body><\/html>$/.test(result)) {
        result = result.substring(0, result.length - '</body></html>'.length);
    }

    return result;
};

exports.PageRenderer = PageRenderer;
