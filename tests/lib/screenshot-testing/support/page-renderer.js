/*!
 * Piwik - free/libre analytics platform
 *
 * PageRenderer class for screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const urlModule = require('url');
const { EventEmitter } = require('events');

const parseUrl = urlModule.parse,
    formatUrl = urlModule.format;

const AJAX_IDLE_THRESHOLD = 500; // same as networkIdle event
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
    'queryObjects',
    'reload',
    'screenshot',
    'select',
    'setBypassCSP',
    'setCacheEnabled',
    'setContent',
    'setExtraHTTPHeaders',
    'setUserAgent',
    'tap',
    'target',
    'title',
    'type',
    'url',
    'viewport',
    'waitFor',
    'waitForFunction',
    'waitForNavigation',
    'waitForSelector',
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

var PageRenderer = function (baseUrl, page) {
    this.webpage = page;

    this.selectorMarkerClass = 0;
    this.pageLogs = [];
    this.baseUrl = baseUrl;
    this.lifeCycleEventEmitter = new EventEmitter();
    this.activeRequestCount = 0;

    if (this.baseUrl.substring(-1) !== '/') {
        this.baseUrl = this.baseUrl + '/';
    }

    PAGE_PROPERTIES_TO_PROXY.forEach((propertyName) => {
        Object.defineProperty(this, propertyName, {
            value: page[propertyName],
            writable: false,
        });
    });

    this.webpage.setViewport({
        width: 1350,
        height: 768,
    });
    this._setupWebpageEvents();
};

PageRenderer.prototype._reset = function () {
    this.pageLogs = [];
};

PageRenderer.prototype.isVisible = function (selector) {
    return this.webpage.evaluate(() => {
        return $(selector).is(':visible');
    });
};

PageRenderer.prototype.jQuery = function (selector) {
    const selectorMarkerClass = '__selector_marker_' + this.selectorMarkerClass;

    ++this.selectorMarkerClass;

    return this.webpage.evaluate((selectorMarkerClass, s) => {
        $(s).addClass(selectorMarkerClass);
    }, selectorMarkerClass, selector).then(() => {
        return this.webpage.$('.' + selectorMarkerClass);
    });
};

PAGE_METHODS_TO_PROXY.forEach(function (methodName) {
    PageRenderer.prototype[methodName] = function (...args) {
        if (methodName === 'goto') {
            let url = args[0];
            if (url.indexOf("://") === -1) {
                url = this.baseUrl + url;
            }
            args[0] = url;
        }

        let result = this.webpage[methodName](...args);

        if (result && result.then && AUTO_WAIT_METHODS[methodName]) {
            result = result.then((value) => {
                return this.waitForNetworkIdle().then(() => value);
            });
        }

        return result;
    };
});

PageRenderer.prototype.waitForNetworkIdle = async function () {
    while (this.activeRequestCount > 0) {
        await new Promise(resolve => setTimeout(resolve, AJAX_IDLE_THRESHOLD));
    }
};

PageRenderer.prototype._isUrlThatWeCareAbout = function (url) {
    return -1 === url.indexOf('proxy/misc/user/favicon.png?r=') && -1 === url.indexOf('proxy/misc/user/logo.png?r=');
};

PageRenderer.prototype._logMessage = function (message) {
    this.pageLogs.push(message);
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
                $('html').addClass('uiTest');
                $.fx.off = true;

                var css = document.createElement('style');
                css.type = 'text/css';
                css.innerHTML = '* { -webkit-transition: none !important; transition: none !important; -webkit-animation: none !important; animation: none !important; }';
                document.body.appendChild(css);
            }
        });
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
            const parsedRequestUrl = parseUrl(parsedPiwikUrl);

            if (parsedRequestUrl.hostname === piwikHost && (!parsedRequestUrl.port || parsedRequestUrl.port === 0 || parsedRequestUrl.port === 80)) {
                parsedRequestUrl.port = piwikPort;
                url = formatUrl(parsedRequestUrl);

                request.continue({
                    url,
                });

                return;
            }

            request.continue();
        }

        if (VERBOSE) {
            this._logMessage('Requesting resource (#' + requestData.id + 'URL:' + url + ')');
        }
    });

    // TODO: self.aborted?
    this.webpage.on('requestfailed', (request) => {
        --this.activeRequestCount;

        if (!VERBOSE) {
            const failure = request.failure();
            const errorMessage = failure ? failure.errorText : 'Unknown error';
            this._logMessage('Unable to load resource (URL:' + request.url() + '): ' + errorMessage);
        }
    });

    this.webpage.on('requestfinished', async (request) => {
        --this.activeRequestCount;

        const response = request.response();
        if (VERBOSE || (response.status() >= 400 && this._isUrlThatWeCareAbout(request.url()))) {
            const bodySize = (await response.buffer());
            const message = 'Response (size "' + bodySize + '", status "' + response.status() + '"): ' + request.url();
            this._logMessage(message);
        }
    });

    this.webpage.on('console', (consoleMessage) => {
        const messageText = util.format(consoleMessage.text(), ...consoleMessage.args());
        this._logMessage(`Log: ${messageText}`);
    });

    this.webpage.on('dialog', (dialog) => {
        this._logMessage(`Alert: ${dialog.message()}`);
    });
};

exports.PageRenderer = PageRenderer;
