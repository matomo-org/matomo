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
// A request still pending after this long is treated as stalled and no longer blocks network-idle
// detection. With request interception enabled, Chrome may leave a redundant resource fetch (e.g. a
// webfont revalidation that is never needed because the font already rendered from cache) pending
// indefinitely, never emitting requestfinished/requestfailed, which would otherwise hang
// waitForNetworkIdle forever.
const STALLED_REQUEST_THRESHOLD = 5000;
const VERBOSE = false;
const PAGE_METHODS_TO_PROXY = [
    '$',
    '$$',
    '$$eval',
    '$eval',
    'bringToFront',
    'click',
    'content',
    'cookies',
    'coverage',
    'deleteCookie',
    'emulateMediaFeatures',
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
    'screenshotNoResize',
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

// Chrome 149 headless workaround. Puppeteer's ElementHandle.scrollIntoViewIfNeeded() - run before every
// native click and element screenshot - first probes visibility via isIntersectingViewport(), which
// evaluates an in-page IntersectionObserver and resolves from its callback. On some pages that callback
// never fires (the observer needs a rendering step the idle headless page does not perform), so the
// promise never resolves and the click/screenshot hangs until the 240s test timeout. Bound the probe and
// fall back to scrollIntoView() (a plain evaluate that cannot hang) so a stuck observer can no longer
// wedge the suite. When the observer does fire (the normal case) this behaves exactly as before.
(function boundScrollIntoViewIfNeeded() {
    let ElementHandle;
    try {
        ElementHandle = require('puppeteer').ElementHandle;
    } catch (e) {
        return;
    }
    if (!ElementHandle || !ElementHandle.prototype || typeof ElementHandle.prototype.scrollIntoViewIfNeeded !== 'function') {
        return;
    }
    ElementHandle.prototype.scrollIntoViewIfNeeded = async function () {
        let intersecting = false;
        try {
            intersecting = await Promise.race([
                this.isIntersectingViewport({ threshold: 1 }),
                new Promise((resolve) => setTimeout(() => resolve(false), 2000)),
            ]);
        } catch (e) {
            intersecting = false;
        }
        if (intersecting) {
            return;
        }
        await this.scrollIntoView();
    };
})();

var PageRenderer = function (baseUrl, browser, originalUserAgent) {

    this.browser = browser;
    this.originalUserAgent = originalUserAgent;

    this.selectorMarkerClass = 0;
    this.pageLogs = [];
    this.baseUrl = baseUrl;
    this.lifeCycleEventEmitter = new EventEmitter();
    this.pendingRequests = new Map();

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
    this.browserContext = await this.browser.createBrowserContext();
    this.webpage = await this.browserContext.newPage();

    if (this.pendingRequests.size > 0) {
      console.log('! pendingRequests size is ' + this.pendingRequests.size + '. Resetting it as new browserContext has started.');
      // clear pending requests, to ensure unresolved requests from previous suites don't cause any issues
      this.pendingRequests.clear();
    }

    // Present the test browser as a regular Chrome rather than headless Chrome. Puppeteer reports a
    // "HeadlessChrome" user agent and Sec-CH-UA brand, which the TrackingSpamPrevention plugin blocks
    // by default (block_headless). Without this, any tracking that originates from the test browser -
    // the JS tracker, and server-side requests that inherit the browser user agent - is treated as a
    // headless bot and excluded, so those tests record no visits/data.
    const defaultUserAgent = await this.browser.userAgent();
    const chromeUserAgent = defaultUserAgent.replace('HeadlessChrome', 'Chrome');
    await this.webpage.setUserAgent(chromeUserAgent);

    PAGE_PROPERTIES_TO_PROXY.forEach((propertyName) => {
      Object.defineProperty(this, propertyName, {
        value: this.webpage[propertyName],
        writable: true,
      });
    });

    await this.webpage._client().send('Animation.setPlaybackRate', { playbackRate: 50 }); // make animations run 50 times faster, so we don't have to wait as much
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
 * Waits for the given number of milliseconds.
 *
 * Puppeteer removed page.waitForTimeout in v22, so we provide it here. Specs (including ones in
 * plugin submodules) rely on the page.waitForTimeout(ms) API, so this keeps them working unchanged.
 *
 * @param {number} milliseconds
 */
PageRenderer.prototype.waitForTimeout = function (milliseconds) {
    return new Promise((resolve) => setTimeout(resolve, milliseconds));
}

/**
 * For BC only. Puppeteer drop support for waitFor function in Version 10
 * @param selectorOrTimeoutOrFunction
 */
PageRenderer.prototype.waitFor = function (selectorOrTimeoutOrFunction) {
    console.log('Using page.waitFor is deprecated, please use one of this instead: waitForSelector, waitForFunction, waitForTimeout');
    if (typeof selectorOrTimeoutOrFunction === 'function') {
        return this.webpage.waitForFunction(selectorOrTimeoutOrFunction)
    } else if (typeof selectorOrTimeoutOrFunction === 'number') {
        return this.waitForTimeout(selectorOrTimeoutOrFunction)
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

PageRenderer.prototype.resizeViewportToFullPage = async function () {
    await this.webpage.waitForFunction(() => !! document.documentElement);

    const dims = await this.webpage.evaluate(() => JSON.stringify({
        width: document.documentElement.scrollWidth,
        height: document.documentElement.scrollHeight,
    }));

    await this.webpage.setViewport(JSON.parse(dims));
};

PageRenderer.prototype.screenshotSelector = async function (selector, shouldResizeViewport = true) {
    await this.waitForFunction(() => !! window.$, { timeout: 60000 });

    if (shouldResizeViewport) {
        await this.resizeViewportToFullPage();
    }

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

    return await this.screenshotNoResize({
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
            result = this.resizeViewportToFullPage()
              .then(() => {
                return this.webpage[methodName](...args);
              });
        } else if (methodName === 'screenshotNoResize') {
            // we do not need to resize the viewport since we did it on top anyway
            return this.webpage.screenshot(...args);
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

PageRenderer.prototype.getActiveRequestCount = function () {
    const now = Date.now();
    let count = 0;
    for (const startedAt of this.pendingRequests.values()) {
        if (now - startedAt < STALLED_REQUEST_THRESHOLD) {
            count++;
        }
    }
    return count;
};

PageRenderer.prototype.waitForNetworkIdle = async function () {
    await new Promise(resolve => setTimeout(resolve, AJAX_IDLE_THRESHOLD));

    while (this.getActiveRequestCount() > 0) {
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
        await this.waitForTimeout(200); // wait for the browser to request the images
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
    await this.webpage._client().send('Network.clearBrowserCookies');
    await this.waitForTimeout(250);
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

        this.webpage.addStyleTag({content: '* { caret-color: transparent !important; -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }'
            // Materialize opens modals with a JS (anime.js / requestAnimationFrame) scale+fade animation
            // rather than a CSS transition, so the rule above does not disable it. Under the modern
            // headless Chrome that animation does not always advance (rAF is stalled on the idle page),
            // leaving the modal at its initial transform: scaleX(0.8) scaleY(0.8); opacity: 0. The
            // scaled-down box then makes element screenshots clip the modal. Force any open modal to its
            // final visible state so it is captured correctly regardless of the animation.
            + ' .modal.open { transform: none !important; opacity: 1 !important; }'});
    });

    this.webpage._client().on('Page.lifecycleEvent', (event) => {
        this.lifeCycleEventEmitter.emit('lifecycleEvent', event);
    });

    const parsedPiwikUrl = parseUrl(config.piwikUrl);

    var piwikHost = parsedPiwikUrl.hostname,
        piwikPort = parsedPiwikUrl.port;

    // Disable the browser cache so each navigation re-fetches the page. Puppeteer 8 disabled the
    // cache implicitly when request interception was enabled; that no longer happens with the
    // cooperative interception mode in current Puppeteer, which let navigations serve stale pages
    // (e.g. a report rendered before a testEnvironment identity/permission change), causing flaky,
    // state-dependent screenshot diffs under the modern headless Chrome.
    this.webpage.setCacheEnabled(false);

    // Only enable request interception when it is actually needed to rewrite the Piwik URL to add a
    // port. Interception is off for the common (portless) case: current Puppeteer's cooperative
    // interception mode makes every request go through the handler, which adds latency and can leave
    // requests pending under the modern headless Chrome, causing intermittent, timing-dependent
    // failures (e.g. modals/report widgets that never finish loading). We still track requests for
    // the network-idle wait via the request/requestfinished/requestfailed events either way.
    const rewritePiwikPort = !!(piwikPort && parseInt(piwikPort, 10) !== 0);
    if (rewritePiwikPort) {
        this.webpage.setRequestInterception(true);
    }

    this.webpage.on('request', (request) => {
        this.pendingRequests.set(request, Date.now());

        if (!rewritePiwikPort) {
            if (VERBOSE) {
                this._logMessage('Requesting resource (URL:' + request.url() + ')');
            }
            return;
        }

        var url = request.url();

        // replaces the requested URL to the piwik URL w/ a port, if it does not have one.  This allows us to run UI
        // tests when Piwik is on a port, w/o having to have different UI screenshots. (This is one half of the
        // solution, the other half is in config/environment/ui-test.php, where we remove all ports from Piwik URLs.)
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

        request.continue();

        if (VERBOSE) {
            this._logMessage('Requesting resource (#' + request.id + 'URL:' + url + ')');
        }
    });

    // TODO: self.aborted?
    this.webpage.on('requestfailed', async (request) => {
        this.pendingRequests.delete(request);

        const failure = request.failure();
        const response = request.response();
        const errorMessage = failure ? failure.errorText : 'Unknown error';

        if (!VERBOSE) {
            this._logMessage('Unable to load resource (URL:' + request.url() + '): ' + errorMessage);
        }
    });

    this.webpage.on('requestfinished', async (request) => {
        this.pendingRequests.delete(request);

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
        const args = await Promise.all(consoleMessage.args().map(arg => arg.evaluate(arg => {
            if (arg instanceof Error) {
                return arg.stack || arg.message;
            }
            return arg;
        }))).catch((e) => {
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
