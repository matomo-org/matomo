/*!
 * Matomo - free/libre analytics platform
 *
 * PageRenderer class for screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const urlModule = require('url');
const util = require('util');
const { EventEmitter } = require('events');

const parseUrl = urlModule.parse,
    formatUrl = urlModule.format;

const AJAX_IDLE_THRESHOLD = 750; // same as networkIdle event
const VERBOSE = false;
const PAGE_METHODS_TO_PROXY = [
    '$',
    '$$',
    '$$eval',
    '$eval',
    '$x',
    'bringToFront',
    'click',
    'content',
    'cookies',
    'coverage',
    'deleteCookie',
    'evaluate',
    'evaluateHandle',
    'evaluateOnNewDocument',
    'exposeFunction',
    'focus',
    'frames',
    'goBack',
    'goForward',
    'goto',
    'hover',
    'mainFrame',
    'metrics',
    'on',
    'once',
    'queryObjects',
    'reload',
    'screenshot',
    'select',
    'setBypassCSP',
    'setCacheEnabled',
    'setContent',
    'setExtraHTTPHeaders',
    'setUserAgent',
    'setCookie',
    'tap',
    'target',
    'title',
    'url',
    'viewport',
    'waitForFunction',
    'waitForNavigation',
    'waitForSelector',
    'waitForTimeout',
    'waitForXPath',
];

const PAGE_PROPERTIES_TO_PROXY = [
    'mouse',
    'keyboard',
    'touchscreen',
];

const AUTO_WAIT_METHODS = {// TODO: remove this to keep it consistent?
    'goBack': true,
    'goForward': true,
    'goto': true,
    'reload': true,
};

var PageRenderer = function (baseUrl, browser, originalUserAgent) {

    this.browser = browser;
    this.originalUserAgent = originalUserAgent;

    this.selectorMarkerClass = 0;
    this.pageLogs = [];
    this.baseUrl = baseUrl;
    this.lifeCycleEventEmitter = new EventEmitter();
    this.activeRequestCount = 0;

    if (this.baseUrl.substring(-1) !== '/') {
        this.baseUrl = this.baseUrl + '/';
    }
};

PageRenderer.prototype._reset = function () {
    this.pageLogs = [];
    this.webpage.setViewport({
        width: 1350,
        height: 768,
    });
};

PageRenderer.prototype.createPage = async function () {
    if (this.browserContext) {
      await this.browserContext.close();
    }
    this.browserContext = await this.browser.createIncognitoBrowserContext();
    this.webpage = await this.browserContext.newPage();

    PAGE_PROPERTIES_TO_PROXY.forEach((propertyName) => {
      Object.defineProperty(this, propertyName, {
        value: this.webpage[propertyName],
        writable: true,
      });
    });

    await this.webpage._client.send('Animation.setPlaybackRate', { playbackRate: 50 }); // make animations run 50 times faster, so we don't have to wait as much
    await this.webpage.setViewport({
      width: 1350,
      height: 768,
    });
    await this.webpage.mouse.move(0, 0);
    await this.webpage.setExtraHTTPHeaders({
      'Accept-Language': 'en-US'
    });
    this._setupWebpageEvents();
};

/**
 * For BC only. Puppeteer drop support for waitFor function in Version 10
 * @param selectorOrTimeoutOrFunction
 */
PageRenderer.prototype.waitFor = function (selectorOrTimeoutOrFunction) {
    console.log('Using page.waitFor is deprecated, please use one of this instead: waitForSelector, waitForFunction, waitForTimeout');
    if (typeof selectorOrTimeoutOrFunction === 'function') {
        return this.webpage.waitForFunction(selectorOrTimeoutOrFunction)
    } else if (typeof selectorOrTimeoutOrFunction === 'number') {
        return this.webpage.waitForTimeout(selectorOrTimeoutOrFunction)
    } else if (typeof selectorOrTimeoutOrFunction === 'string') {
        return this.webpage.waitForSelector(selectorOrTimeoutOrFunction)
    }
}

PageRenderer.prototype.type = async function (...args) {
  await this.webpage.type(...args);
  await this.waitForTimeout(50); // puppeteer types faster than vue can update the model state
};

PageRenderer.prototype.isVisible = function (selector) {
    return this.webpage.evaluate(() => {
        return jQuery(selector).is(':visible');
    });
};

