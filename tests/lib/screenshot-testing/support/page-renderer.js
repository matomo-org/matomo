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
    'keyboard',
    'mainFrame',
    'metrics',
    'mouse',
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

const AUTO_WAIT_METHODS = {
    'goto': true,
    // TODO
};

var PageRenderer = function (baseUrl, page) {
    this.webpage = page;

    this._logMessage = [];
    this.pageLogs = [];
    this.baseUrl = baseUrl;
    this.lifeCycleEventEmitter = new EventEmitter();
    this.activeRequestCount = 0;

    if (this.baseUrl.substring(-1) !== '/') {
        this.baseUrl = this.baseUrl + '/';
    }

    this.webpage.setViewport({
        width: 1350,
        height: 768,
    });
    this._setupWebpageEvents();
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
            result = result.then(() => {
                return this.waitForNetworkIdle();
            });
        }

        return result;
    };
});

PageRenderer.prototype.waitForNetworkIdle = function () {
    return new Promise(resolve => {
        var self = this;

        if (!this.activeRequestCount) {
            resolve();
            return;
        }

        this.lifeCycleEventEmitter.on('lifecycleEvent', _onLifecycleEvent);

        function _onLifecycleEvent(event) {
            if (event.frameId === self.webpage.mainFrame()._id
                && event.name === 'networkIdle'
            ) {
                resolve();
                self.lifeCycleEventEmitter.removeListener('lifecycleEvent', _onLifecycleEvent);
            }
        }
    });
};

// TODO: implement load timeout?
/* TODO: timeout should be global mocha timeout
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
*/

// TODO: for capture() take screenshot & spit out the page logs


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
