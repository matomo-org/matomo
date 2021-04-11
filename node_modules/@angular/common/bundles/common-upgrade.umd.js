/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('rxjs'), require('@angular/common'), require('@angular/core'), require('@angular/upgrade/static')) :
    typeof define === 'function' && define.amd ? define('@angular/common/upgrade', ['exports', 'rxjs', '@angular/common', '@angular/core', '@angular/upgrade/static'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.common = global.ng.common || {}, global.ng.common.upgrade = {}), global.rxjs, global.ng.common, global.ng.core, global.ng.upgrade.static));
}(this, (function (exports, rxjs, common, core, _static) { 'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */
    /* global Reflect, Promise */
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b)
                if (b.hasOwnProperty(p))
                    d[p] = b[p]; };
        return extendStatics(d, b);
    };
    function __extends(d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    }
    var __assign = function () {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s)
                    if (Object.prototype.hasOwnProperty.call(s, p))
                        t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };
    function __rest(s, e) {
        var t = {};
        for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
                t[p] = s[p];
        if (s != null && typeof Object.getOwnPropertySymbols === "function")
            for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
                if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                    t[p[i]] = s[p[i]];
            }
        return t;
    }
    function __decorate(decorators, target, key, desc) {
        var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
        if (typeof Reflect === "object" && typeof Reflect.decorate === "function")
            r = Reflect.decorate(decorators, target, key, desc);
        else
            for (var i = decorators.length - 1; i >= 0; i--)
                if (d = decorators[i])
                    r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
        return c > 3 && r && Object.defineProperty(target, key, r), r;
    }
    function __param(paramIndex, decorator) {
        return function (target, key) { decorator(target, key, paramIndex); };
    }
    function __metadata(metadataKey, metadataValue) {
        if (typeof Reflect === "object" && typeof Reflect.metadata === "function")
            return Reflect.metadata(metadataKey, metadataValue);
    }
    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try {
                step(generator.next(value));
            }
            catch (e) {
                reject(e);
            } }
            function rejected(value) { try {
                step(generator["throw"](value));
            }
            catch (e) {
                reject(e);
            } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }
    function __generator(thisArg, body) {
        var _ = { label: 0, sent: function () { if (t[0] & 1)
                throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function () { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f)
                throw new TypeError("Generator is already executing.");
            while (_)
                try {
                    if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done)
                        return t;
                    if (y = 0, t)
                        op = [op[0] & 2, t.value];
                    switch (op[0]) {
                        case 0:
                        case 1:
                            t = op;
                            break;
                        case 4:
                            _.label++;
                            return { value: op[1], done: false };
                        case 5:
                            _.label++;
                            y = op[1];
                            op = [0];
                            continue;
                        case 7:
                            op = _.ops.pop();
                            _.trys.pop();
                            continue;
                        default:
                            if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) {
                                _ = 0;
                                continue;
                            }
                            if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) {
                                _.label = op[1];
                                break;
                            }
                            if (op[0] === 6 && _.label < t[1]) {
                                _.label = t[1];
                                t = op;
                                break;
                            }
                            if (t && _.label < t[2]) {
                                _.label = t[2];
                                _.ops.push(op);
                                break;
                            }
                            if (t[2])
                                _.ops.pop();
                            _.trys.pop();
                            continue;
                    }
                    op = body.call(thisArg, _);
                }
                catch (e) {
                    op = [6, e];
                    y = 0;
                }
                finally {
                    f = t = 0;
                }
            if (op[0] & 5)
                throw op[1];
            return { value: op[0] ? op[1] : void 0, done: true };
        }
    }
    var __createBinding = Object.create ? (function (o, m, k, k2) {
        if (k2 === undefined)
            k2 = k;
        Object.defineProperty(o, k2, { enumerable: true, get: function () { return m[k]; } });
    }) : (function (o, m, k, k2) {
        if (k2 === undefined)
            k2 = k;
        o[k2] = m[k];
    });
    function __exportStar(m, exports) {
        for (var p in m)
            if (p !== "default" && !exports.hasOwnProperty(p))
                __createBinding(exports, m, p);
    }
    function __values(o) {
        var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
        if (m)
            return m.call(o);
        if (o && typeof o.length === "number")
            return {
                next: function () {
                    if (o && i >= o.length)
                        o = void 0;
                    return { value: o && o[i++], done: !o };
                }
            };
        throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
    }
    function __read(o, n) {
        var m = typeof Symbol === "function" && o[Symbol.iterator];
        if (!m)
            return o;
        var i = m.call(o), r, ar = [], e;
        try {
            while ((n === void 0 || n-- > 0) && !(r = i.next()).done)
                ar.push(r.value);
        }
        catch (error) {
            e = { error: error };
        }
        finally {
            try {
                if (r && !r.done && (m = i["return"]))
                    m.call(i);
            }
            finally {
                if (e)
                    throw e.error;
            }
        }
        return ar;
    }
    function __spread() {
        for (var ar = [], i = 0; i < arguments.length; i++)
            ar = ar.concat(__read(arguments[i]));
        return ar;
    }
    function __spreadArrays() {
        for (var s = 0, i = 0, il = arguments.length; i < il; i++)
            s += arguments[i].length;
        for (var r = Array(s), k = 0, i = 0; i < il; i++)
            for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
                r[k] = a[j];
        return r;
    }
    ;
    function __await(v) {
        return this instanceof __await ? (this.v = v, this) : new __await(v);
    }
    function __asyncGenerator(thisArg, _arguments, generator) {
        if (!Symbol.asyncIterator)
            throw new TypeError("Symbol.asyncIterator is not defined.");
        var g = generator.apply(thisArg, _arguments || []), i, q = [];
        return i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i;
        function verb(n) { if (g[n])
            i[n] = function (v) { return new Promise(function (a, b) { q.push([n, v, a, b]) > 1 || resume(n, v); }); }; }
        function resume(n, v) { try {
            step(g[n](v));
        }
        catch (e) {
            settle(q[0][3], e);
        } }
        function step(r) { r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r); }
        function fulfill(value) { resume("next", value); }
        function reject(value) { resume("throw", value); }
        function settle(f, v) { if (f(v), q.shift(), q.length)
            resume(q[0][0], q[0][1]); }
    }
    function __asyncDelegator(o) {
        var i, p;
        return i = {}, verb("next"), verb("throw", function (e) { throw e; }), verb("return"), i[Symbol.iterator] = function () { return this; }, i;
        function verb(n, f) { i[n] = o[n] ? function (v) { return (p = !p) ? { value: __await(o[n](v)), done: n === "return" } : f ? f(v) : v; } : f; }
    }
    function __asyncValues(o) {
        if (!Symbol.asyncIterator)
            throw new TypeError("Symbol.asyncIterator is not defined.");
        var m = o[Symbol.asyncIterator], i;
        return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i);
        function verb(n) { i[n] = o[n] && function (v) { return new Promise(function (resolve, reject) { v = o[n](v), settle(resolve, reject, v.done, v.value); }); }; }
        function settle(resolve, reject, d, v) { Promise.resolve(v).then(function (v) { resolve({ value: v, done: d }); }, reject); }
    }
    function __makeTemplateObject(cooked, raw) {
        if (Object.defineProperty) {
            Object.defineProperty(cooked, "raw", { value: raw });
        }
        else {
            cooked.raw = raw;
        }
        return cooked;
    }
    ;
    var __setModuleDefault = Object.create ? (function (o, v) {
        Object.defineProperty(o, "default", { enumerable: true, value: v });
    }) : function (o, v) {
        o["default"] = v;
    };
    function __importStar(mod) {
        if (mod && mod.__esModule)
            return mod;
        var result = {};
        if (mod != null)
            for (var k in mod)
                if (Object.hasOwnProperty.call(mod, k))
                    __createBinding(result, mod, k);
        __setModuleDefault(result, mod);
        return result;
    }
    function __importDefault(mod) {
        return (mod && mod.__esModule) ? mod : { default: mod };
    }
    function __classPrivateFieldGet(receiver, privateMap) {
        if (!privateMap.has(receiver)) {
            throw new TypeError("attempted to get private field on non-instance");
        }
        return privateMap.get(receiver);
    }
    function __classPrivateFieldSet(receiver, privateMap, value) {
        if (!privateMap.has(receiver)) {
            throw new TypeError("attempted to set private field on non-instance");
        }
        privateMap.set(receiver, value);
        return value;
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function stripPrefix(val, prefix) {
        return val.startsWith(prefix) ? val.substring(prefix.length) : val;
    }
    function deepEqual(a, b) {
        if (a === b) {
            return true;
        }
        else if (!a || !b) {
            return false;
        }
        else {
            try {
                if ((a.prototype !== b.prototype) || (Array.isArray(a) && Array.isArray(b))) {
                    return false;
                }
                return JSON.stringify(a) === JSON.stringify(b);
            }
            catch (e) {
                return false;
            }
        }
    }
    function isAnchor(el) {
        return el.href !== undefined;
    }
    function isPromise(obj) {
        // allow any Promise/A+ compliant thenable.
        // It's up to the caller to ensure that obj.then conforms to the spec
        return !!obj && typeof obj.then === 'function';
    }

    var PATH_MATCH = /^([^?#]*)(\?([^#]*))?(#(.*))?$/;
    var DOUBLE_SLASH_REGEX = /^\s*[\\/]{2,}/;
    var IGNORE_URI_REGEXP = /^\s*(javascript|mailto):/i;
    var DEFAULT_PORTS = {
        'http:': 80,
        'https:': 443,
        'ftp:': 21
    };
    /**
     * Location service that provides a drop-in replacement for the $location service
     * provided in AngularJS.
     *
     * @see [Using the Angular Unified Location Service](guide/upgrade#using-the-unified-angular-location-service)
     *
     * @publicApi
     */
    var $locationShim = /** @class */ (function () {
        function $locationShim($injector, location, platformLocation, urlCodec, locationStrategy) {
            var _this = this;
            this.location = location;
            this.platformLocation = platformLocation;
            this.urlCodec = urlCodec;
            this.locationStrategy = locationStrategy;
            this.initalizing = true;
            this.updateBrowser = false;
            this.$$absUrl = '';
            this.$$url = '';
            this.$$host = '';
            this.$$replace = false;
            this.$$path = '';
            this.$$search = '';
            this.$$hash = '';
            this.$$changeListeners = [];
            this.cachedState = null;
            this.urlChanges = new rxjs.ReplaySubject(1);
            this.lastBrowserUrl = '';
            // This variable should be used *only* inside the cacheState function.
            this.lastCachedState = null;
            var initialUrl = this.browserUrl();
            var parsedUrl = this.urlCodec.parse(initialUrl);
            if (typeof parsedUrl === 'string') {
                throw 'Invalid URL';
            }
            this.$$protocol = parsedUrl.protocol;
            this.$$host = parsedUrl.hostname;
            this.$$port = parseInt(parsedUrl.port) || DEFAULT_PORTS[parsedUrl.protocol] || null;
            this.$$parseLinkUrl(initialUrl, initialUrl);
            this.cacheState();
            this.$$state = this.browserState();
            this.location.onUrlChange(function (newUrl, newState) {
                _this.urlChanges.next({ newUrl: newUrl, newState: newState });
            });
            if (isPromise($injector)) {
                $injector.then(function ($i) { return _this.initialize($i); });
            }
            else {
                this.initialize($injector);
            }
        }
        $locationShim.prototype.initialize = function ($injector) {
            var _this = this;
            var $rootScope = $injector.get('$rootScope');
            var $rootElement = $injector.get('$rootElement');
            $rootElement.on('click', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.which === 2 ||
                    event.button === 2) {
                    return;
                }
                var elm = event.target;
                // traverse the DOM up to find first A tag
                while (elm && elm.nodeName.toLowerCase() !== 'a') {
                    // ignore rewriting if no A tag (reached root element, or no parent - removed from document)
                    if (elm === $rootElement[0] || !(elm = elm.parentNode)) {
                        return;
                    }
                }
                if (!isAnchor(elm)) {
                    return;
                }
                var absHref = elm.href;
                var relHref = elm.getAttribute('href');
                // Ignore when url is started with javascript: or mailto:
                if (IGNORE_URI_REGEXP.test(absHref)) {
                    return;
                }
                if (absHref && !elm.getAttribute('target') && !event.isDefaultPrevented()) {
                    if (_this.$$parseLinkUrl(absHref, relHref)) {
                        // We do a preventDefault for all urls that are part of the AngularJS application,
                        // in html5mode and also without, so that we are able to abort navigation without
                        // getting double entries in the location history.
                        event.preventDefault();
                        // update location manually
                        if (_this.absUrl() !== _this.browserUrl()) {
                            $rootScope.$apply();
                        }
                    }
                }
            });
            this.urlChanges.subscribe(function (_a) {
                var newUrl = _a.newUrl, newState = _a.newState;
                var oldUrl = _this.absUrl();
                var oldState = _this.$$state;
                _this.$$parse(newUrl);
                newUrl = _this.absUrl();
                _this.$$state = newState;
                var defaultPrevented = $rootScope.$broadcast('$locationChangeStart', newUrl, oldUrl, newState, oldState)
                    .defaultPrevented;
                // if the location was changed by a `$locationChangeStart` handler then stop
                // processing this location change
                if (_this.absUrl() !== newUrl)
                    return;
                // If default was prevented, set back to old state. This is the state that was locally
                // cached in the $location service.
                if (defaultPrevented) {
                    _this.$$parse(oldUrl);
                    _this.state(oldState);
                    _this.setBrowserUrlWithFallback(oldUrl, false, oldState);
                    _this.$$notifyChangeListeners(_this.url(), _this.$$state, oldUrl, oldState);
                }
                else {
                    _this.initalizing = false;
                    $rootScope.$broadcast('$locationChangeSuccess', newUrl, oldUrl, newState, oldState);
                    _this.resetBrowserUpdate();
                }
                if (!$rootScope.$$phase) {
                    $rootScope.$digest();
                }
            });
            // update browser
            $rootScope.$watch(function () {
                if (_this.initalizing || _this.updateBrowser) {
                    _this.updateBrowser = false;
                    var oldUrl_1 = _this.browserUrl();
                    var newUrl = _this.absUrl();
                    var oldState_1 = _this.browserState();
                    var currentReplace_1 = _this.$$replace;
                    var urlOrStateChanged_1 = !_this.urlCodec.areEqual(oldUrl_1, newUrl) || oldState_1 !== _this.$$state;
                    // Fire location changes one time to on initialization. This must be done on the
                    // next tick (thus inside $evalAsync()) in order for listeners to be registered
                    // before the event fires. Mimicing behavior from $locationWatch:
                    // https://github.com/angular/angular.js/blob/master/src/ng/location.js#L983
                    if (_this.initalizing || urlOrStateChanged_1) {
                        _this.initalizing = false;
                        $rootScope.$evalAsync(function () {
                            // Get the new URL again since it could have changed due to async update
                            var newUrl = _this.absUrl();
                            var defaultPrevented = $rootScope
                                .$broadcast('$locationChangeStart', newUrl, oldUrl_1, _this.$$state, oldState_1)
                                .defaultPrevented;
                            // if the location was changed by a `$locationChangeStart` handler then stop
                            // processing this location change
                            if (_this.absUrl() !== newUrl)
                                return;
                            if (defaultPrevented) {
                                _this.$$parse(oldUrl_1);
                                _this.$$state = oldState_1;
                            }
                            else {
                                // This block doesn't run when initalizing because it's going to perform the update to
                                // the URL which shouldn't be needed when initalizing.
                                if (urlOrStateChanged_1) {
                                    _this.setBrowserUrlWithFallback(newUrl, currentReplace_1, oldState_1 === _this.$$state ? null : _this.$$state);
                                    _this.$$replace = false;
                                }
                                $rootScope.$broadcast('$locationChangeSuccess', newUrl, oldUrl_1, _this.$$state, oldState_1);
                                if (urlOrStateChanged_1) {
                                    _this.$$notifyChangeListeners(_this.url(), _this.$$state, oldUrl_1, oldState_1);
                                }
                            }
                        });
                    }
                }
                _this.$$replace = false;
            });
        };
        $locationShim.prototype.resetBrowserUpdate = function () {
            this.$$replace = false;
            this.$$state = this.browserState();
            this.updateBrowser = false;
            this.lastBrowserUrl = this.browserUrl();
        };
        $locationShim.prototype.browserUrl = function (url, replace, state) {
            // In modern browsers `history.state` is `null` by default; treating it separately
            // from `undefined` would cause `$browser.url('/foo')` to change `history.state`
            // to undefined via `pushState`. Instead, let's change `undefined` to `null` here.
            if (typeof state === 'undefined') {
                state = null;
            }
            // setter
            if (url) {
                var sameState = this.lastHistoryState === state;
                // Normalize the inputted URL
                url = this.urlCodec.parse(url).href;
                // Don't change anything if previous and current URLs and states match.
                if (this.lastBrowserUrl === url && sameState) {
                    return this;
                }
                this.lastBrowserUrl = url;
                this.lastHistoryState = state;
                // Remove server base from URL as the Angular APIs for updating URL require
                // it to be the path+.
                url = this.stripBaseUrl(this.getServerBase(), url) || url;
                // Set the URL
                if (replace) {
                    this.locationStrategy.replaceState(state, '', url, '');
                }
                else {
                    this.locationStrategy.pushState(state, '', url, '');
                }
                this.cacheState();
                return this;
                // getter
            }
            else {
                return this.platformLocation.href;
            }
        };
        $locationShim.prototype.cacheState = function () {
            // This should be the only place in $browser where `history.state` is read.
            this.cachedState = this.platformLocation.getState();
            if (typeof this.cachedState === 'undefined') {
                this.cachedState = null;
            }
            // Prevent callbacks fo fire twice if both hashchange & popstate were fired.
            if (deepEqual(this.cachedState, this.lastCachedState)) {
                this.cachedState = this.lastCachedState;
            }
            this.lastCachedState = this.cachedState;
            this.lastHistoryState = this.cachedState;
        };
        /**
         * This function emulates the $browser.state() function from AngularJS. It will cause
         * history.state to be cached unless changed with deep equality check.
         */
        $locationShim.prototype.browserState = function () {
            return this.cachedState;
        };
        $locationShim.prototype.stripBaseUrl = function (base, url) {
            if (url.startsWith(base)) {
                return url.substr(base.length);
            }
            return undefined;
        };
        $locationShim.prototype.getServerBase = function () {
            var _a = this.platformLocation, protocol = _a.protocol, hostname = _a.hostname, port = _a.port;
            var baseHref = this.locationStrategy.getBaseHref();
            var url = protocol + "//" + hostname + (port ? ':' + port : '') + (baseHref || '/');
            return url.endsWith('/') ? url : url + '/';
        };
        $locationShim.prototype.parseAppUrl = function (url) {
            if (DOUBLE_SLASH_REGEX.test(url)) {
                throw new Error("Bad Path - URL cannot start with double slashes: " + url);
            }
            var prefixed = (url.charAt(0) !== '/');
            if (prefixed) {
                url = '/' + url;
            }
            var match = this.urlCodec.parse(url, this.getServerBase());
            if (typeof match === 'string') {
                throw new Error("Bad URL - Cannot parse URL: " + url);
            }
            var path = prefixed && match.pathname.charAt(0) === '/' ? match.pathname.substring(1) : match.pathname;
            this.$$path = this.urlCodec.decodePath(path);
            this.$$search = this.urlCodec.decodeSearch(match.search);
            this.$$hash = this.urlCodec.decodeHash(match.hash);
            // make sure path starts with '/';
            if (this.$$path && this.$$path.charAt(0) !== '/') {
                this.$$path = '/' + this.$$path;
            }
        };
        /**
         * Registers listeners for URL changes. This API is used to catch updates performed by the
         * AngularJS framework. These changes are a subset of the `$locationChangeStart` and
         * `$locationChangeSuccess` events which fire when AngularJS updates its internally-referenced
         * version of the browser URL.
         *
         * It's possible for `$locationChange` events to happen, but for the browser URL
         * (window.location) to remain unchanged. This `onChange` callback will fire only when AngularJS
         * actually updates the browser URL (window.location).
         *
         * @param fn The callback function that is triggered for the listener when the URL changes.
         * @param err The callback function that is triggered when an error occurs.
         */
        $locationShim.prototype.onChange = function (fn, err) {
            if (err === void 0) { err = function (e) { }; }
            this.$$changeListeners.push([fn, err]);
        };
        /** @internal */
        $locationShim.prototype.$$notifyChangeListeners = function (url, state, oldUrl, oldState) {
            if (url === void 0) { url = ''; }
            if (oldUrl === void 0) { oldUrl = ''; }
            this.$$changeListeners.forEach(function (_a) {
                var _b = __read(_a, 2), fn = _b[0], err = _b[1];
                try {
                    fn(url, state, oldUrl, oldState);
                }
                catch (e) {
                    err(e);
                }
            });
        };
        /**
         * Parses the provided URL, and sets the current URL to the parsed result.
         *
         * @param url The URL string.
         */
        $locationShim.prototype.$$parse = function (url) {
            var pathUrl;
            if (url.startsWith('/')) {
                pathUrl = url;
            }
            else {
                // Remove protocol & hostname if URL starts with it
                pathUrl = this.stripBaseUrl(this.getServerBase(), url);
            }
            if (typeof pathUrl === 'undefined') {
                throw new Error("Invalid url \"" + url + "\", missing path prefix \"" + this.getServerBase() + "\".");
            }
            this.parseAppUrl(pathUrl);
            if (!this.$$path) {
                this.$$path = '/';
            }
            this.composeUrls();
        };
        /**
         * Parses the provided URL and its relative URL.
         *
         * @param url The full URL string.
         * @param relHref A URL string relative to the full URL string.
         */
        $locationShim.prototype.$$parseLinkUrl = function (url, relHref) {
            // When relHref is passed, it should be a hash and is handled separately
            if (relHref && relHref[0] === '#') {
                this.hash(relHref.slice(1));
                return true;
            }
            var rewrittenUrl;
            var appUrl = this.stripBaseUrl(this.getServerBase(), url);
            if (typeof appUrl !== 'undefined') {
                rewrittenUrl = this.getServerBase() + appUrl;
            }
            else if (this.getServerBase() === url + '/') {
                rewrittenUrl = this.getServerBase();
            }
            // Set the URL
            if (rewrittenUrl) {
                this.$$parse(rewrittenUrl);
            }
            return !!rewrittenUrl;
        };
        $locationShim.prototype.setBrowserUrlWithFallback = function (url, replace, state) {
            var oldUrl = this.url();
            var oldState = this.$$state;
            try {
                this.browserUrl(url, replace, state);
                // Make sure $location.state() returns referentially identical (not just deeply equal)
                // state object; this makes possible quick checking if the state changed in the digest
                // loop. Checking deep equality would be too expensive.
                this.$$state = this.browserState();
            }
            catch (e) {
                // Restore old values if pushState fails
                this.url(oldUrl);
                this.$$state = oldState;
                throw e;
            }
        };
        $locationShim.prototype.composeUrls = function () {
            this.$$url = this.urlCodec.normalize(this.$$path, this.$$search, this.$$hash);
            this.$$absUrl = this.getServerBase() + this.$$url.substr(1); // remove '/' from front of URL
            this.updateBrowser = true;
        };
        /**
         * Retrieves the full URL representation with all segments encoded according to
         * rules specified in
         * [RFC 3986](https://tools.ietf.org/html/rfc3986).
         *
         *
         * ```js
         * // given URL http://example.com/#/some/path?foo=bar&baz=xoxo
         * let absUrl = $location.absUrl();
         * // => "http://example.com/#/some/path?foo=bar&baz=xoxo"
         * ```
         */
        $locationShim.prototype.absUrl = function () {
            return this.$$absUrl;
        };
        $locationShim.prototype.url = function (url) {
            if (typeof url === 'string') {
                if (!url.length) {
                    url = '/';
                }
                var match = PATH_MATCH.exec(url);
                if (!match)
                    return this;
                if (match[1] || url === '')
                    this.path(this.urlCodec.decodePath(match[1]));
                if (match[2] || match[1] || url === '')
                    this.search(match[3] || '');
                this.hash(match[5] || '');
                // Chainable method
                return this;
            }
            return this.$$url;
        };
        /**
         * Retrieves the protocol of the current URL.
         *
         * ```js
         * // given URL http://example.com/#/some/path?foo=bar&baz=xoxo
         * let protocol = $location.protocol();
         * // => "http"
         * ```
         */
        $locationShim.prototype.protocol = function () {
            return this.$$protocol;
        };
        /**
         * Retrieves the protocol of the current URL.
         *
         * In contrast to the non-AngularJS version `location.host` which returns `hostname:port`, this
         * returns the `hostname` portion only.
         *
         *
         * ```js
         * // given URL http://example.com/#/some/path?foo=bar&baz=xoxo
         * let host = $location.host();
         * // => "example.com"
         *
         * // given URL http://user:password@example.com:8080/#/some/path?foo=bar&baz=xoxo
         * host = $location.host();
         * // => "example.com"
         * host = location.host;
         * // => "example.com:8080"
         * ```
         */
        $locationShim.prototype.host = function () {
            return this.$$host;
        };
        /**
         * Retrieves the port of the current URL.
         *
         * ```js
         * // given URL http://example.com/#/some/path?foo=bar&baz=xoxo
         * let port = $location.port();
         * // => 80
         * ```
         */
        $locationShim.prototype.port = function () {
            return this.$$port;
        };
        $locationShim.prototype.path = function (path) {
            if (typeof path === 'undefined') {
                return this.$$path;
            }
            // null path converts to empty string. Prepend with "/" if needed.
            path = path !== null ? path.toString() : '';
            path = path.charAt(0) === '/' ? path : '/' + path;
            this.$$path = path;
            this.composeUrls();
            return this;
        };
        $locationShim.prototype.search = function (search, paramValue) {
            switch (arguments.length) {
                case 0:
                    return this.$$search;
                case 1:
                    if (typeof search === 'string' || typeof search === 'number') {
                        this.$$search = this.urlCodec.decodeSearch(search.toString());
                    }
                    else if (typeof search === 'object' && search !== null) {
                        // Copy the object so it's never mutated
                        search = Object.assign({}, search);
                        // remove object undefined or null properties
                        for (var key in search) {
                            if (search[key] == null)
                                delete search[key];
                        }
                        this.$$search = search;
                    }
                    else {
                        throw new Error('LocationProvider.search(): First argument must be a string or an object.');
                    }
                    break;
                default:
                    if (typeof search === 'string') {
                        var currentSearch = this.search();
                        if (typeof paramValue === 'undefined' || paramValue === null) {
                            delete currentSearch[search];
                            return this.search(currentSearch);
                        }
                        else {
                            currentSearch[search] = paramValue;
                            return this.search(currentSearch);
                        }
                    }
            }
            this.composeUrls();
            return this;
        };
        $locationShim.prototype.hash = function (hash) {
            if (typeof hash === 'undefined') {
                return this.$$hash;
            }
            this.$$hash = hash !== null ? hash.toString() : '';
            this.composeUrls();
            return this;
        };
        /**
         * Changes to `$location` during the current `$digest` will replace the current
         * history record, instead of adding a new one.
         */
        $locationShim.prototype.replace = function () {
            this.$$replace = true;
            return this;
        };
        $locationShim.prototype.state = function (state) {
            if (typeof state === 'undefined') {
                return this.$$state;
            }
            this.$$state = state;
            return this;
        };
        return $locationShim;
    }());
    /**
     * The factory function used to create an instance of the `$locationShim` in Angular,
     * and provides an API-compatiable `$locationProvider` for AngularJS.
     *
     * @publicApi
     */
    var $locationShimProvider = /** @class */ (function () {
        function $locationShimProvider(ngUpgrade, location, platformLocation, urlCodec, locationStrategy) {
            this.ngUpgrade = ngUpgrade;
            this.location = location;
            this.platformLocation = platformLocation;
            this.urlCodec = urlCodec;
            this.locationStrategy = locationStrategy;
        }
        /**
         * Factory method that returns an instance of the $locationShim
         */
        $locationShimProvider.prototype.$get = function () {
            return new $locationShim(this.ngUpgrade.$injector, this.location, this.platformLocation, this.urlCodec, this.locationStrategy);
        };
        /**
         * Stub method used to keep API compatible with AngularJS. This setting is configured through
         * the LocationUpgradeModule's `config` method in your Angular app.
         */
        $locationShimProvider.prototype.hashPrefix = function (prefix) {
            throw new Error('Configure LocationUpgrade through LocationUpgradeModule.config method.');
        };
        /**
         * Stub method used to keep API compatible with AngularJS. This setting is configured through
         * the LocationUpgradeModule's `config` method in your Angular app.
         */
        $locationShimProvider.prototype.html5Mode = function (mode) {
            throw new Error('Configure LocationUpgrade through LocationUpgradeModule.config method.');
        };
        return $locationShimProvider;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * A codec for encoding and decoding URL parts.
     *
     * @publicApi
     **/
    var UrlCodec = /** @class */ (function () {
        function UrlCodec() {
        }
        return UrlCodec;
    }());
    /**
     * A `UrlCodec` that uses logic from AngularJS to serialize and parse URLs
     * and URL parameters.
     *
     * @publicApi
     */
    var AngularJSUrlCodec = /** @class */ (function () {
        function AngularJSUrlCodec() {
        }
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L15
        AngularJSUrlCodec.prototype.encodePath = function (path) {
            var segments = path.split('/');
            var i = segments.length;
            while (i--) {
                // decode forward slashes to prevent them from being double encoded
                segments[i] = encodeUriSegment(segments[i].replace(/%2F/g, '/'));
            }
            path = segments.join('/');
            return _stripIndexHtml((path && path[0] !== '/' && '/' || '') + path);
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L42
        AngularJSUrlCodec.prototype.encodeSearch = function (search) {
            if (typeof search === 'string') {
                search = parseKeyValue(search);
            }
            search = toKeyValue(search);
            return search ? '?' + search : '';
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L44
        AngularJSUrlCodec.prototype.encodeHash = function (hash) {
            hash = encodeUriSegment(hash);
            return hash ? '#' + hash : '';
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L27
        AngularJSUrlCodec.prototype.decodePath = function (path, html5Mode) {
            if (html5Mode === void 0) { html5Mode = true; }
            var segments = path.split('/');
            var i = segments.length;
            while (i--) {
                segments[i] = decodeURIComponent(segments[i]);
                if (html5Mode) {
                    // encode forward slashes to prevent them from being mistaken for path separators
                    segments[i] = segments[i].replace(/\//g, '%2F');
                }
            }
            return segments.join('/');
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L72
        AngularJSUrlCodec.prototype.decodeSearch = function (search) {
            return parseKeyValue(search);
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/location.js#L73
        AngularJSUrlCodec.prototype.decodeHash = function (hash) {
            hash = decodeURIComponent(hash);
            return hash[0] === '#' ? hash.substring(1) : hash;
        };
        AngularJSUrlCodec.prototype.normalize = function (pathOrHref, search, hash, baseUrl) {
            if (arguments.length === 1) {
                var parsed = this.parse(pathOrHref, baseUrl);
                if (typeof parsed === 'string') {
                    return parsed;
                }
                var serverUrl = parsed.protocol + "://" + parsed.hostname + (parsed.port ? ':' + parsed.port : '');
                return this.normalize(this.decodePath(parsed.pathname), this.decodeSearch(parsed.search), this.decodeHash(parsed.hash), serverUrl);
            }
            else {
                var encPath = this.encodePath(pathOrHref);
                var encSearch = search && this.encodeSearch(search) || '';
                var encHash = hash && this.encodeHash(hash) || '';
                var joinedPath = (baseUrl || '') + encPath;
                if (!joinedPath.length || joinedPath[0] !== '/') {
                    joinedPath = '/' + joinedPath;
                }
                return joinedPath + encSearch + encHash;
            }
        };
        AngularJSUrlCodec.prototype.areEqual = function (valA, valB) {
            return this.normalize(valA) === this.normalize(valB);
        };
        // https://github.com/angular/angular.js/blob/864c7f0/src/ng/urlUtils.js#L60
        AngularJSUrlCodec.prototype.parse = function (url, base) {
            try {
                // Safari 12 throws an error when the URL constructor is called with an undefined base.
                var parsed = !base ? new URL(url) : new URL(url, base);
                return {
                    href: parsed.href,
                    protocol: parsed.protocol ? parsed.protocol.replace(/:$/, '') : '',
                    host: parsed.host,
                    search: parsed.search ? parsed.search.replace(/^\?/, '') : '',
                    hash: parsed.hash ? parsed.hash.replace(/^#/, '') : '',
                    hostname: parsed.hostname,
                    port: parsed.port,
                    pathname: (parsed.pathname.charAt(0) === '/') ? parsed.pathname : '/' + parsed.pathname
                };
            }
            catch (e) {
                throw new Error("Invalid URL (" + url + ") with base (" + base + ")");
            }
        };
        return AngularJSUrlCodec;
    }());
    function _stripIndexHtml(url) {
        return url.replace(/\/index.html$/, '');
    }
    /**
     * Tries to decode the URI component without throwing an exception.
     *
     * @param str value potential URI component to check.
     * @returns the decoded URI if it can be decoded or else `undefined`.
     */
    function tryDecodeURIComponent(value) {
        try {
            return decodeURIComponent(value);
        }
        catch (e) {
            // Ignore any invalid uri component.
            return undefined;
        }
    }
    /**
     * Parses an escaped url query string into key-value pairs. Logic taken from
     * https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1382
     */
    function parseKeyValue(keyValue) {
        var obj = {};
        (keyValue || '').split('&').forEach(function (keyValue) {
            var splitPoint, key, val;
            if (keyValue) {
                key = keyValue = keyValue.replace(/\+/g, '%20');
                splitPoint = keyValue.indexOf('=');
                if (splitPoint !== -1) {
                    key = keyValue.substring(0, splitPoint);
                    val = keyValue.substring(splitPoint + 1);
                }
                key = tryDecodeURIComponent(key);
                if (typeof key !== 'undefined') {
                    val = typeof val !== 'undefined' ? tryDecodeURIComponent(val) : true;
                    if (!obj.hasOwnProperty(key)) {
                        obj[key] = val;
                    }
                    else if (Array.isArray(obj[key])) {
                        obj[key].push(val);
                    }
                    else {
                        obj[key] = [obj[key], val];
                    }
                }
            }
        });
        return obj;
    }
    /**
     * Serializes into key-value pairs. Logic taken from
     * https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1409
     */
    function toKeyValue(obj) {
        var parts = [];
        var _loop_1 = function (key) {
            var value = obj[key];
            if (Array.isArray(value)) {
                value.forEach(function (arrayValue) {
                    parts.push(encodeUriQuery(key, true) +
                        (arrayValue === true ? '' : '=' + encodeUriQuery(arrayValue, true)));
                });
            }
            else {
                parts.push(encodeUriQuery(key, true) +
                    (value === true ? '' : '=' + encodeUriQuery(value, true)));
            }
        };
        for (var key in obj) {
            _loop_1(key);
        }
        return parts.length ? parts.join('&') : '';
    }
    /**
     * We need our custom method because encodeURIComponent is too aggressive and doesn't follow
     * https://tools.ietf.org/html/rfc3986 with regards to the character set (pchar) allowed in path
     * segments:
     *    segment       = *pchar
     *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     *    pct-encoded   = "%" HEXDIG HEXDIG
     *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *                     / "*" / "+" / "," / ";" / "="
     *
     * Logic from https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1437
     */
    function encodeUriSegment(val) {
        return encodeUriQuery(val, true).replace(/%26/g, '&').replace(/%3D/gi, '=').replace(/%2B/gi, '+');
    }
    /**
     * This method is intended for encoding *key* or *value* parts of query component. We need a custom
     * method because encodeURIComponent is too aggressive and encodes stuff that doesn't have to be
     * encoded per https://tools.ietf.org/html/rfc3986:
     *    query         = *( pchar / "/" / "?" )
     *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
     *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
     *    pct-encoded   = "%" HEXDIG HEXDIG
     *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
     *                     / "*" / "+" / "," / ";" / "="
     *
     * Logic from https://github.com/angular/angular.js/blob/864c7f0/src/Angular.js#L1456
     */
    function encodeUriQuery(val, pctEncodeSpaces) {
        if (pctEncodeSpaces === void 0) { pctEncodeSpaces = false; }
        return encodeURIComponent(val)
            .replace(/%40/g, '@')
            .replace(/%3A/gi, ':')
            .replace(/%24/g, '$')
            .replace(/%2C/gi, ',')
            .replace(/%3B/gi, ';')
            .replace(/%20/g, (pctEncodeSpaces ? '%20' : '+'));
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * A provider token used to configure the location upgrade module.
     *
     * @publicApi
     */
    var LOCATION_UPGRADE_CONFIGURATION = new core.InjectionToken('LOCATION_UPGRADE_CONFIGURATION');
    var APP_BASE_HREF_RESOLVED = new core.InjectionToken('APP_BASE_HREF_RESOLVED');
    /**
     * `NgModule` used for providing and configuring Angular's Unified Location Service for upgrading.
     *
     * @see [Using the Unified Angular Location Service](guide/upgrade#using-the-unified-angular-location-service)
     *
     * @publicApi
     */
    var LocationUpgradeModule = /** @class */ (function () {
        function LocationUpgradeModule() {
        }
        LocationUpgradeModule.config = function (config) {
            return {
                ngModule: LocationUpgradeModule,
                providers: [
                    common.Location,
                    {
                        provide: $locationShim,
                        useFactory: provide$location,
                        deps: [_static.UpgradeModule, common.Location, common.PlatformLocation, UrlCodec, common.LocationStrategy]
                    },
                    { provide: LOCATION_UPGRADE_CONFIGURATION, useValue: config ? config : {} },
                    { provide: UrlCodec, useFactory: provideUrlCodec, deps: [LOCATION_UPGRADE_CONFIGURATION] },
                    {
                        provide: APP_BASE_HREF_RESOLVED,
                        useFactory: provideAppBaseHref,
                        deps: [LOCATION_UPGRADE_CONFIGURATION, [new core.Inject(common.APP_BASE_HREF), new core.Optional()]]
                    },
                    {
                        provide: common.LocationStrategy,
                        useFactory: provideLocationStrategy,
                        deps: [
                            common.PlatformLocation,
                            APP_BASE_HREF_RESOLVED,
                            LOCATION_UPGRADE_CONFIGURATION,
                        ]
                    },
                ],
            };
        };
        return LocationUpgradeModule;
    }());
    LocationUpgradeModule.decorators = [
        { type: core.NgModule, args: [{ imports: [common.CommonModule] },] }
    ];
    function provideAppBaseHref(config, appBaseHref) {
        if (config && config.appBaseHref != null) {
            return config.appBaseHref;
        }
        else if (appBaseHref != null) {
            return appBaseHref;
        }
        return '';
    }
    function provideUrlCodec(config) {
        var codec = config && config.urlCodec || AngularJSUrlCodec;
        return new codec();
    }
    function provideLocationStrategy(platformLocation, baseHref, options) {
        if (options === void 0) { options = {}; }
        return options.useHash ? new common.HashLocationStrategy(platformLocation, baseHref) :
            new common.PathLocationStrategy(platformLocation, baseHref);
    }
    function provide$location(ngUpgrade, location, platformLocation, urlCodec, locationStrategy) {
        var $locationProvider = new $locationShimProvider(ngUpgrade, location, platformLocation, urlCodec, locationStrategy);
        return $locationProvider.$get();
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    // This file only reexports content of the `src` folder. Keep it that way.

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.$locationShim = $locationShim;
    exports.$locationShimProvider = $locationShimProvider;
    exports.AngularJSUrlCodec = AngularJSUrlCodec;
    exports.LOCATION_UPGRADE_CONFIGURATION = LOCATION_UPGRADE_CONFIGURATION;
    exports.LocationUpgradeModule = LocationUpgradeModule;
    exports.UrlCodec = UrlCodec;
    exports.ɵangular_packages_common_upgrade_upgrade_a = provideAppBaseHref;
    exports.ɵangular_packages_common_upgrade_upgrade_b = provideUrlCodec;
    exports.ɵangular_packages_common_upgrade_upgrade_c = provideLocationStrategy;
    exports.ɵangular_packages_common_upgrade_upgrade_d = provide$location;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=common-upgrade.umd.js.map