PageRenderer.prototype.jQuery = async function (selector, options = {}) {
    const selectorMarkerClass = '__selector_marker_' + this.selectorMarkerClass;

    ++this.selectorMarkerClass;

    await this.waitForFunction(() => !! window.jQuery);

    if (options.waitFor) {
        try {
            await this.waitForFunction((selector) => {
                return !!jQuery(selector).length;
            }, {}, selector);
        } catch (err) {
            err.message += " (selector = " + selector + ")";
            throw err;
        }
    }

    await this.webpage.evaluate((selectorMarkerClass, s) => {
        jQuery(s).addClass(selectorMarkerClass);
    }, selectorMarkerClass, selector);

    return await this.webpage.$('.' + selectorMarkerClass);
};

PageRenderer.prototype.screenshotSelector = async function (selector) {
    await this.waitForFunction(() => !! window.$, { timeout: 60000 });

    const result = await this.webpage.evaluate(function (selector) {
        window.jQuery('html').addClass('uiTest');

        var docWidth = window.jQuery(document).width(),
            docHeight = window.jQuery(document).height();

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
                if (!jQuery(node).is(':visible')) {
                    return;
                }

                var rect = jQuery(node).offset();
                rect.width = jQuery(node).outerWidth();
                rect.height = jQuery(node).outerHeight();
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

    return await this.screenshot({
        clip: {
            x: result.left,
            y: result.top,
            width: result.width,
            height: result.height,
        },
    });
};

PAGE_METHODS_TO_PROXY.forEach(function (methodName) {
    PageRenderer.prototype[methodName] = function (...args) {
        if (methodName === 'goto') {
            let url = args[0];
            if (url.indexOf("://") === -1 && url !== 'about:blank') {
                url = this.baseUrl + url;
            }
            args[0] = url;
        }

        if (methodName === 'goto' || methodName === 'reload') {
            if (typeof args[1] === 'object') {
                args[1].timeout = 0;
            } else {
                args[1] = {
                    timeout: 0,
                };
            }
        }

        let result;
        if (methodName === 'screenshot') {
            // change viewport to entire page before screenshot
            result = this.webpage.waitForFunction(() => !! document.documentElement)
                .then(() => {
                    return this.webpage.evaluate(() => JSON.stringify({
                        width: document.documentElement.scrollWidth,
                        height: document.documentElement.scrollHeight,
                    }));
                }).then((dims) => {
                    return this.webpage.setViewport(JSON.parse(dims));
                }).then(() => {
                    return this.webpage[methodName](...args);
                });
        } else {
            result = this.webpage[methodName](...args);
        }

        if (result && result.then && AUTO_WAIT_METHODS[methodName]) {
            result = result.then((value) => {
                return this.waitForNetworkIdle().then(() => value);
            });
        }

        return result;
    };
});

PageRenderer.prototype.waitForNetworkIdle = async function () {
    await new Promise(resolve => setTimeout(resolve, AJAX_IDLE_THRESHOLD));

    while (this.activeRequestCount > 0) {
        await new Promise(resolve => setTimeout(resolve, AJAX_IDLE_THRESHOLD));
    }

    await this.waitForLazyImages();

    // wait for any queued vue logic
    await this.webpage.evaluate(function () {
        if (window.Vue) {
          return window.Vue.nextTick(function () {
              // wait
          });
        }
    });

    // if the visitor map is shown trigger a window resize, to ensure map always has the same height/width
    await this.webpage.evaluate(function () {
        if (window.jQuery && window.jQuery('.UserCountryMap_map').length) {
            window.jQuery(window).trigger('resize');
        }
    });
};

PageRenderer.prototype.waitForLazyImages = async function () {
    // remove loading attribute from images
    const hasImages = await this.webpage.evaluate(function(){
        if (!window.jQuery) {
            return false; // skip if no jquery is available
        }

        var $ = window.jQuery;

        var images = $('img[loading]');
        if (images.length > 0) {
            images.removeAttr('loading');
            return true;
        }
        return false;
    });

    if (hasImages) {
        await this.webpage.waitForTimeout(200); // wait for the browser to request the images
        await this.waitForNetworkIdle(); // wait till all requests are finished
    }
};

PageRenderer.prototype.downloadUrl = async function (url) {
    return await this.webpage.evaluate(function (url) {
        var $ = window.jQuery;

        return $.ajax({
            type: "GET",
            url: url,
            async: false
        }).responseText;
    }, url);
};

PageRenderer.prototype._isUrlThatWeCareAbout = function (url) {
    return -1 === url.indexOf('proxy/misc/user/favicon.png?r=') && -1 === url.indexOf('proxy/misc/user/logo.png?r=');
};

PageRenderer.prototype._logMessage = function (message) {
    this.pageLogs.push(message);
};

PageRenderer.prototype.clearCookies = async function () {
    // see https://github.com/GoogleChrome/puppeteer/issues/1632#issuecomment-353086292
    await this.webpage._client.send('Network.clearBrowserCookies');
    await this.webpage.waitForTimeout(250);
};

PageRenderer.prototype._setupWebpageEvents = function () {
    this.webpage.on('error', (message, trace) => {
        var msgStack = ['Webpage error: ' + message];
        if (trace && trace.length) {
            msgStack.push('trace:');
            trace.forEach(function(t) {
                msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function + '")' : ''));
            });
        }

        this._logMessage(msgStack.join('\n'));
    });

    this.webpage.on('load', () => {
        this.webpage.evaluate(function () {
            var $ = window.jQuery;
            if ($) {
                jQuery('html').addClass('uiTest');
                $.fx.off = true;
            }
        });

        this.webpage.addStyleTag({content: '* { caret-color: transparent !important; -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }'});
    });

    this.webpage._client.on('Page.lifecycleEvent', (event) => {
        this.lifeCycleEventEmitter.emit('lifecycleEvent', event);
    });

    const parsedPiwikUrl = parseUrl(config.piwikUrl);

    var piwikHost = parsedPiwikUrl.hostname,
        piwikPort = parsedPiwikUrl.port;

    this.webpage.setRequestInterception(true);
    this.webpage.on('request', (request) => {
        ++this.activeRequestCount;

        var url = request.url();

        // replaces the requested URL to the piwik URL w/ a port, if it does not have one.  This allows us to run UI
        // tests when Piwik is on a port, w/o having to have different UI screenshots. (This is one half of the
        // solution, the other half is in config/environment/ui-test.php, where we remove all ports from Piwik URLs.)
        if (piwikPort && piwikPort !== 0) {
            const parsedRequestUrl = parseUrl(url);

            if (parsedRequestUrl.hostname === piwikHost && (!parsedRequestUrl.port || parseInt(parsedRequestUrl.port) === 0 || parseInt(parsedRequestUrl.port) === 80)) {

                parsedRequestUrl.port = piwikPort;
                parsedRequestUrl.host = piwikHost + ':' + piwikPort;

                url = formatUrl(parsedRequestUrl);

                request.continue({
                    url,
                });


                if (VERBOSE) {
                    this._logMessage('Requesting resource (#' + request.id + 'URL:' + url + ')');
                }

                return;
            }
        }

        request.continue();

        if (VERBOSE) {
            this._logMessage('Requesting resource (#' + request.id + 'URL:' + url + ')');
        }
    });

    // TODO: self.aborted?
    this.webpage.on('requestfailed', async (request) => {
        --this.activeRequestCount;

        const failure = request.failure();
        const response = request.response();
        const errorMessage = failure ? failure.errorText : 'Unknown error';

        if (!VERBOSE) {
            this._logMessage('Unable to load resource (URL:' + request.url() + '): ' + errorMessage);
        }
    });

    this.webpage.on('requestfinished', async (request) => {
        --this.activeRequestCount;

        const response = request.response();

        if (VERBOSE || (response.status() >= 400 && this._isUrlThatWeCareAbout(request.url()))) {
            let bodyLength = 0;
            let bodyContent = '';
            try {
                const body = await response.buffer();
                bodyLength = body.length;
                bodyContent = body.toString();
            } catch (e) {
            }
            const message = 'Response (size "' + bodyLength + '", status "' + response.status() + '"): ' + request.url() + "\n" + bodyContent.substring(0, 2000);
            this._logMessage(message);
        }
    });

    this.webpage.on('console', async (consoleMessage) => {
        const args = await Promise.all(consoleMessage.args().map(arg => arg.executionContext().evaluate(arg => {
            if (arg instanceof Error) {
                return arg.stack || arg.message;
            }
            return arg;
        }, arg))).catch((e) => {
          console.log(`Could not print message: ${e.message}`);
          console.log(consoleMessage.text());
        });
        const message = (args || []).join(' ');
        this._logMessage(`Log: ${message}`);
    });

    this.webpage.on('dialog', (dialog) => {
        this._logMessage(`Alert: ${dialog.message()}`);
    });
};

PageRenderer.prototype.getPageLogsString = function(indent) {
    var result = "";
    if (this.pageLogs.length) {
        result = "\n\n" + indent + "Rendering logs:\n";
        this.pageLogs.slice(0, 5).forEach(function (message) {
            result += indent + "  " + message.replace(/\n/g, "\n" + indent + "  ") + "\n";
        });
        result = result.substring(0, result.length - 1);
    }
    return result;
};

PageRenderer.prototype.getWholeCurrentUrl = function () {
    return this.webpage.evaluate(() => window.location.href);
};

PageRenderer.prototype.allowClipboard = async function () {
    await this.browserContext.overridePermissions(await this.getWholeCurrentUrl(), ['clipboard-read', 'clipboard-write']);
};

exports.PageRenderer = PageRenderer;
