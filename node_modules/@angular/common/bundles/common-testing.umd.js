/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/common'), require('rxjs')) :
    typeof define === 'function' && define.amd ? define('@angular/common/testing', ['exports', '@angular/core', '@angular/common', 'rxjs'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.common = global.ng.common || {}, global.ng.common.testing = {}), global.ng.core, global.ng.common, global.rxjs));
}(this, (function (exports, core, common, rxjs) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * A spy for {@link Location} that allows tests to fire simulated location events.
     *
     * @publicApi
     */
    var SpyLocation = /** @class */ (function () {
        function SpyLocation() {
            this.urlChanges = [];
            this._history = [new LocationState('', '', null)];
            this._historyIndex = 0;
            /** @internal */
            this._subject = new core.EventEmitter();
            /** @internal */
            this._baseHref = '';
            /** @internal */
            this._platformStrategy = null;
            /** @internal */
            this._platformLocation = null;
            /** @internal */
            this._urlChangeListeners = [];
        }
        SpyLocation.prototype.setInitialPath = function (url) {
            this._history[this._historyIndex].path = url;
        };
        SpyLocation.prototype.setBaseHref = function (url) {
            this._baseHref = url;
        };
        SpyLocation.prototype.path = function () {
            return this._history[this._historyIndex].path;
        };
        SpyLocation.prototype.getState = function () {
            return this._history[this._historyIndex].state;
        };
        SpyLocation.prototype.isCurrentPathEqualTo = function (path, query) {
            if (query === void 0) { query = ''; }
            var givenPath = path.endsWith('/') ? path.substring(0, path.length - 1) : path;
            var currPath = this.path().endsWith('/') ? this.path().substring(0, this.path().length - 1) : this.path();
            return currPath == givenPath + (query.length > 0 ? ('?' + query) : '');
        };
        SpyLocation.prototype.simulateUrlPop = function (pathname) {
            this._subject.emit({ 'url': pathname, 'pop': true, 'type': 'popstate' });
        };
        SpyLocation.prototype.simulateHashChange = function (pathname) {
            // Because we don't prevent the native event, the browser will independently update the path
            this.setInitialPath(pathname);
            this.urlChanges.push('hash: ' + pathname);
            this._subject.emit({ 'url': pathname, 'pop': true, 'type': 'hashchange' });
        };
        SpyLocation.prototype.prepareExternalUrl = function (url) {
            if (url.length > 0 && !url.startsWith('/')) {
                url = '/' + url;
            }
            return this._baseHref + url;
        };
        SpyLocation.prototype.go = function (path, query, state) {
            if (query === void 0) { query = ''; }
            if (state === void 0) { state = null; }
            path = this.prepareExternalUrl(path);
            if (this._historyIndex > 0) {
                this._history.splice(this._historyIndex + 1);
            }
            this._history.push(new LocationState(path, query, state));
            this._historyIndex = this._history.length - 1;
            var locationState = this._history[this._historyIndex - 1];
            if (locationState.path == path && locationState.query == query) {
                return;
            }
            var url = path + (query.length > 0 ? ('?' + query) : '');
            this.urlChanges.push(url);
            this._subject.emit({ 'url': url, 'pop': false });
        };
        SpyLocation.prototype.replaceState = function (path, query, state) {
            if (query === void 0) { query = ''; }
            if (state === void 0) { state = null; }
            path = this.prepareExternalUrl(path);
            var history = this._history[this._historyIndex];
            if (history.path == path && history.query == query) {
                return;
            }
            history.path = path;
            history.query = query;
            history.state = state;
            var url = path + (query.length > 0 ? ('?' + query) : '');
            this.urlChanges.push('replace: ' + url);
        };
        SpyLocation.prototype.forward = function () {
            if (this._historyIndex < (this._history.length - 1)) {
                this._historyIndex++;
                this._subject.emit({ 'url': this.path(), 'state': this.getState(), 'pop': true });
            }
        };
        SpyLocation.prototype.back = function () {
            if (this._historyIndex > 0) {
                this._historyIndex--;
                this._subject.emit({ 'url': this.path(), 'state': this.getState(), 'pop': true });
            }
        };
        SpyLocation.prototype.onUrlChange = function (fn) {
            var _this = this;
            this._urlChangeListeners.push(fn);
            if (!this._urlChangeSubscription) {
                this._urlChangeSubscription = this.subscribe(function (v) {
                    _this._notifyUrlChangeListeners(v.url, v.state);
                });
            }
        };
        /** @internal */
        SpyLocation.prototype._notifyUrlChangeListeners = function (url, state) {
            if (url === void 0) { url = ''; }
            this._urlChangeListeners.forEach(function (fn) { return fn(url, state); });
        };
        SpyLocation.prototype.subscribe = function (onNext, onThrow, onReturn) {
            return this._subject.subscribe({ next: onNext, error: onThrow, complete: onReturn });
        };
        SpyLocation.prototype.normalize = function (url) {
            return null;
        };
        return SpyLocation;
    }());
    SpyLocation.decorators = [
        { type: core.Injectable }
    ];
    var LocationState = /** @class */ (function () {
        function LocationState(path, query, state) {
            this.path = path;
            this.query = query;
            this.state = state;
        }
        return LocationState;
    }());

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
     * A mock implementation of {@link LocationStrategy} that allows tests to fire simulated
     * location events.
     *
     * @publicApi
     */
    var MockLocationStrategy = /** @class */ (function (_super) {
        __extends(MockLocationStrategy, _super);
        function MockLocationStrategy() {
            var _this = _super.call(this) || this;
            _this.internalBaseHref = '/';
            _this.internalPath = '/';
            _this.internalTitle = '';
            _this.urlChanges = [];
            /** @internal */
            _this._subject = new core.EventEmitter();
            _this.stateChanges = [];
            return _this;
        }
        MockLocationStrategy.prototype.simulatePopState = function (url) {
            this.internalPath = url;
            this._subject.emit(new _MockPopStateEvent(this.path()));
        };
        MockLocationStrategy.prototype.path = function (includeHash) {
            if (includeHash === void 0) { includeHash = false; }
            return this.internalPath;
        };
        MockLocationStrategy.prototype.prepareExternalUrl = function (internal) {
            if (internal.startsWith('/') && this.internalBaseHref.endsWith('/')) {
                return this.internalBaseHref + internal.substring(1);
            }
            return this.internalBaseHref + internal;
        };
        MockLocationStrategy.prototype.pushState = function (ctx, title, path, query) {
            // Add state change to changes array
            this.stateChanges.push(ctx);
            this.internalTitle = title;
            var url = path + (query.length > 0 ? ('?' + query) : '');
            this.internalPath = url;
            var externalUrl = this.prepareExternalUrl(url);
            this.urlChanges.push(externalUrl);
        };
        MockLocationStrategy.prototype.replaceState = function (ctx, title, path, query) {
            // Reset the last index of stateChanges to the ctx (state) object
            this.stateChanges[(this.stateChanges.length || 1) - 1] = ctx;
            this.internalTitle = title;
            var url = path + (query.length > 0 ? ('?' + query) : '');
            this.internalPath = url;
            var externalUrl = this.prepareExternalUrl(url);
            this.urlChanges.push('replace: ' + externalUrl);
        };
        MockLocationStrategy.prototype.onPopState = function (fn) {
            this._subject.subscribe({ next: fn });
        };
        MockLocationStrategy.prototype.getBaseHref = function () {
            return this.internalBaseHref;
        };
        MockLocationStrategy.prototype.back = function () {
            if (this.urlChanges.length > 0) {
                this.urlChanges.pop();
                this.stateChanges.pop();
                var nextUrl = this.urlChanges.length > 0 ? this.urlChanges[this.urlChanges.length - 1] : '';
                this.simulatePopState(nextUrl);
            }
        };
        MockLocationStrategy.prototype.forward = function () {
            throw 'not implemented';
        };
        MockLocationStrategy.prototype.getState = function () {
            return this.stateChanges[(this.stateChanges.length || 1) - 1];
        };
        return MockLocationStrategy;
    }(common.LocationStrategy));
    MockLocationStrategy.decorators = [
        { type: core.Injectable }
    ];
    MockLocationStrategy.ctorParameters = function () { return []; };
    var _MockPopStateEvent = /** @class */ (function () {
        function _MockPopStateEvent(newUrl) {
            this.newUrl = newUrl;
            this.pop = true;
            this.type = 'popstate';
        }
        return _MockPopStateEvent;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Parser from https://tools.ietf.org/html/rfc3986#appendix-B
     * ^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
     *  12            3  4          5       6  7        8 9
     *
     * Example: http://www.ics.uci.edu/pub/ietf/uri/#Related
     *
     * Results in:
     *
     * $1 = http:
     * $2 = http
     * $3 = //www.ics.uci.edu
     * $4 = www.ics.uci.edu
     * $5 = /pub/ietf/uri/
     * $6 = <undefined>
     * $7 = <undefined>
     * $8 = #Related
     * $9 = Related
     */
    var urlParse = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/;
    function parseUrl(urlStr, baseHref) {
        var verifyProtocol = /^((http[s]?|ftp):\/\/)/;
        var serverBase;
        // URL class requires full URL. If the URL string doesn't start with protocol, we need to add
        // an arbitrary base URL which can be removed afterward.
        if (!verifyProtocol.test(urlStr)) {
            serverBase = 'http://empty.com/';
        }
        var parsedUrl;
        try {
            parsedUrl = new URL(urlStr, serverBase);
        }
        catch (e) {
            var result = urlParse.exec(serverBase || '' + urlStr);
            if (!result) {
                throw new Error("Invalid URL: " + urlStr + " with base: " + baseHref);
            }
            var hostSplit = result[4].split(':');
            parsedUrl = {
                protocol: result[1],
                hostname: hostSplit[0],
                port: hostSplit[1] || '',
                pathname: result[5],
                search: result[6],
                hash: result[8],
            };
        }
        if (parsedUrl.pathname && parsedUrl.pathname.indexOf(baseHref) === 0) {
            parsedUrl.pathname = parsedUrl.pathname.substring(baseHref.length);
        }
        return {
            hostname: !serverBase && parsedUrl.hostname || '',
            protocol: !serverBase && parsedUrl.protocol || '',
            port: !serverBase && parsedUrl.port || '',
            pathname: parsedUrl.pathname || '/',
            search: parsedUrl.search || '',
            hash: parsedUrl.hash || '',
        };
    }
    /**
     * Provider for mock platform location config
     *
     * @publicApi
     */
    var MOCK_PLATFORM_LOCATION_CONFIG = new core.InjectionToken('MOCK_PLATFORM_LOCATION_CONFIG');
    /**
     * Mock implementation of URL state.
     *
     * @publicApi
     */
    var MockPlatformLocation = /** @class */ (function () {
        function MockPlatformLocation(config) {
            this.baseHref = '';
            this.hashUpdate = new rxjs.Subject();
            this.urlChanges = [{ hostname: '', protocol: '', port: '', pathname: '/', search: '', hash: '', state: null }];
            if (config) {
                this.baseHref = config.appBaseHref || '';
                var parsedChanges = this.parseChanges(null, config.startUrl || 'http://<empty>/', this.baseHref);
                this.urlChanges[0] = Object.assign({}, parsedChanges);
            }
        }
        Object.defineProperty(MockPlatformLocation.prototype, "hostname", {
            get: function () {
                return this.urlChanges[0].hostname;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "protocol", {
            get: function () {
                return this.urlChanges[0].protocol;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "port", {
            get: function () {
                return this.urlChanges[0].port;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "pathname", {
            get: function () {
                return this.urlChanges[0].pathname;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "search", {
            get: function () {
                return this.urlChanges[0].search;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "hash", {
            get: function () {
                return this.urlChanges[0].hash;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "state", {
            get: function () {
                return this.urlChanges[0].state;
            },
            enumerable: false,
            configurable: true
        });
        MockPlatformLocation.prototype.getBaseHrefFromDOM = function () {
            return this.baseHref;
        };
        MockPlatformLocation.prototype.onPopState = function (fn) {
            // No-op: a state stack is not implemented, so
            // no events will ever come.
        };
        MockPlatformLocation.prototype.onHashChange = function (fn) {
            this.hashUpdate.subscribe(fn);
        };
        Object.defineProperty(MockPlatformLocation.prototype, "href", {
            get: function () {
                var url = this.protocol + "//" + this.hostname + (this.port ? ':' + this.port : '');
                url += "" + (this.pathname === '/' ? '' : this.pathname) + this.search + this.hash;
                return url;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(MockPlatformLocation.prototype, "url", {
            get: function () {
                return "" + this.pathname + this.search + this.hash;
            },
            enumerable: false,
            configurable: true
        });
        MockPlatformLocation.prototype.parseChanges = function (state, url, baseHref) {
            if (baseHref === void 0) { baseHref = ''; }
            // When the `history.state` value is stored, it is always copied.
            state = JSON.parse(JSON.stringify(state));
            return Object.assign(Object.assign({}, parseUrl(url, baseHref)), { state: state });
        };
        MockPlatformLocation.prototype.replaceState = function (state, title, newUrl) {
            var _a = this.parseChanges(state, newUrl), pathname = _a.pathname, search = _a.search, parsedState = _a.state, hash = _a.hash;
            this.urlChanges[0] = Object.assign(Object.assign({}, this.urlChanges[0]), { pathname: pathname, search: search, hash: hash, state: parsedState });
        };
        MockPlatformLocation.prototype.pushState = function (state, title, newUrl) {
            var _a = this.parseChanges(state, newUrl), pathname = _a.pathname, search = _a.search, parsedState = _a.state, hash = _a.hash;
            this.urlChanges.unshift(Object.assign(Object.assign({}, this.urlChanges[0]), { pathname: pathname, search: search, hash: hash, state: parsedState }));
        };
        MockPlatformLocation.prototype.forward = function () {
            throw new Error('Not implemented');
        };
        MockPlatformLocation.prototype.back = function () {
            var _this = this;
            var oldUrl = this.url;
            var oldHash = this.hash;
            this.urlChanges.shift();
            var newHash = this.hash;
            if (oldHash !== newHash) {
                scheduleMicroTask(function () { return _this.hashUpdate.next({ type: 'hashchange', state: null, oldUrl: oldUrl, newUrl: _this.url }); });
            }
        };
        MockPlatformLocation.prototype.getState = function () {
            return this.state;
        };
        return MockPlatformLocation;
    }());
    MockPlatformLocation.decorators = [
        { type: core.Injectable }
    ];
    MockPlatformLocation.ctorParameters = function () { return [
        { type: undefined, decorators: [{ type: core.Inject, args: [MOCK_PLATFORM_LOCATION_CONFIG,] }, { type: core.Optional }] }
    ]; };
    function scheduleMicroTask(cb) {
        Promise.resolve(null).then(cb);
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

    exports.MOCK_PLATFORM_LOCATION_CONFIG = MOCK_PLATFORM_LOCATION_CONFIG;
    exports.MockLocationStrategy = MockLocationStrategy;
    exports.MockPlatformLocation = MockPlatformLocation;
    exports.SpyLocation = SpyLocation;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=common-testing.umd.js.map
