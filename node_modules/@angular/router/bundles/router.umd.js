/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/common'), require('@angular/core'), require('rxjs'), require('rxjs/operators')) :
    typeof define === 'function' && define.amd ? define('@angular/router', ['exports', '@angular/common', '@angular/core', 'rxjs', 'rxjs/operators'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.router = {}), global.ng.common, global.ng.core, global.rxjs, global.rxjs.operators));
}(this, (function (exports, common, core, rxjs, operators) { 'use strict';

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
    /**
     * Base for events the router goes through, as opposed to events tied to a specific
     * route. Fired one time for any given navigation.
     *
     * The following code shows how a class subscribes to router events.
     *
     * ```ts
     * class MyService {
     *   constructor(public router: Router, logger: Logger) {
     *     router.events.pipe(
     *        filter((e: Event): e is RouterEvent => e instanceof RouterEvent)
     *     ).subscribe((e: RouterEvent) => {
     *       logger.log(e.id, e.url);
     *     });
     *   }
     * }
     * ```
     *
     * @see `Event`
     * @see [Router events summary](guide/router#router-events)
     * @publicApi
     */
    var RouterEvent = /** @class */ (function () {
        function RouterEvent(
        /** A unique ID that the router assigns to every router navigation. */
        id, 
        /** The URL that is the destination for this navigation. */
        url) {
            this.id = id;
            this.url = url;
        }
        return RouterEvent;
    }());
    /**
     * An event triggered when a navigation starts.
     *
     * @publicApi
     */
    var NavigationStart = /** @class */ (function (_super) {
        __extends(NavigationStart, _super);
        function NavigationStart(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        navigationTrigger, 
        /** @docsNotRequired */
        restoredState) {
            if (navigationTrigger === void 0) { navigationTrigger = 'imperative'; }
            if (restoredState === void 0) { restoredState = null; }
            var _this = _super.call(this, id, url) || this;
            _this.navigationTrigger = navigationTrigger;
            _this.restoredState = restoredState;
            return _this;
        }
        /** @docsNotRequired */
        NavigationStart.prototype.toString = function () {
            return "NavigationStart(id: " + this.id + ", url: '" + this.url + "')";
        };
        return NavigationStart;
    }(RouterEvent));
    /**
     * An event triggered when a navigation ends successfully.
     *
     * @see `NavigationStart`
     * @see `NavigationCancel`
     * @see `NavigationError`
     *
     * @publicApi
     */
    var NavigationEnd = /** @class */ (function (_super) {
        __extends(NavigationEnd, _super);
        function NavigationEnd(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            return _this;
        }
        /** @docsNotRequired */
        NavigationEnd.prototype.toString = function () {
            return "NavigationEnd(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "')";
        };
        return NavigationEnd;
    }(RouterEvent));
    /**
     * An event triggered when a navigation is canceled, directly or indirectly.
     * This can happen when a route guard
     * returns `false` or initiates a redirect by returning a `UrlTree`.
     *
     * @see `NavigationStart`
     * @see `NavigationEnd`
     * @see `NavigationError`
     *
     * @publicApi
     */
    var NavigationCancel = /** @class */ (function (_super) {
        __extends(NavigationCancel, _super);
        function NavigationCancel(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        reason) {
            var _this = _super.call(this, id, url) || this;
            _this.reason = reason;
            return _this;
        }
        /** @docsNotRequired */
        NavigationCancel.prototype.toString = function () {
            return "NavigationCancel(id: " + this.id + ", url: '" + this.url + "')";
        };
        return NavigationCancel;
    }(RouterEvent));
    /**
     * An event triggered when a navigation fails due to an unexpected error.
     *
     * @see `NavigationStart`
     * @see `NavigationEnd`
     * @see `NavigationCancel`
     *
     * @publicApi
     */
    var NavigationError = /** @class */ (function (_super) {
        __extends(NavigationError, _super);
        function NavigationError(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        error) {
            var _this = _super.call(this, id, url) || this;
            _this.error = error;
            return _this;
        }
        /** @docsNotRequired */
        NavigationError.prototype.toString = function () {
            return "NavigationError(id: " + this.id + ", url: '" + this.url + "', error: " + this.error + ")";
        };
        return NavigationError;
    }(RouterEvent));
    /**
     * An event triggered when routes are recognized.
     *
     * @publicApi
     */
    var RoutesRecognized = /** @class */ (function (_super) {
        __extends(RoutesRecognized, _super);
        function RoutesRecognized(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects, 
        /** @docsNotRequired */
        state) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            _this.state = state;
            return _this;
        }
        /** @docsNotRequired */
        RoutesRecognized.prototype.toString = function () {
            return "RoutesRecognized(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "', state: " + this.state + ")";
        };
        return RoutesRecognized;
    }(RouterEvent));
    /**
     * An event triggered at the start of the Guard phase of routing.
     *
     * @see `GuardsCheckEnd`
     *
     * @publicApi
     */
    var GuardsCheckStart = /** @class */ (function (_super) {
        __extends(GuardsCheckStart, _super);
        function GuardsCheckStart(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects, 
        /** @docsNotRequired */
        state) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            _this.state = state;
            return _this;
        }
        GuardsCheckStart.prototype.toString = function () {
            return "GuardsCheckStart(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "', state: " + this.state + ")";
        };
        return GuardsCheckStart;
    }(RouterEvent));
    /**
     * An event triggered at the end of the Guard phase of routing.
     *
     * @see `GuardsCheckStart`
     *
     * @publicApi
     */
    var GuardsCheckEnd = /** @class */ (function (_super) {
        __extends(GuardsCheckEnd, _super);
        function GuardsCheckEnd(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects, 
        /** @docsNotRequired */
        state, 
        /** @docsNotRequired */
        shouldActivate) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            _this.state = state;
            _this.shouldActivate = shouldActivate;
            return _this;
        }
        GuardsCheckEnd.prototype.toString = function () {
            return "GuardsCheckEnd(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "', state: " + this.state + ", shouldActivate: " + this.shouldActivate + ")";
        };
        return GuardsCheckEnd;
    }(RouterEvent));
    /**
     * An event triggered at the start of the Resolve phase of routing.
     *
     * Runs in the "resolve" phase whether or not there is anything to resolve.
     * In future, may change to only run when there are things to be resolved.
     *
     * @see `ResolveEnd`
     *
     * @publicApi
     */
    var ResolveStart = /** @class */ (function (_super) {
        __extends(ResolveStart, _super);
        function ResolveStart(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects, 
        /** @docsNotRequired */
        state) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            _this.state = state;
            return _this;
        }
        ResolveStart.prototype.toString = function () {
            return "ResolveStart(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "', state: " + this.state + ")";
        };
        return ResolveStart;
    }(RouterEvent));
    /**
     * An event triggered at the end of the Resolve phase of routing.
     * @see `ResolveStart`.
     *
     * @publicApi
     */
    var ResolveEnd = /** @class */ (function (_super) {
        __extends(ResolveEnd, _super);
        function ResolveEnd(
        /** @docsNotRequired */
        id, 
        /** @docsNotRequired */
        url, 
        /** @docsNotRequired */
        urlAfterRedirects, 
        /** @docsNotRequired */
        state) {
            var _this = _super.call(this, id, url) || this;
            _this.urlAfterRedirects = urlAfterRedirects;
            _this.state = state;
            return _this;
        }
        ResolveEnd.prototype.toString = function () {
            return "ResolveEnd(id: " + this.id + ", url: '" + this.url + "', urlAfterRedirects: '" + this.urlAfterRedirects + "', state: " + this.state + ")";
        };
        return ResolveEnd;
    }(RouterEvent));
    /**
     * An event triggered before lazy loading a route configuration.
     *
     * @see `RouteConfigLoadEnd`
     *
     * @publicApi
     */
    var RouteConfigLoadStart = /** @class */ (function () {
        function RouteConfigLoadStart(
        /** @docsNotRequired */
        route) {
            this.route = route;
        }
        RouteConfigLoadStart.prototype.toString = function () {
            return "RouteConfigLoadStart(path: " + this.route.path + ")";
        };
        return RouteConfigLoadStart;
    }());
    /**
     * An event triggered when a route has been lazy loaded.
     *
     * @see `RouteConfigLoadStart`
     *
     * @publicApi
     */
    var RouteConfigLoadEnd = /** @class */ (function () {
        function RouteConfigLoadEnd(
        /** @docsNotRequired */
        route) {
            this.route = route;
        }
        RouteConfigLoadEnd.prototype.toString = function () {
            return "RouteConfigLoadEnd(path: " + this.route.path + ")";
        };
        return RouteConfigLoadEnd;
    }());
    /**
     * An event triggered at the start of the child-activation
     * part of the Resolve phase of routing.
     * @see  `ChildActivationEnd`
     * @see `ResolveStart`
     *
     * @publicApi
     */
    var ChildActivationStart = /** @class */ (function () {
        function ChildActivationStart(
        /** @docsNotRequired */
        snapshot) {
            this.snapshot = snapshot;
        }
        ChildActivationStart.prototype.toString = function () {
            var path = this.snapshot.routeConfig && this.snapshot.routeConfig.path || '';
            return "ChildActivationStart(path: '" + path + "')";
        };
        return ChildActivationStart;
    }());
    /**
     * An event triggered at the end of the child-activation part
     * of the Resolve phase of routing.
     * @see `ChildActivationStart`
     * @see `ResolveStart`
     * @publicApi
     */
    var ChildActivationEnd = /** @class */ (function () {
        function ChildActivationEnd(
        /** @docsNotRequired */
        snapshot) {
            this.snapshot = snapshot;
        }
        ChildActivationEnd.prototype.toString = function () {
            var path = this.snapshot.routeConfig && this.snapshot.routeConfig.path || '';
            return "ChildActivationEnd(path: '" + path + "')";
        };
        return ChildActivationEnd;
    }());
    /**
     * An event triggered at the start of the activation part
     * of the Resolve phase of routing.
     * @see `ActivationEnd`
     * @see `ResolveStart`
     *
     * @publicApi
     */
    var ActivationStart = /** @class */ (function () {
        function ActivationStart(
        /** @docsNotRequired */
        snapshot) {
            this.snapshot = snapshot;
        }
        ActivationStart.prototype.toString = function () {
            var path = this.snapshot.routeConfig && this.snapshot.routeConfig.path || '';
            return "ActivationStart(path: '" + path + "')";
        };
        return ActivationStart;
    }());
    /**
     * An event triggered at the end of the activation part
     * of the Resolve phase of routing.
     * @see `ActivationStart`
     * @see `ResolveStart`
     *
     * @publicApi
     */
    var ActivationEnd = /** @class */ (function () {
        function ActivationEnd(
        /** @docsNotRequired */
        snapshot) {
            this.snapshot = snapshot;
        }
        ActivationEnd.prototype.toString = function () {
            var path = this.snapshot.routeConfig && this.snapshot.routeConfig.path || '';
            return "ActivationEnd(path: '" + path + "')";
        };
        return ActivationEnd;
    }());
    /**
     * An event triggered by scrolling.
     *
     * @publicApi
     */
    var Scroll = /** @class */ (function () {
        function Scroll(
        /** @docsNotRequired */
        routerEvent, 
        /** @docsNotRequired */
        position, 
        /** @docsNotRequired */
        anchor) {
            this.routerEvent = routerEvent;
            this.position = position;
            this.anchor = anchor;
        }
        Scroll.prototype.toString = function () {
            var pos = this.position ? this.position[0] + ", " + this.position[1] : null;
            return "Scroll(anchor: '" + this.anchor + "', position: '" + pos + "')";
        };
        return Scroll;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * The primary routing outlet.
     *
     * @publicApi
     */
    var PRIMARY_OUTLET = 'primary';
    var ParamsAsMap = /** @class */ (function () {
        function ParamsAsMap(params) {
            this.params = params || {};
        }
        ParamsAsMap.prototype.has = function (name) {
            return Object.prototype.hasOwnProperty.call(this.params, name);
        };
        ParamsAsMap.prototype.get = function (name) {
            if (this.has(name)) {
                var v = this.params[name];
                return Array.isArray(v) ? v[0] : v;
            }
            return null;
        };
        ParamsAsMap.prototype.getAll = function (name) {
            if (this.has(name)) {
                var v = this.params[name];
                return Array.isArray(v) ? v : [v];
            }
            return [];
        };
        Object.defineProperty(ParamsAsMap.prototype, "keys", {
            get: function () {
                return Object.keys(this.params);
            },
            enumerable: false,
            configurable: true
        });
        return ParamsAsMap;
    }());
    /**
     * Converts a `Params` instance to a `ParamMap`.
     * @param params The instance to convert.
     * @returns The new map instance.
     *
     * @publicApi
     */
    function convertToParamMap(params) {
        return new ParamsAsMap(params);
    }
    var NAVIGATION_CANCELING_ERROR = 'ngNavigationCancelingError';
    function navigationCancelingError(message) {
        var error = Error('NavigationCancelingError: ' + message);
        error[NAVIGATION_CANCELING_ERROR] = true;
        return error;
    }
    function isNavigationCancelingError(error) {
        return error && error[NAVIGATION_CANCELING_ERROR];
    }
    // Matches the route configuration (`route`) against the actual URL (`segments`).
    function defaultUrlMatcher(segments, segmentGroup, route) {
        var parts = route.path.split('/');
        if (parts.length > segments.length) {
            // The actual URL is shorter than the config, no match
            return null;
        }
        if (route.pathMatch === 'full' &&
            (segmentGroup.hasChildren() || parts.length < segments.length)) {
            // The config is longer than the actual URL but we are looking for a full match, return null
            return null;
        }
        var posParams = {};
        // Check each config part against the actual URL
        for (var index = 0; index < parts.length; index++) {
            var part = parts[index];
            var segment = segments[index];
            var isParameter = part.startsWith(':');
            if (isParameter) {
                posParams[part.substring(1)] = segment;
            }
            else if (part !== segment.path) {
                // The actual URL part does not match the config, no match
                return null;
            }
        }
        return { consumed: segments.slice(0, parts.length), posParams: posParams };
    }

    function shallowEqualArrays(a, b) {
        if (a.length !== b.length)
            return false;
        for (var i = 0; i < a.length; ++i) {
            if (!shallowEqual(a[i], b[i]))
                return false;
        }
        return true;
    }
    function shallowEqual(a, b) {
        // While `undefined` should never be possible, it would sometimes be the case in IE 11
        // and pre-chromium Edge. The check below accounts for this edge case.
        var k1 = a ? Object.keys(a) : undefined;
        var k2 = b ? Object.keys(b) : undefined;
        if (!k1 || !k2 || k1.length != k2.length) {
            return false;
        }
        var key;
        for (var i = 0; i < k1.length; i++) {
            key = k1[i];
            if (!equalArraysOrString(a[key], b[key])) {
                return false;
            }
        }
        return true;
    }
    /**
     * Test equality for arrays of strings or a string.
     */
    function equalArraysOrString(a, b) {
        if (Array.isArray(a) && Array.isArray(b)) {
            if (a.length !== b.length)
                return false;
            var aSorted = __spread(a).sort();
            var bSorted_1 = __spread(b).sort();
            return aSorted.every(function (val, index) { return bSorted_1[index] === val; });
        }
        else {
            return a === b;
        }
    }
    /**
     * Flattens single-level nested arrays.
     */
    function flatten(arr) {
        return Array.prototype.concat.apply([], arr);
    }
    /**
     * Return the last element of an array.
     */
    function last(a) {
        return a.length > 0 ? a[a.length - 1] : null;
    }
    /**
     * Verifys all booleans in an array are `true`.
     */
    function and(bools) {
        return !bools.some(function (v) { return !v; });
    }
    function forEach(map, callback) {
        for (var prop in map) {
            if (map.hasOwnProperty(prop)) {
                callback(map[prop], prop);
            }
        }
    }
    function wrapIntoObservable(value) {
        if (core.ɵisObservable(value)) {
            return value;
        }
        if (core.ɵisPromise(value)) {
            // Use `Promise.resolve()` to wrap promise-like instances.
            // Required ie when a Resolver returns a AngularJS `$q` promise to correctly trigger the
            // change detection.
            return rxjs.from(Promise.resolve(value));
        }
        return rxjs.of(value);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function createEmptyUrlTree() {
        return new UrlTree(new UrlSegmentGroup([], {}), {}, null);
    }
    function containsTree(container, containee, exact) {
        if (exact) {
            return equalQueryParams(container.queryParams, containee.queryParams) &&
                equalSegmentGroups(container.root, containee.root);
        }
        return containsQueryParams(container.queryParams, containee.queryParams) &&
            containsSegmentGroup(container.root, containee.root);
    }
    function equalQueryParams(container, containee) {
        // TODO: This does not handle array params correctly.
        return shallowEqual(container, containee);
    }
    function equalSegmentGroups(container, containee) {
        if (!equalPath(container.segments, containee.segments))
            return false;
        if (container.numberOfChildren !== containee.numberOfChildren)
            return false;
        for (var c in containee.children) {
            if (!container.children[c])
                return false;
            if (!equalSegmentGroups(container.children[c], containee.children[c]))
                return false;
        }
        return true;
    }
    function containsQueryParams(container, containee) {
        return Object.keys(containee).length <= Object.keys(container).length &&
            Object.keys(containee).every(function (key) { return equalArraysOrString(container[key], containee[key]); });
    }
    function containsSegmentGroup(container, containee) {
        return containsSegmentGroupHelper(container, containee, containee.segments);
    }
    function containsSegmentGroupHelper(container, containee, containeePaths) {
        if (container.segments.length > containeePaths.length) {
            var current = container.segments.slice(0, containeePaths.length);
            if (!equalPath(current, containeePaths))
                return false;
            if (containee.hasChildren())
                return false;
            return true;
        }
        else if (container.segments.length === containeePaths.length) {
            if (!equalPath(container.segments, containeePaths))
                return false;
            for (var c in containee.children) {
                if (!container.children[c])
                    return false;
                if (!containsSegmentGroup(container.children[c], containee.children[c]))
                    return false;
            }
            return true;
        }
        else {
            var current = containeePaths.slice(0, container.segments.length);
            var next = containeePaths.slice(container.segments.length);
            if (!equalPath(container.segments, current))
                return false;
            if (!container.children[PRIMARY_OUTLET])
                return false;
            return containsSegmentGroupHelper(container.children[PRIMARY_OUTLET], containee, next);
        }
    }
    /**
     * @description
     *
     * Represents the parsed URL.
     *
     * Since a router state is a tree, and the URL is nothing but a serialized state, the URL is a
     * serialized tree.
     * UrlTree is a data structure that provides a lot of affordances in dealing with URLs
     *
     * @usageNotes
     * ### Example
     *
     * ```
     * @Component({templateUrl:'template.html'})
     * class MyComponent {
     *   constructor(router: Router) {
     *     const tree: UrlTree =
     *       router.parseUrl('/team/33/(user/victor//support:help)?debug=true#fragment');
     *     const f = tree.fragment; // return 'fragment'
     *     const q = tree.queryParams; // returns {debug: 'true'}
     *     const g: UrlSegmentGroup = tree.root.children[PRIMARY_OUTLET];
     *     const s: UrlSegment[] = g.segments; // returns 2 segments 'team' and '33'
     *     g.children[PRIMARY_OUTLET].segments; // returns 2 segments 'user' and 'victor'
     *     g.children['support'].segments; // return 1 segment 'help'
     *   }
     * }
     * ```
     *
     * @publicApi
     */
    var UrlTree = /** @class */ (function () {
        /** @internal */
        function UrlTree(
        /** The root segment group of the URL tree */
        root, 
        /** The query params of the URL */
        queryParams, 
        /** The fragment of the URL */
        fragment) {
            this.root = root;
            this.queryParams = queryParams;
            this.fragment = fragment;
        }
        Object.defineProperty(UrlTree.prototype, "queryParamMap", {
            get: function () {
                if (!this._queryParamMap) {
                    this._queryParamMap = convertToParamMap(this.queryParams);
                }
                return this._queryParamMap;
            },
            enumerable: false,
            configurable: true
        });
        /** @docsNotRequired */
        UrlTree.prototype.toString = function () {
            return DEFAULT_SERIALIZER.serialize(this);
        };
        return UrlTree;
    }());
    /**
     * @description
     *
     * Represents the parsed URL segment group.
     *
     * See `UrlTree` for more information.
     *
     * @publicApi
     */
    var UrlSegmentGroup = /** @class */ (function () {
        function UrlSegmentGroup(
        /** The URL segments of this group. See `UrlSegment` for more information */
        segments, 
        /** The list of children of this group */
        children) {
            var _this = this;
            this.segments = segments;
            this.children = children;
            /** The parent node in the url tree */
            this.parent = null;
            forEach(children, function (v, k) { return v.parent = _this; });
        }
        /** Whether the segment has child segments */
        UrlSegmentGroup.prototype.hasChildren = function () {
            return this.numberOfChildren > 0;
        };
        Object.defineProperty(UrlSegmentGroup.prototype, "numberOfChildren", {
            /** Number of child segments */
            get: function () {
                return Object.keys(this.children).length;
            },
            enumerable: false,
            configurable: true
        });
        /** @docsNotRequired */
        UrlSegmentGroup.prototype.toString = function () {
            return serializePaths(this);
        };
        return UrlSegmentGroup;
    }());
    /**
     * @description
     *
     * Represents a single URL segment.
     *
     * A UrlSegment is a part of a URL between the two slashes. It contains a path and the matrix
     * parameters associated with the segment.
     *
     * @usageNotes
     * ### Example
     *
     * ```
     * @Component({templateUrl:'template.html'})
     * class MyComponent {
     *   constructor(router: Router) {
     *     const tree: UrlTree = router.parseUrl('/team;id=33');
     *     const g: UrlSegmentGroup = tree.root.children[PRIMARY_OUTLET];
     *     const s: UrlSegment[] = g.segments;
     *     s[0].path; // returns 'team'
     *     s[0].parameters; // returns {id: 33}
     *   }
     * }
     * ```
     *
     * @publicApi
     */
    var UrlSegment = /** @class */ (function () {
        function UrlSegment(
        /** The path part of a URL segment */
        path, 
        /** The matrix parameters associated with a segment */
        parameters) {
            this.path = path;
            this.parameters = parameters;
        }
        Object.defineProperty(UrlSegment.prototype, "parameterMap", {
            get: function () {
                if (!this._parameterMap) {
                    this._parameterMap = convertToParamMap(this.parameters);
                }
                return this._parameterMap;
            },
            enumerable: false,
            configurable: true
        });
        /** @docsNotRequired */
        UrlSegment.prototype.toString = function () {
            return serializePath(this);
        };
        return UrlSegment;
    }());
    function equalSegments(as, bs) {
        return equalPath(as, bs) && as.every(function (a, i) { return shallowEqual(a.parameters, bs[i].parameters); });
    }
    function equalPath(as, bs) {
        if (as.length !== bs.length)
            return false;
        return as.every(function (a, i) { return a.path === bs[i].path; });
    }
    function mapChildrenIntoArray(segment, fn) {
        var res = [];
        forEach(segment.children, function (child, childOutlet) {
            if (childOutlet === PRIMARY_OUTLET) {
                res = res.concat(fn(child, childOutlet));
            }
        });
        forEach(segment.children, function (child, childOutlet) {
            if (childOutlet !== PRIMARY_OUTLET) {
                res = res.concat(fn(child, childOutlet));
            }
        });
        return res;
    }
    /**
     * @description
     *
     * Serializes and deserializes a URL string into a URL tree.
     *
     * The url serialization strategy is customizable. You can
     * make all URLs case insensitive by providing a custom UrlSerializer.
     *
     * See `DefaultUrlSerializer` for an example of a URL serializer.
     *
     * @publicApi
     */
    var UrlSerializer = /** @class */ (function () {
        function UrlSerializer() {
        }
        return UrlSerializer;
    }());
    /**
     * @description
     *
     * A default implementation of the `UrlSerializer`.
     *
     * Example URLs:
     *
     * ```
     * /inbox/33(popup:compose)
     * /inbox/33;open=true/messages/44
     * ```
     *
     * DefaultUrlSerializer uses parentheses to serialize secondary segments (e.g., popup:compose), the
     * colon syntax to specify the outlet, and the ';parameter=value' syntax (e.g., open=true) to
     * specify route specific parameters.
     *
     * @publicApi
     */
    var DefaultUrlSerializer = /** @class */ (function () {
        function DefaultUrlSerializer() {
        }
        /** Parses a url into a `UrlTree` */
        DefaultUrlSerializer.prototype.parse = function (url) {
            var p = new UrlParser(url);
            return new UrlTree(p.parseRootSegment(), p.parseQueryParams(), p.parseFragment());
        };
        /** Converts a `UrlTree` into a url */
        DefaultUrlSerializer.prototype.serialize = function (tree) {
            var segment = "/" + serializeSegment(tree.root, true);
            var query = serializeQueryParams(tree.queryParams);
            var fragment = typeof tree.fragment === "string" ? "#" + encodeUriFragment(tree.fragment) : '';
            return "" + segment + query + fragment;
        };
        return DefaultUrlSerializer;
    }());
    var DEFAULT_SERIALIZER = new DefaultUrlSerializer();
    function serializePaths(segment) {
        return segment.segments.map(function (p) { return serializePath(p); }).join('/');
    }
    function serializeSegment(segment, root) {
        if (!segment.hasChildren()) {
            return serializePaths(segment);
        }
        if (root) {
            var primary = segment.children[PRIMARY_OUTLET] ?
                serializeSegment(segment.children[PRIMARY_OUTLET], false) :
                '';
            var children_1 = [];
            forEach(segment.children, function (v, k) {
                if (k !== PRIMARY_OUTLET) {
                    children_1.push(k + ":" + serializeSegment(v, false));
                }
            });
            return children_1.length > 0 ? primary + "(" + children_1.join('//') + ")" : primary;
        }
        else {
            var children = mapChildrenIntoArray(segment, function (v, k) {
                if (k === PRIMARY_OUTLET) {
                    return [serializeSegment(segment.children[PRIMARY_OUTLET], false)];
                }
                return [k + ":" + serializeSegment(v, false)];
            });
            // use no parenthesis if the only child is a primary outlet route
            if (Object.keys(segment.children).length === 1 && segment.children[PRIMARY_OUTLET] != null) {
                return serializePaths(segment) + "/" + children[0];
            }
            return serializePaths(segment) + "/(" + children.join('//') + ")";
        }
    }
    /**
     * Encodes a URI string with the default encoding. This function will only ever be called from
     * `encodeUriQuery` or `encodeUriSegment` as it's the base set of encodings to be used. We need
     * a custom encoding because encodeURIComponent is too aggressive and encodes stuff that doesn't
     * have to be encoded per https://url.spec.whatwg.org.
     */
    function encodeUriString(s) {
        return encodeURIComponent(s)
            .replace(/%40/g, '@')
            .replace(/%3A/gi, ':')
            .replace(/%24/g, '$')
            .replace(/%2C/gi, ',');
    }
    /**
     * This function should be used to encode both keys and values in a query string key/value. In
     * the following URL, you need to call encodeUriQuery on "k" and "v":
     *
     * http://www.site.org/html;mk=mv?k=v#f
     */
    function encodeUriQuery(s) {
        return encodeUriString(s).replace(/%3B/gi, ';');
    }
    /**
     * This function should be used to encode a URL fragment. In the following URL, you need to call
     * encodeUriFragment on "f":
     *
     * http://www.site.org/html;mk=mv?k=v#f
     */
    function encodeUriFragment(s) {
        return encodeURI(s);
    }
    /**
     * This function should be run on any URI segment as well as the key and value in a key/value
     * pair for matrix params. In the following URL, you need to call encodeUriSegment on "html",
     * "mk", and "mv":
     *
     * http://www.site.org/html;mk=mv?k=v#f
     */
    function encodeUriSegment(s) {
        return encodeUriString(s).replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/%26/gi, '&');
    }
    function decode(s) {
        return decodeURIComponent(s);
    }
    // Query keys/values should have the "+" replaced first, as "+" in a query string is " ".
    // decodeURIComponent function will not decode "+" as a space.
    function decodeQuery(s) {
        return decode(s.replace(/\+/g, '%20'));
    }
    function serializePath(path) {
        return "" + encodeUriSegment(path.path) + serializeMatrixParams(path.parameters);
    }
    function serializeMatrixParams(params) {
        return Object.keys(params)
            .map(function (key) { return ";" + encodeUriSegment(key) + "=" + encodeUriSegment(params[key]); })
            .join('');
    }
    function serializeQueryParams(params) {
        var strParams = Object.keys(params).map(function (name) {
            var value = params[name];
            return Array.isArray(value) ?
                value.map(function (v) { return encodeUriQuery(name) + "=" + encodeUriQuery(v); }).join('&') :
                encodeUriQuery(name) + "=" + encodeUriQuery(value);
        });
        return strParams.length ? "?" + strParams.join('&') : '';
    }
    var SEGMENT_RE = /^[^\/()?;=#]+/;
    function matchSegments(str) {
        var match = str.match(SEGMENT_RE);
        return match ? match[0] : '';
    }
    var QUERY_PARAM_RE = /^[^=?&#]+/;
    // Return the name of the query param at the start of the string or an empty string
    function matchQueryParams(str) {
        var match = str.match(QUERY_PARAM_RE);
        return match ? match[0] : '';
    }
    var QUERY_PARAM_VALUE_RE = /^[^?&#]+/;
    // Return the value of the query param at the start of the string or an empty string
    function matchUrlQueryParamValue(str) {
        var match = str.match(QUERY_PARAM_VALUE_RE);
        return match ? match[0] : '';
    }
    var UrlParser = /** @class */ (function () {
        function UrlParser(url) {
            this.url = url;
            this.remaining = url;
        }
        UrlParser.prototype.parseRootSegment = function () {
            this.consumeOptional('/');
            if (this.remaining === '' || this.peekStartsWith('?') || this.peekStartsWith('#')) {
                return new UrlSegmentGroup([], {});
            }
            // The root segment group never has segments
            return new UrlSegmentGroup([], this.parseChildren());
        };
        UrlParser.prototype.parseQueryParams = function () {
            var params = {};
            if (this.consumeOptional('?')) {
                do {
                    this.parseQueryParam(params);
                } while (this.consumeOptional('&'));
            }
            return params;
        };
        UrlParser.prototype.parseFragment = function () {
            return this.consumeOptional('#') ? decodeURIComponent(this.remaining) : null;
        };
        UrlParser.prototype.parseChildren = function () {
            if (this.remaining === '') {
                return {};
            }
            this.consumeOptional('/');
            var segments = [];
            if (!this.peekStartsWith('(')) {
                segments.push(this.parseSegment());
            }
            while (this.peekStartsWith('/') && !this.peekStartsWith('//') && !this.peekStartsWith('/(')) {
                this.capture('/');
                segments.push(this.parseSegment());
            }
            var children = {};
            if (this.peekStartsWith('/(')) {
                this.capture('/');
                children = this.parseParens(true);
            }
            var res = {};
            if (this.peekStartsWith('(')) {
                res = this.parseParens(false);
            }
            if (segments.length > 0 || Object.keys(children).length > 0) {
                res[PRIMARY_OUTLET] = new UrlSegmentGroup(segments, children);
            }
            return res;
        };
        // parse a segment with its matrix parameters
        // ie `name;k1=v1;k2`
        UrlParser.prototype.parseSegment = function () {
            var path = matchSegments(this.remaining);
            if (path === '' && this.peekStartsWith(';')) {
                throw new Error("Empty path url segment cannot have parameters: '" + this.remaining + "'.");
            }
            this.capture(path);
            return new UrlSegment(decode(path), this.parseMatrixParams());
        };
        UrlParser.prototype.parseMatrixParams = function () {
            var params = {};
            while (this.consumeOptional(';')) {
                this.parseParam(params);
            }
            return params;
        };
        UrlParser.prototype.parseParam = function (params) {
            var key = matchSegments(this.remaining);
            if (!key) {
                return;
            }
            this.capture(key);
            var value = '';
            if (this.consumeOptional('=')) {
                var valueMatch = matchSegments(this.remaining);
                if (valueMatch) {
                    value = valueMatch;
                    this.capture(value);
                }
            }
            params[decode(key)] = decode(value);
        };
        // Parse a single query parameter `name[=value]`
        UrlParser.prototype.parseQueryParam = function (params) {
            var key = matchQueryParams(this.remaining);
            if (!key) {
                return;
            }
            this.capture(key);
            var value = '';
            if (this.consumeOptional('=')) {
                var valueMatch = matchUrlQueryParamValue(this.remaining);
                if (valueMatch) {
                    value = valueMatch;
                    this.capture(value);
                }
            }
            var decodedKey = decodeQuery(key);
            var decodedVal = decodeQuery(value);
            if (params.hasOwnProperty(decodedKey)) {
                // Append to existing values
                var currentVal = params[decodedKey];
                if (!Array.isArray(currentVal)) {
                    currentVal = [currentVal];
                    params[decodedKey] = currentVal;
                }
                currentVal.push(decodedVal);
            }
            else {
                // Create a new value
                params[decodedKey] = decodedVal;
            }
        };
        // parse `(a/b//outlet_name:c/d)`
        UrlParser.prototype.parseParens = function (allowPrimary) {
            var segments = {};
            this.capture('(');
            while (!this.consumeOptional(')') && this.remaining.length > 0) {
                var path = matchSegments(this.remaining);
                var next = this.remaining[path.length];
                // if is is not one of these characters, then the segment was unescaped
                // or the group was not closed
                if (next !== '/' && next !== ')' && next !== ';') {
                    throw new Error("Cannot parse url '" + this.url + "'");
                }
                var outletName = undefined;
                if (path.indexOf(':') > -1) {
                    outletName = path.substr(0, path.indexOf(':'));
                    this.capture(outletName);
                    this.capture(':');
                }
                else if (allowPrimary) {
                    outletName = PRIMARY_OUTLET;
                }
                var children = this.parseChildren();
                segments[outletName] = Object.keys(children).length === 1 ? children[PRIMARY_OUTLET] :
                    new UrlSegmentGroup([], children);
                this.consumeOptional('//');
            }
            return segments;
        };
        UrlParser.prototype.peekStartsWith = function (str) {
            return this.remaining.startsWith(str);
        };
        // Consumes the prefix when it is present and returns whether it has been consumed
        UrlParser.prototype.consumeOptional = function (str) {
            if (this.peekStartsWith(str)) {
                this.remaining = this.remaining.substring(str.length);
                return true;
            }
            return false;
        };
        UrlParser.prototype.capture = function (str) {
            if (!this.consumeOptional(str)) {
                throw new Error("Expected \"" + str + "\".");
            }
        };
        return UrlParser;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var Tree = /** @class */ (function () {
        function Tree(root) {
            this._root = root;
        }
        Object.defineProperty(Tree.prototype, "root", {
            get: function () {
                return this._root.value;
            },
            enumerable: false,
            configurable: true
        });
        /**
         * @internal
         */
        Tree.prototype.parent = function (t) {
            var p = this.pathFromRoot(t);
            return p.length > 1 ? p[p.length - 2] : null;
        };
        /**
         * @internal
         */
        Tree.prototype.children = function (t) {
            var n = findNode(t, this._root);
            return n ? n.children.map(function (t) { return t.value; }) : [];
        };
        /**
         * @internal
         */
        Tree.prototype.firstChild = function (t) {
            var n = findNode(t, this._root);
            return n && n.children.length > 0 ? n.children[0].value : null;
        };
        /**
         * @internal
         */
        Tree.prototype.siblings = function (t) {
            var p = findPath(t, this._root);
            if (p.length < 2)
                return [];
            var c = p[p.length - 2].children.map(function (c) { return c.value; });
            return c.filter(function (cc) { return cc !== t; });
        };
        /**
         * @internal
         */
        Tree.prototype.pathFromRoot = function (t) {
            return findPath(t, this._root).map(function (s) { return s.value; });
        };
        return Tree;
    }());
    // DFS for the node matching the value
    function findNode(value, node) {
        var e_1, _a;
        if (value === node.value)
            return node;
        try {
            for (var _b = __values(node.children), _c = _b.next(); !_c.done; _c = _b.next()) {
                var child = _c.value;
                var node_1 = findNode(value, child);
                if (node_1)
                    return node_1;
            }
        }
        catch (e_1_1) { e_1 = { error: e_1_1 }; }
        finally {
            try {
                if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
            }
            finally { if (e_1) throw e_1.error; }
        }
        return null;
    }
    // Return the path to the node with the given value using DFS
    function findPath(value, node) {
        var e_2, _a;
        if (value === node.value)
            return [node];
        try {
            for (var _b = __values(node.children), _c = _b.next(); !_c.done; _c = _b.next()) {
                var child = _c.value;
                var path = findPath(value, child);
                if (path.length) {
                    path.unshift(node);
                    return path;
                }
            }
        }
        catch (e_2_1) { e_2 = { error: e_2_1 }; }
        finally {
            try {
                if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
            }
            finally { if (e_2) throw e_2.error; }
        }
        return [];
    }
    var TreeNode = /** @class */ (function () {
        function TreeNode(value, children) {
            this.value = value;
            this.children = children;
        }
        TreeNode.prototype.toString = function () {
            return "TreeNode(" + this.value + ")";
        };
        return TreeNode;
    }());
    // Return the list of T indexed by outlet name
    function nodeChildrenAsMap(node) {
        var map = {};
        if (node) {
            node.children.forEach(function (child) { return map[child.value.outlet] = child; });
        }
        return map;
    }

    /**
     * Represents the state of the router as a tree of activated routes.
     *
     * @usageNotes
     *
     * Every node in the route tree is an `ActivatedRoute` instance
     * that knows about the "consumed" URL segments, the extracted parameters,
     * and the resolved data.
     * Use the `ActivatedRoute` properties to traverse the tree from any node.
     *
     * The following fragment shows how a component gets the root node
     * of the current state to establish its own route tree:
     *
     * ```
     * @Component({templateUrl:'template.html'})
     * class MyComponent {
     *   constructor(router: Router) {
     *     const state: RouterState = router.routerState;
     *     const root: ActivatedRoute = state.root;
     *     const child = root.firstChild;
     *     const id: Observable<string> = child.params.map(p => p.id);
     *     //...
     *   }
     * }
     * ```
     *
     * @see `ActivatedRoute`
     * @see [Getting route information](guide/router#getting-route-information)
     *
     * @publicApi
     */
    var RouterState = /** @class */ (function (_super) {
        __extends(RouterState, _super);
        /** @internal */
        function RouterState(root, 
        /** The current snapshot of the router state */
        snapshot) {
            var _this = _super.call(this, root) || this;
            _this.snapshot = snapshot;
            setRouterState(_this, root);
            return _this;
        }
        RouterState.prototype.toString = function () {
            return this.snapshot.toString();
        };
        return RouterState;
    }(Tree));
    function createEmptyState(urlTree, rootComponent) {
        var snapshot = createEmptyStateSnapshot(urlTree, rootComponent);
        var emptyUrl = new rxjs.BehaviorSubject([new UrlSegment('', {})]);
        var emptyParams = new rxjs.BehaviorSubject({});
        var emptyData = new rxjs.BehaviorSubject({});
        var emptyQueryParams = new rxjs.BehaviorSubject({});
        var fragment = new rxjs.BehaviorSubject('');
        var activated = new ActivatedRoute(emptyUrl, emptyParams, emptyQueryParams, fragment, emptyData, PRIMARY_OUTLET, rootComponent, snapshot.root);
        activated.snapshot = snapshot.root;
        return new RouterState(new TreeNode(activated, []), snapshot);
    }
    function createEmptyStateSnapshot(urlTree, rootComponent) {
        var emptyParams = {};
        var emptyData = {};
        var emptyQueryParams = {};
        var fragment = '';
        var activated = new ActivatedRouteSnapshot([], emptyParams, emptyQueryParams, fragment, emptyData, PRIMARY_OUTLET, rootComponent, null, urlTree.root, -1, {});
        return new RouterStateSnapshot('', new TreeNode(activated, []));
    }
    /**
     * Provides access to information about a route associated with a component
     * that is loaded in an outlet.
     * Use to traverse the `RouterState` tree and extract information from nodes.
     *
     * The following example shows how to construct a component using information from a
     * currently activated route.
     *
     * {@example router/activated-route/module.ts region="activated-route"
     *     header="activated-route.component.ts"}
     *
     * @see [Getting route information](guide/router#getting-route-information)
     *
     * @publicApi
     */
    var ActivatedRoute = /** @class */ (function () {
        /** @internal */
        function ActivatedRoute(
        /** An observable of the URL segments matched by this route. */
        url, 
        /** An observable of the matrix parameters scoped to this route. */
        params, 
        /** An observable of the query parameters shared by all the routes. */
        queryParams, 
        /** An observable of the URL fragment shared by all the routes. */
        fragment, 
        /** An observable of the static and resolved data of this route. */
        data, 
        /** The outlet name of the route, a constant. */
        outlet, 
        /** The component of the route, a constant. */
        // TODO(vsavkin): remove |string
        component, futureSnapshot) {
            this.url = url;
            this.params = params;
            this.queryParams = queryParams;
            this.fragment = fragment;
            this.data = data;
            this.outlet = outlet;
            this.component = component;
            this._futureSnapshot = futureSnapshot;
        }
        Object.defineProperty(ActivatedRoute.prototype, "routeConfig", {
            /** The configuration used to match this route. */
            get: function () {
                return this._futureSnapshot.routeConfig;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "root", {
            /** The root of the router state. */
            get: function () {
                return this._routerState.root;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "parent", {
            /** The parent of this route in the router state tree. */
            get: function () {
                return this._routerState.parent(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "firstChild", {
            /** The first child of this route in the router state tree. */
            get: function () {
                return this._routerState.firstChild(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "children", {
            /** The children of this route in the router state tree. */
            get: function () {
                return this._routerState.children(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "pathFromRoot", {
            /** The path from the root of the router state tree to this route. */
            get: function () {
                return this._routerState.pathFromRoot(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "paramMap", {
            /**
             * An Observable that contains a map of the required and optional parameters
             * specific to the route.
             * The map supports retrieving single and multiple values from the same parameter.
             */
            get: function () {
                if (!this._paramMap) {
                    this._paramMap = this.params.pipe(operators.map(function (p) { return convertToParamMap(p); }));
                }
                return this._paramMap;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRoute.prototype, "queryParamMap", {
            /**
             * An Observable that contains a map of the query parameters available to all routes.
             * The map supports retrieving single and multiple values from the query parameter.
             */
            get: function () {
                if (!this._queryParamMap) {
                    this._queryParamMap =
                        this.queryParams.pipe(operators.map(function (p) { return convertToParamMap(p); }));
                }
                return this._queryParamMap;
            },
            enumerable: false,
            configurable: true
        });
        ActivatedRoute.prototype.toString = function () {
            return this.snapshot ? this.snapshot.toString() : "Future(" + this._futureSnapshot + ")";
        };
        return ActivatedRoute;
    }());
    /**
     * Returns the inherited params, data, and resolve for a given route.
     * By default, this only inherits values up to the nearest path-less or component-less route.
     * @internal
     */
    function inheritedParamsDataResolve(route, paramsInheritanceStrategy) {
        if (paramsInheritanceStrategy === void 0) { paramsInheritanceStrategy = 'emptyOnly'; }
        var pathFromRoot = route.pathFromRoot;
        var inheritingStartingFrom = 0;
        if (paramsInheritanceStrategy !== 'always') {
            inheritingStartingFrom = pathFromRoot.length - 1;
            while (inheritingStartingFrom >= 1) {
                var current = pathFromRoot[inheritingStartingFrom];
                var parent = pathFromRoot[inheritingStartingFrom - 1];
                // current route is an empty path => inherits its parent's params and data
                if (current.routeConfig && current.routeConfig.path === '') {
                    inheritingStartingFrom--;
                    // parent is componentless => current route should inherit its params and data
                }
                else if (!parent.component) {
                    inheritingStartingFrom--;
                }
                else {
                    break;
                }
            }
        }
        return flattenInherited(pathFromRoot.slice(inheritingStartingFrom));
    }
    /** @internal */
    function flattenInherited(pathFromRoot) {
        return pathFromRoot.reduce(function (res, curr) {
            var params = Object.assign(Object.assign({}, res.params), curr.params);
            var data = Object.assign(Object.assign({}, res.data), curr.data);
            var resolve = Object.assign(Object.assign({}, res.resolve), curr._resolvedData);
            return { params: params, data: data, resolve: resolve };
        }, { params: {}, data: {}, resolve: {} });
    }
    /**
     * @description
     *
     * Contains the information about a route associated with a component loaded in an
     * outlet at a particular moment in time. ActivatedRouteSnapshot can also be used to
     * traverse the router state tree.
     *
     * The following example initializes a component with route information extracted
     * from the snapshot of the root node at the time of creation.
     *
     * ```
     * @Component({templateUrl:'./my-component.html'})
     * class MyComponent {
     *   constructor(route: ActivatedRoute) {
     *     const id: string = route.snapshot.params.id;
     *     const url: string = route.snapshot.url.join('');
     *     const user = route.snapshot.data.user;
     *   }
     * }
     * ```
     *
     * @publicApi
     */
    var ActivatedRouteSnapshot = /** @class */ (function () {
        /** @internal */
        function ActivatedRouteSnapshot(
        /** The URL segments matched by this route */
        url, 
        /**
         *  The matrix parameters scoped to this route.
         *
         *  You can compute all params (or data) in the router state or to get params outside
         *  of an activated component by traversing the `RouterState` tree as in the following
         *  example:
         *  ```
         *  collectRouteParams(router: Router) {
         *    let params = {};
         *    let stack: ActivatedRouteSnapshot[] = [router.routerState.snapshot.root];
         *    while (stack.length > 0) {
         *      const route = stack.pop()!;
         *      params = {...params, ...route.params};
         *      stack.push(...route.children);
         *    }
         *    return params;
         *  }
         *  ```
         */
        params, 
        /** The query parameters shared by all the routes */
        queryParams, 
        /** The URL fragment shared by all the routes */
        fragment, 
        /** The static and resolved data of this route */
        data, 
        /** The outlet name of the route */
        outlet, 
        /** The component of the route */
        component, routeConfig, urlSegment, lastPathIndex, resolve) {
            this.url = url;
            this.params = params;
            this.queryParams = queryParams;
            this.fragment = fragment;
            this.data = data;
            this.outlet = outlet;
            this.component = component;
            this.routeConfig = routeConfig;
            this._urlSegment = urlSegment;
            this._lastPathIndex = lastPathIndex;
            this._resolve = resolve;
        }
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "root", {
            /** The root of the router state */
            get: function () {
                return this._routerState.root;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "parent", {
            /** The parent of this route in the router state tree */
            get: function () {
                return this._routerState.parent(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "firstChild", {
            /** The first child of this route in the router state tree */
            get: function () {
                return this._routerState.firstChild(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "children", {
            /** The children of this route in the router state tree */
            get: function () {
                return this._routerState.children(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "pathFromRoot", {
            /** The path from the root of the router state tree to this route */
            get: function () {
                return this._routerState.pathFromRoot(this);
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "paramMap", {
            get: function () {
                if (!this._paramMap) {
                    this._paramMap = convertToParamMap(this.params);
                }
                return this._paramMap;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(ActivatedRouteSnapshot.prototype, "queryParamMap", {
            get: function () {
                if (!this._queryParamMap) {
                    this._queryParamMap = convertToParamMap(this.queryParams);
                }
                return this._queryParamMap;
            },
            enumerable: false,
            configurable: true
        });
        ActivatedRouteSnapshot.prototype.toString = function () {
            var url = this.url.map(function (segment) { return segment.toString(); }).join('/');
            var matched = this.routeConfig ? this.routeConfig.path : '';
            return "Route(url:'" + url + "', path:'" + matched + "')";
        };
        return ActivatedRouteSnapshot;
    }());
    /**
     * @description
     *
     * Represents the state of the router at a moment in time.
     *
     * This is a tree of activated route snapshots. Every node in this tree knows about
     * the "consumed" URL segments, the extracted parameters, and the resolved data.
     *
     * The following example shows how a component is initialized with information
     * from the snapshot of the root node's state at the time of creation.
     *
     * ```
     * @Component({templateUrl:'template.html'})
     * class MyComponent {
     *   constructor(router: Router) {
     *     const state: RouterState = router.routerState;
     *     const snapshot: RouterStateSnapshot = state.snapshot;
     *     const root: ActivatedRouteSnapshot = snapshot.root;
     *     const child = root.firstChild;
     *     const id: Observable<string> = child.params.map(p => p.id);
     *     //...
     *   }
     * }
     * ```
     *
     * @publicApi
     */
    var RouterStateSnapshot = /** @class */ (function (_super) {
        __extends(RouterStateSnapshot, _super);
        /** @internal */
        function RouterStateSnapshot(
        /** The url from which this snapshot was created */
        url, root) {
            var _this = _super.call(this, root) || this;
            _this.url = url;
            setRouterState(_this, root);
            return _this;
        }
        RouterStateSnapshot.prototype.toString = function () {
            return serializeNode(this._root);
        };
        return RouterStateSnapshot;
    }(Tree));
    function setRouterState(state, node) {
        node.value._routerState = state;
        node.children.forEach(function (c) { return setRouterState(state, c); });
    }
    function serializeNode(node) {
        var c = node.children.length > 0 ? " { " + node.children.map(serializeNode).join(', ') + " } " : '';
        return "" + node.value + c;
    }
    /**
     * The expectation is that the activate route is created with the right set of parameters.
     * So we push new values into the observables only when they are not the initial values.
     * And we detect that by checking if the snapshot field is set.
     */
    function advanceActivatedRoute(route) {
        if (route.snapshot) {
            var currentSnapshot = route.snapshot;
            var nextSnapshot = route._futureSnapshot;
            route.snapshot = nextSnapshot;
            if (!shallowEqual(currentSnapshot.queryParams, nextSnapshot.queryParams)) {
                route.queryParams.next(nextSnapshot.queryParams);
            }
            if (currentSnapshot.fragment !== nextSnapshot.fragment) {
                route.fragment.next(nextSnapshot.fragment);
            }
            if (!shallowEqual(currentSnapshot.params, nextSnapshot.params)) {
                route.params.next(nextSnapshot.params);
            }
            if (!shallowEqualArrays(currentSnapshot.url, nextSnapshot.url)) {
                route.url.next(nextSnapshot.url);
            }
            if (!shallowEqual(currentSnapshot.data, nextSnapshot.data)) {
                route.data.next(nextSnapshot.data);
            }
        }
        else {
            route.snapshot = route._futureSnapshot;
            // this is for resolved data
            route.data.next(route._futureSnapshot.data);
        }
    }
    function equalParamsAndUrlSegments(a, b) {
        var equalUrlParams = shallowEqual(a.params, b.params) && equalSegments(a.url, b.url);
        var parentsMismatch = !a.parent !== !b.parent;
        return equalUrlParams && !parentsMismatch &&
            (!a.parent || equalParamsAndUrlSegments(a.parent, b.parent));
    }

    function createRouterState(routeReuseStrategy, curr, prevState) {
        var root = createNode(routeReuseStrategy, curr._root, prevState ? prevState._root : undefined);
        return new RouterState(root, curr);
    }
    function createNode(routeReuseStrategy, curr, prevState) {
        // reuse an activated route that is currently displayed on the screen
        if (prevState && routeReuseStrategy.shouldReuseRoute(curr.value, prevState.value.snapshot)) {
            var value = prevState.value;
            value._futureSnapshot = curr.value;
            var children = createOrReuseChildren(routeReuseStrategy, curr, prevState);
            return new TreeNode(value, children);
            // retrieve an activated route that is used to be displayed, but is not currently displayed
        }
        else {
            var detachedRouteHandle = routeReuseStrategy.retrieve(curr.value);
            if (detachedRouteHandle) {
                var tree = detachedRouteHandle.route;
                setFutureSnapshotsOfActivatedRoutes(curr, tree);
                return tree;
            }
            else {
                var value = createActivatedRoute(curr.value);
                var children = curr.children.map(function (c) { return createNode(routeReuseStrategy, c); });
                return new TreeNode(value, children);
            }
        }
    }
    function setFutureSnapshotsOfActivatedRoutes(curr, result) {
        if (curr.value.routeConfig !== result.value.routeConfig) {
            throw new Error('Cannot reattach ActivatedRouteSnapshot created from a different route');
        }
        if (curr.children.length !== result.children.length) {
            throw new Error('Cannot reattach ActivatedRouteSnapshot with a different number of children');
        }
        result.value._futureSnapshot = curr.value;
        for (var i = 0; i < curr.children.length; ++i) {
            setFutureSnapshotsOfActivatedRoutes(curr.children[i], result.children[i]);
        }
    }
    function createOrReuseChildren(routeReuseStrategy, curr, prevState) {
        return curr.children.map(function (child) {
            var e_1, _a;
            try {
                for (var _b = __values(prevState.children), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var p = _c.value;
                    if (routeReuseStrategy.shouldReuseRoute(child.value, p.value.snapshot)) {
                        return createNode(routeReuseStrategy, child, p);
                    }
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            return createNode(routeReuseStrategy, child);
        });
    }
    function createActivatedRoute(c) {
        return new ActivatedRoute(new rxjs.BehaviorSubject(c.url), new rxjs.BehaviorSubject(c.params), new rxjs.BehaviorSubject(c.queryParams), new rxjs.BehaviorSubject(c.fragment), new rxjs.BehaviorSubject(c.data), c.outlet, c.component, c);
    }

    function createUrlTree(route, urlTree, commands, queryParams, fragment) {
        if (commands.length === 0) {
            return tree(urlTree.root, urlTree.root, urlTree, queryParams, fragment);
        }
        var nav = computeNavigation(commands);
        if (nav.toRoot()) {
            return tree(urlTree.root, new UrlSegmentGroup([], {}), urlTree, queryParams, fragment);
        }
        var startingPosition = findStartingPosition(nav, urlTree, route);
        var segmentGroup = startingPosition.processChildren ?
            updateSegmentGroupChildren(startingPosition.segmentGroup, startingPosition.index, nav.commands) :
            updateSegmentGroup(startingPosition.segmentGroup, startingPosition.index, nav.commands);
        return tree(startingPosition.segmentGroup, segmentGroup, urlTree, queryParams, fragment);
    }
    function isMatrixParams(command) {
        return typeof command === 'object' && command != null && !command.outlets && !command.segmentPath;
    }
    /**
     * Determines if a given command has an `outlets` map. When we encounter a command
     * with an outlets k/v map, we need to apply each outlet individually to the existing segment.
     */
    function isCommandWithOutlets(command) {
        return typeof command === 'object' && command != null && command.outlets;
    }
    function tree(oldSegmentGroup, newSegmentGroup, urlTree, queryParams, fragment) {
        var qp = {};
        if (queryParams) {
            forEach(queryParams, function (value, name) {
                qp[name] = Array.isArray(value) ? value.map(function (v) { return "" + v; }) : "" + value;
            });
        }
        if (urlTree.root === oldSegmentGroup) {
            return new UrlTree(newSegmentGroup, qp, fragment);
        }
        return new UrlTree(replaceSegment(urlTree.root, oldSegmentGroup, newSegmentGroup), qp, fragment);
    }
    function replaceSegment(current, oldSegment, newSegment) {
        var children = {};
        forEach(current.children, function (c, outletName) {
            if (c === oldSegment) {
                children[outletName] = newSegment;
            }
            else {
                children[outletName] = replaceSegment(c, oldSegment, newSegment);
            }
        });
        return new UrlSegmentGroup(current.segments, children);
    }
    var Navigation = /** @class */ (function () {
        function Navigation(isAbsolute, numberOfDoubleDots, commands) {
            this.isAbsolute = isAbsolute;
            this.numberOfDoubleDots = numberOfDoubleDots;
            this.commands = commands;
            if (isAbsolute && commands.length > 0 && isMatrixParams(commands[0])) {
                throw new Error('Root segment cannot have matrix parameters');
            }
            var cmdWithOutlet = commands.find(isCommandWithOutlets);
            if (cmdWithOutlet && cmdWithOutlet !== last(commands)) {
                throw new Error('{outlets:{}} has to be the last command');
            }
        }
        Navigation.prototype.toRoot = function () {
            return this.isAbsolute && this.commands.length === 1 && this.commands[0] == '/';
        };
        return Navigation;
    }());
    /** Transforms commands to a normalized `Navigation` */
    function computeNavigation(commands) {
        if ((typeof commands[0] === 'string') && commands.length === 1 && commands[0] === '/') {
            return new Navigation(true, 0, commands);
        }
        var numberOfDoubleDots = 0;
        var isAbsolute = false;
        var res = commands.reduce(function (res, cmd, cmdIdx) {
            if (typeof cmd === 'object' && cmd != null) {
                if (cmd.outlets) {
                    var outlets_1 = {};
                    forEach(cmd.outlets, function (commands, name) {
                        outlets_1[name] = typeof commands === 'string' ? commands.split('/') : commands;
                    });
                    return __spread(res, [{ outlets: outlets_1 }]);
                }
                if (cmd.segmentPath) {
                    return __spread(res, [cmd.segmentPath]);
                }
            }
            if (!(typeof cmd === 'string')) {
                return __spread(res, [cmd]);
            }
            if (cmdIdx === 0) {
                cmd.split('/').forEach(function (urlPart, partIndex) {
                    if (partIndex == 0 && urlPart === '.') {
                        // skip './a'
                    }
                    else if (partIndex == 0 && urlPart === '') { //  '/a'
                        isAbsolute = true;
                    }
                    else if (urlPart === '..') { //  '../a'
                        numberOfDoubleDots++;
                    }
                    else if (urlPart != '') {
                        res.push(urlPart);
                    }
                });
                return res;
            }
            return __spread(res, [cmd]);
        }, []);
        return new Navigation(isAbsolute, numberOfDoubleDots, res);
    }
    var Position = /** @class */ (function () {
        function Position(segmentGroup, processChildren, index) {
            this.segmentGroup = segmentGroup;
            this.processChildren = processChildren;
            this.index = index;
        }
        return Position;
    }());
    function findStartingPosition(nav, tree, route) {
        if (nav.isAbsolute) {
            return new Position(tree.root, true, 0);
        }
        if (route.snapshot._lastPathIndex === -1) {
            var segmentGroup = route.snapshot._urlSegment;
            // Pathless ActivatedRoute has _lastPathIndex === -1 but should not process children
            // see issue #26224, #13011, #35687
            // However, if the ActivatedRoute is the root we should process children like above.
            var processChildren = segmentGroup === tree.root;
            return new Position(segmentGroup, processChildren, 0);
        }
        var modifier = isMatrixParams(nav.commands[0]) ? 0 : 1;
        var index = route.snapshot._lastPathIndex + modifier;
        return createPositionApplyingDoubleDots(route.snapshot._urlSegment, index, nav.numberOfDoubleDots);
    }
    function createPositionApplyingDoubleDots(group, index, numberOfDoubleDots) {
        var g = group;
        var ci = index;
        var dd = numberOfDoubleDots;
        while (dd > ci) {
            dd -= ci;
            g = g.parent;
            if (!g) {
                throw new Error('Invalid number of \'../\'');
            }
            ci = g.segments.length;
        }
        return new Position(g, false, ci - dd);
    }
    function getOutlets(commands) {
        var _a;
        if (isCommandWithOutlets(commands[0])) {
            return commands[0].outlets;
        }
        return _a = {}, _a[PRIMARY_OUTLET] = commands, _a;
    }
    function updateSegmentGroup(segmentGroup, startIndex, commands) {
        if (!segmentGroup) {
            segmentGroup = new UrlSegmentGroup([], {});
        }
        if (segmentGroup.segments.length === 0 && segmentGroup.hasChildren()) {
            return updateSegmentGroupChildren(segmentGroup, startIndex, commands);
        }
        var m = prefixedWith(segmentGroup, startIndex, commands);
        var slicedCommands = commands.slice(m.commandIndex);
        if (m.match && m.pathIndex < segmentGroup.segments.length) {
            var g = new UrlSegmentGroup(segmentGroup.segments.slice(0, m.pathIndex), {});
            g.children[PRIMARY_OUTLET] =
                new UrlSegmentGroup(segmentGroup.segments.slice(m.pathIndex), segmentGroup.children);
            return updateSegmentGroupChildren(g, 0, slicedCommands);
        }
        else if (m.match && slicedCommands.length === 0) {
            return new UrlSegmentGroup(segmentGroup.segments, {});
        }
        else if (m.match && !segmentGroup.hasChildren()) {
            return createNewSegmentGroup(segmentGroup, startIndex, commands);
        }
        else if (m.match) {
            return updateSegmentGroupChildren(segmentGroup, 0, slicedCommands);
        }
        else {
            return createNewSegmentGroup(segmentGroup, startIndex, commands);
        }
    }
    function updateSegmentGroupChildren(segmentGroup, startIndex, commands) {
        if (commands.length === 0) {
            return new UrlSegmentGroup(segmentGroup.segments, {});
        }
        else {
            var outlets_2 = getOutlets(commands);
            var children_1 = {};
            forEach(outlets_2, function (commands, outlet) {
                if (typeof commands === 'string') {
                    commands = [commands];
                }
                if (commands !== null) {
                    children_1[outlet] = updateSegmentGroup(segmentGroup.children[outlet], startIndex, commands);
                }
            });
            forEach(segmentGroup.children, function (child, childOutlet) {
                if (outlets_2[childOutlet] === undefined) {
                    children_1[childOutlet] = child;
                }
            });
            return new UrlSegmentGroup(segmentGroup.segments, children_1);
        }
    }
    function prefixedWith(segmentGroup, startIndex, commands) {
        var currentCommandIndex = 0;
        var currentPathIndex = startIndex;
        var noMatch = { match: false, pathIndex: 0, commandIndex: 0 };
        while (currentPathIndex < segmentGroup.segments.length) {
            if (currentCommandIndex >= commands.length)
                return noMatch;
            var path = segmentGroup.segments[currentPathIndex];
            var command = commands[currentCommandIndex];
            // Do not try to consume command as part of the prefixing if it has outlets because it can
            // contain outlets other than the one being processed. Consuming the outlets command would
            // result in other outlets being ignored.
            if (isCommandWithOutlets(command)) {
                break;
            }
            var curr = "" + command;
            var next = currentCommandIndex < commands.length - 1 ? commands[currentCommandIndex + 1] : null;
            if (currentPathIndex > 0 && curr === undefined)
                break;
            if (curr && next && (typeof next === 'object') && next.outlets === undefined) {
                if (!compare(curr, next, path))
                    return noMatch;
                currentCommandIndex += 2;
            }
            else {
                if (!compare(curr, {}, path))
                    return noMatch;
                currentCommandIndex++;
            }
            currentPathIndex++;
        }
        return { match: true, pathIndex: currentPathIndex, commandIndex: currentCommandIndex };
    }
    function createNewSegmentGroup(segmentGroup, startIndex, commands) {
        var paths = segmentGroup.segments.slice(0, startIndex);
        var i = 0;
        while (i < commands.length) {
            var command = commands[i];
            if (isCommandWithOutlets(command)) {
                var children = createNewSegmentChildren(command.outlets);
                return new UrlSegmentGroup(paths, children);
            }
            // if we start with an object literal, we need to reuse the path part from the segment
            if (i === 0 && isMatrixParams(commands[0])) {
                var p = segmentGroup.segments[startIndex];
                paths.push(new UrlSegment(p.path, stringify(commands[0])));
                i++;
                continue;
            }
            var curr = isCommandWithOutlets(command) ? command.outlets[PRIMARY_OUTLET] : "" + command;
            var next = (i < commands.length - 1) ? commands[i + 1] : null;
            if (curr && next && isMatrixParams(next)) {
                paths.push(new UrlSegment(curr, stringify(next)));
                i += 2;
            }
            else {
                paths.push(new UrlSegment(curr, {}));
                i++;
            }
        }
        return new UrlSegmentGroup(paths, {});
    }
    function createNewSegmentChildren(outlets) {
        var children = {};
        forEach(outlets, function (commands, outlet) {
            if (typeof commands === 'string') {
                commands = [commands];
            }
            if (commands !== null) {
                children[outlet] = createNewSegmentGroup(new UrlSegmentGroup([], {}), 0, commands);
            }
        });
        return children;
    }
    function stringify(params) {
        var res = {};
        forEach(params, function (v, k) { return res[k] = "" + v; });
        return res;
    }
    function compare(path, params, segment) {
        return path == segment.path && shallowEqual(params, segment.parameters);
    }

    var activateRoutes = function (rootContexts, routeReuseStrategy, forwardEvent) { return operators.map(function (t) {
        new ActivateRoutes(routeReuseStrategy, t.targetRouterState, t.currentRouterState, forwardEvent)
            .activate(rootContexts);
        return t;
    }); };
    var ActivateRoutes = /** @class */ (function () {
        function ActivateRoutes(routeReuseStrategy, futureState, currState, forwardEvent) {
            this.routeReuseStrategy = routeReuseStrategy;
            this.futureState = futureState;
            this.currState = currState;
            this.forwardEvent = forwardEvent;
        }
        ActivateRoutes.prototype.activate = function (parentContexts) {
            var futureRoot = this.futureState._root;
            var currRoot = this.currState ? this.currState._root : null;
            this.deactivateChildRoutes(futureRoot, currRoot, parentContexts);
            advanceActivatedRoute(this.futureState.root);
            this.activateChildRoutes(futureRoot, currRoot, parentContexts);
        };
        // De-activate the child route that are not re-used for the future state
        ActivateRoutes.prototype.deactivateChildRoutes = function (futureNode, currNode, contexts) {
            var _this = this;
            var children = nodeChildrenAsMap(currNode);
            // Recurse on the routes active in the future state to de-activate deeper children
            futureNode.children.forEach(function (futureChild) {
                var childOutletName = futureChild.value.outlet;
                _this.deactivateRoutes(futureChild, children[childOutletName], contexts);
                delete children[childOutletName];
            });
            // De-activate the routes that will not be re-used
            forEach(children, function (v, childName) {
                _this.deactivateRouteAndItsChildren(v, contexts);
            });
        };
        ActivateRoutes.prototype.deactivateRoutes = function (futureNode, currNode, parentContext) {
            var future = futureNode.value;
            var curr = currNode ? currNode.value : null;
            if (future === curr) {
                // Reusing the node, check to see if the children need to be de-activated
                if (future.component) {
                    // If we have a normal route, we need to go through an outlet.
                    var context = parentContext.getContext(future.outlet);
                    if (context) {
                        this.deactivateChildRoutes(futureNode, currNode, context.children);
                    }
                }
                else {
                    // if we have a componentless route, we recurse but keep the same outlet map.
                    this.deactivateChildRoutes(futureNode, currNode, parentContext);
                }
            }
            else {
                if (curr) {
                    // Deactivate the current route which will not be re-used
                    this.deactivateRouteAndItsChildren(currNode, parentContext);
                }
            }
        };
        ActivateRoutes.prototype.deactivateRouteAndItsChildren = function (route, parentContexts) {
            if (this.routeReuseStrategy.shouldDetach(route.value.snapshot)) {
                this.detachAndStoreRouteSubtree(route, parentContexts);
            }
            else {
                this.deactivateRouteAndOutlet(route, parentContexts);
            }
        };
        ActivateRoutes.prototype.detachAndStoreRouteSubtree = function (route, parentContexts) {
            var context = parentContexts.getContext(route.value.outlet);
            if (context && context.outlet) {
                var componentRef = context.outlet.detach();
                var contexts = context.children.onOutletDeactivated();
                this.routeReuseStrategy.store(route.value.snapshot, { componentRef: componentRef, route: route, contexts: contexts });
            }
        };
        ActivateRoutes.prototype.deactivateRouteAndOutlet = function (route, parentContexts) {
            var e_1, _a;
            var context = parentContexts.getContext(route.value.outlet);
            // The context could be `null` if we are on a componentless route but there may still be
            // children that need deactivating.
            var contexts = context && route.value.component ? context.children : parentContexts;
            var children = nodeChildrenAsMap(route);
            try {
                for (var _b = __values(Object.keys(children)), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var childOutlet = _c.value;
                    this.deactivateRouteAndItsChildren(children[childOutlet], contexts);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            if (context && context.outlet) {
                // Destroy the component
                context.outlet.deactivate();
                // Destroy the contexts for all the outlets that were in the component
                context.children.onOutletDeactivated();
            }
        };
        ActivateRoutes.prototype.activateChildRoutes = function (futureNode, currNode, contexts) {
            var _this = this;
            var children = nodeChildrenAsMap(currNode);
            futureNode.children.forEach(function (c) {
                _this.activateRoutes(c, children[c.value.outlet], contexts);
                _this.forwardEvent(new ActivationEnd(c.value.snapshot));
            });
            if (futureNode.children.length) {
                this.forwardEvent(new ChildActivationEnd(futureNode.value.snapshot));
            }
        };
        ActivateRoutes.prototype.activateRoutes = function (futureNode, currNode, parentContexts) {
            var future = futureNode.value;
            var curr = currNode ? currNode.value : null;
            advanceActivatedRoute(future);
            // reusing the node
            if (future === curr) {
                if (future.component) {
                    // If we have a normal route, we need to go through an outlet.
                    var context = parentContexts.getOrCreateContext(future.outlet);
                    this.activateChildRoutes(futureNode, currNode, context.children);
                }
                else {
                    // if we have a componentless route, we recurse but keep the same outlet map.
                    this.activateChildRoutes(futureNode, currNode, parentContexts);
                }
            }
            else {
                if (future.component) {
                    // if we have a normal route, we need to place the component into the outlet and recurse.
                    var context = parentContexts.getOrCreateContext(future.outlet);
                    if (this.routeReuseStrategy.shouldAttach(future.snapshot)) {
                        var stored = this.routeReuseStrategy.retrieve(future.snapshot);
                        this.routeReuseStrategy.store(future.snapshot, null);
                        context.children.onOutletReAttached(stored.contexts);
                        context.attachRef = stored.componentRef;
                        context.route = stored.route.value;
                        if (context.outlet) {
                            // Attach right away when the outlet has already been instantiated
                            // Otherwise attach from `RouterOutlet.ngOnInit` when it is instantiated
                            context.outlet.attach(stored.componentRef, stored.route.value);
                        }
                        advanceActivatedRouteNodeAndItsChildren(stored.route);
                    }
                    else {
                        var config = parentLoadedConfig(future.snapshot);
                        var cmpFactoryResolver = config ? config.module.componentFactoryResolver : null;
                        context.attachRef = null;
                        context.route = future;
                        context.resolver = cmpFactoryResolver;
                        if (context.outlet) {
                            // Activate the outlet when it has already been instantiated
                            // Otherwise it will get activated from its `ngOnInit` when instantiated
                            context.outlet.activateWith(future, cmpFactoryResolver);
                        }
                        this.activateChildRoutes(futureNode, null, context.children);
                    }
                }
                else {
                    // if we have a componentless route, we recurse but keep the same outlet map.
                    this.activateChildRoutes(futureNode, null, parentContexts);
                }
            }
        };
        return ActivateRoutes;
    }());
    function advanceActivatedRouteNodeAndItsChildren(node) {
        advanceActivatedRoute(node.value);
        node.children.forEach(advanceActivatedRouteNodeAndItsChildren);
    }
    function parentLoadedConfig(snapshot) {
        for (var s = snapshot.parent; s; s = s.parent) {
            var route = s.routeConfig;
            if (route && route._loadedConfig)
                return route._loadedConfig;
            if (route && route.component)
                return null;
        }
        return null;
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var LoadedRouterConfig = /** @class */ (function () {
        function LoadedRouterConfig(routes, module) {
            this.routes = routes;
            this.module = module;
        }
        return LoadedRouterConfig;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Simple function check, but generic so type inference will flow. Example:
     *
     * function product(a: number, b: number) {
     *   return a * b;
     * }
     *
     * if (isFunction<product>(fn)) {
     *   return fn(1, 2);
     * } else {
     *   throw "Must provide the `product` function";
     * }
     */
    function isFunction(v) {
        return typeof v === 'function';
    }
    function isBoolean(v) {
        return typeof v === 'boolean';
    }
    function isUrlTree(v) {
        return v instanceof UrlTree;
    }
    function isCanLoad(guard) {
        return guard && isFunction(guard.canLoad);
    }
    function isCanActivate(guard) {
        return guard && isFunction(guard.canActivate);
    }
    function isCanActivateChild(guard) {
        return guard && isFunction(guard.canActivateChild);
    }
    function isCanDeactivate(guard) {
        return guard && isFunction(guard.canDeactivate);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var INITIAL_VALUE = Symbol('INITIAL_VALUE');
    function prioritizedGuardValue() {
        return operators.switchMap(function (obs) {
            return rxjs.combineLatest(obs.map(function (o) { return o.pipe(operators.take(1), operators.startWith(INITIAL_VALUE)); }))
                .pipe(operators.scan(function (acc, list) {
                var isPending = false;
                return list.reduce(function (innerAcc, val, i) {
                    if (innerAcc !== INITIAL_VALUE)
                        return innerAcc;
                    // Toggle pending flag if any values haven't been set yet
                    if (val === INITIAL_VALUE)
                        isPending = true;
                    // Any other return values are only valid if we haven't yet hit a pending
                    // call. This guarantees that in the case of a guard at the bottom of the
                    // tree that returns a redirect, we will wait for the higher priority
                    // guard at the top to finish before performing the redirect.
                    if (!isPending) {
                        // Early return when we hit a `false` value as that should always
                        // cancel navigation
                        if (val === false)
                            return val;
                        if (i === list.length - 1 || isUrlTree(val)) {
                            return val;
                        }
                    }
                    return innerAcc;
                }, acc);
            }, INITIAL_VALUE), operators.filter(function (item) { return item !== INITIAL_VALUE; }), operators.map(function (item) { return isUrlTree(item) ? item : item === true; }), //
            operators.take(1));
        });
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * This component is used internally within the router to be a placeholder when an empty
     * router-outlet is needed. For example, with a config such as:
     *
     * `{path: 'parent', outlet: 'nav', children: [...]}`
     *
     * In order to render, there needs to be a component on this config, which will default
     * to this `EmptyOutletComponent`.
     */
    var ɵEmptyOutletComponent = /** @class */ (function () {
        function ɵEmptyOutletComponent() {
        }
        return ɵEmptyOutletComponent;
    }());
    ɵEmptyOutletComponent.decorators = [
        { type: core.Component, args: [{ template: "<router-outlet></router-outlet>" },] }
    ];

    function validateConfig(config, parentPath) {
        if (parentPath === void 0) { parentPath = ''; }
        // forEach doesn't iterate undefined values
        for (var i = 0; i < config.length; i++) {
            var route = config[i];
            var fullPath = getFullPath(parentPath, route);
            validateNode(route, fullPath);
        }
    }
    function validateNode(route, fullPath) {
        if (typeof ngDevMode === 'undefined' || ngDevMode) {
            if (!route) {
                throw new Error("\n      Invalid configuration of route '" + fullPath + "': Encountered undefined route.\n      The reason might be an extra comma.\n\n      Example:\n      const routes: Routes = [\n        { path: '', redirectTo: '/dashboard', pathMatch: 'full' },\n        { path: 'dashboard',  component: DashboardComponent },, << two commas\n        { path: 'detail/:id', component: HeroDetailComponent }\n      ];\n    ");
            }
            if (Array.isArray(route)) {
                throw new Error("Invalid configuration of route '" + fullPath + "': Array cannot be specified");
            }
            if (!route.component && !route.children && !route.loadChildren &&
                (route.outlet && route.outlet !== PRIMARY_OUTLET)) {
                throw new Error("Invalid configuration of route '" + fullPath + "': a componentless route without children or loadChildren cannot have a named outlet set");
            }
            if (route.redirectTo && route.children) {
                throw new Error("Invalid configuration of route '" + fullPath + "': redirectTo and children cannot be used together");
            }
            if (route.redirectTo && route.loadChildren) {
                throw new Error("Invalid configuration of route '" + fullPath + "': redirectTo and loadChildren cannot be used together");
            }
            if (route.children && route.loadChildren) {
                throw new Error("Invalid configuration of route '" + fullPath + "': children and loadChildren cannot be used together");
            }
            if (route.redirectTo && route.component) {
                throw new Error("Invalid configuration of route '" + fullPath + "': redirectTo and component cannot be used together");
            }
            if (route.redirectTo && route.canActivate) {
                throw new Error("Invalid configuration of route '" + fullPath + "': redirectTo and canActivate cannot be used together. Redirects happen before activation " +
                    "so canActivate will never be executed.");
            }
            if (route.path && route.matcher) {
                throw new Error("Invalid configuration of route '" + fullPath + "': path and matcher cannot be used together");
            }
            if (route.redirectTo === void 0 && !route.component && !route.children && !route.loadChildren) {
                throw new Error("Invalid configuration of route '" + fullPath + "'. One of the following must be provided: component, redirectTo, children or loadChildren");
            }
            if (route.path === void 0 && route.matcher === void 0) {
                throw new Error("Invalid configuration of route '" + fullPath + "': routes must have either a path or a matcher specified");
            }
            if (typeof route.path === 'string' && route.path.charAt(0) === '/') {
                throw new Error("Invalid configuration of route '" + fullPath + "': path cannot start with a slash");
            }
            if (route.path === '' && route.redirectTo !== void 0 && route.pathMatch === void 0) {
                var exp = "The default value of 'pathMatch' is 'prefix', but often the intent is to use 'full'.";
                throw new Error("Invalid configuration of route '{path: \"" + fullPath + "\", redirectTo: \"" + route.redirectTo + "\"}': please provide 'pathMatch'. " + exp);
            }
            if (route.pathMatch !== void 0 && route.pathMatch !== 'full' && route.pathMatch !== 'prefix') {
                throw new Error("Invalid configuration of route '" + fullPath + "': pathMatch can only be set to 'prefix' or 'full'");
            }
        }
        if (route.children) {
            validateConfig(route.children, fullPath);
        }
    }
    function getFullPath(parentPath, currentRoute) {
        if (!currentRoute) {
            return parentPath;
        }
        if (!parentPath && !currentRoute.path) {
            return '';
        }
        else if (parentPath && !currentRoute.path) {
            return parentPath + "/";
        }
        else if (!parentPath && currentRoute.path) {
            return currentRoute.path;
        }
        else {
            return parentPath + "/" + currentRoute.path;
        }
    }
    /**
     * Makes a copy of the config and adds any default required properties.
     */
    function standardizeConfig(r) {
        var children = r.children && r.children.map(standardizeConfig);
        var c = children ? Object.assign(Object.assign({}, r), { children: children }) : Object.assign({}, r);
        if (!c.component && (children || c.loadChildren) && (c.outlet && c.outlet !== PRIMARY_OUTLET)) {
            c.component = ɵEmptyOutletComponent;
        }
        return c;
    }
    /** Returns the `route.outlet` or PRIMARY_OUTLET if none exists. */
    function getOutlet(route) {
        return route.outlet || PRIMARY_OUTLET;
    }
    /**
     * Sorts the `routes` such that the ones with an outlet matching `outletName` come first.
     * The order of the configs is otherwise preserved.
     */
    function sortByMatchingOutlets(routes, outletName) {
        var sortedConfig = routes.filter(function (r) { return getOutlet(r) === outletName; });
        sortedConfig.push.apply(sortedConfig, __spread(routes.filter(function (r) { return getOutlet(r) !== outletName; })));
        return sortedConfig;
    }

    var noMatch = {
        matched: false,
        consumedSegments: [],
        lastChild: 0,
        parameters: {},
        positionalParamSegments: {}
    };
    function match(segmentGroup, route, segments) {
        var _a;
        if (route.path === '') {
            if (route.pathMatch === 'full' && (segmentGroup.hasChildren() || segments.length > 0)) {
                return Object.assign({}, noMatch);
            }
            return {
                matched: true,
                consumedSegments: [],
                lastChild: 0,
                parameters: {},
                positionalParamSegments: {}
            };
        }
        var matcher = route.matcher || defaultUrlMatcher;
        var res = matcher(segments, segmentGroup, route);
        if (!res)
            return Object.assign({}, noMatch);
        var posParams = {};
        forEach(res.posParams, function (v, k) {
            posParams[k] = v.path;
        });
        var parameters = res.consumed.length > 0 ? Object.assign(Object.assign({}, posParams), res.consumed[res.consumed.length - 1].parameters) :
            posParams;
        return {
            matched: true,
            consumedSegments: res.consumed,
            lastChild: res.consumed.length,
            // TODO(atscott): investigate combining parameters and positionalParamSegments
            parameters: parameters,
            positionalParamSegments: (_a = res.posParams) !== null && _a !== void 0 ? _a : {}
        };
    }
    function split(segmentGroup, consumedSegments, slicedSegments, config, relativeLinkResolution) {
        if (relativeLinkResolution === void 0) { relativeLinkResolution = 'corrected'; }
        if (slicedSegments.length > 0 &&
            containsEmptyPathMatchesWithNamedOutlets(segmentGroup, slicedSegments, config)) {
            var s_1 = new UrlSegmentGroup(consumedSegments, createChildrenForEmptyPaths(segmentGroup, consumedSegments, config, new UrlSegmentGroup(slicedSegments, segmentGroup.children)));
            s_1._sourceSegment = segmentGroup;
            s_1._segmentIndexShift = consumedSegments.length;
            return { segmentGroup: s_1, slicedSegments: [] };
        }
        if (slicedSegments.length === 0 &&
            containsEmptyPathMatches(segmentGroup, slicedSegments, config)) {
            var s_2 = new UrlSegmentGroup(segmentGroup.segments, addEmptyPathsToChildrenIfNeeded(segmentGroup, consumedSegments, slicedSegments, config, segmentGroup.children, relativeLinkResolution));
            s_2._sourceSegment = segmentGroup;
            s_2._segmentIndexShift = consumedSegments.length;
            return { segmentGroup: s_2, slicedSegments: slicedSegments };
        }
        var s = new UrlSegmentGroup(segmentGroup.segments, segmentGroup.children);
        s._sourceSegment = segmentGroup;
        s._segmentIndexShift = consumedSegments.length;
        return { segmentGroup: s, slicedSegments: slicedSegments };
    }
    function addEmptyPathsToChildrenIfNeeded(segmentGroup, consumedSegments, slicedSegments, routes, children, relativeLinkResolution) {
        var e_1, _b;
        var res = {};
        try {
            for (var routes_1 = __values(routes), routes_1_1 = routes_1.next(); !routes_1_1.done; routes_1_1 = routes_1.next()) {
                var r = routes_1_1.value;
                if (emptyPathMatch(segmentGroup, slicedSegments, r) && !children[getOutlet(r)]) {
                    var s = new UrlSegmentGroup([], {});
                    s._sourceSegment = segmentGroup;
                    if (relativeLinkResolution === 'legacy') {
                        s._segmentIndexShift = segmentGroup.segments.length;
                    }
                    else {
                        s._segmentIndexShift = consumedSegments.length;
                    }
                    res[getOutlet(r)] = s;
                }
            }
        }
        catch (e_1_1) { e_1 = { error: e_1_1 }; }
        finally {
            try {
                if (routes_1_1 && !routes_1_1.done && (_b = routes_1.return)) _b.call(routes_1);
            }
            finally { if (e_1) throw e_1.error; }
        }
        return Object.assign(Object.assign({}, children), res);
    }
    function createChildrenForEmptyPaths(segmentGroup, consumedSegments, routes, primarySegment) {
        var e_2, _b;
        var res = {};
        res[PRIMARY_OUTLET] = primarySegment;
        primarySegment._sourceSegment = segmentGroup;
        primarySegment._segmentIndexShift = consumedSegments.length;
        try {
            for (var routes_2 = __values(routes), routes_2_1 = routes_2.next(); !routes_2_1.done; routes_2_1 = routes_2.next()) {
                var r = routes_2_1.value;
                if (r.path === '' && getOutlet(r) !== PRIMARY_OUTLET) {
                    var s = new UrlSegmentGroup([], {});
                    s._sourceSegment = segmentGroup;
                    s._segmentIndexShift = consumedSegments.length;
                    res[getOutlet(r)] = s;
                }
            }
        }
        catch (e_2_1) { e_2 = { error: e_2_1 }; }
        finally {
            try {
                if (routes_2_1 && !routes_2_1.done && (_b = routes_2.return)) _b.call(routes_2);
            }
            finally { if (e_2) throw e_2.error; }
        }
        return res;
    }
    function containsEmptyPathMatchesWithNamedOutlets(segmentGroup, slicedSegments, routes) {
        return routes.some(function (r) { return emptyPathMatch(segmentGroup, slicedSegments, r) && getOutlet(r) !== PRIMARY_OUTLET; });
    }
    function containsEmptyPathMatches(segmentGroup, slicedSegments, routes) {
        return routes.some(function (r) { return emptyPathMatch(segmentGroup, slicedSegments, r); });
    }
    function emptyPathMatch(segmentGroup, slicedSegments, r) {
        if ((segmentGroup.hasChildren() || slicedSegments.length > 0) && r.pathMatch === 'full') {
            return false;
        }
        return r.path === '';
    }
    /**
     * Determines if `route` is a path match for the `rawSegment`, `segments`, and `outlet` without
     * verifying that its children are a full match for the remainder of the `rawSegment` children as
     * well.
     */
    function isImmediateMatch(route, rawSegment, segments, outlet) {
        // We allow matches to empty paths when the outlets differ so we can match a url like `/(b:b)` to
        // a config like
        // * `{path: '', children: [{path: 'b', outlet: 'b'}]}`
        // or even
        // * `{path: '', outlet: 'a', children: [{path: 'b', outlet: 'b'}]`
        //
        // The exception here is when the segment outlet is for the primary outlet. This would
        // result in a match inside the named outlet because all children there are written as primary
        // outlets. So we need to prevent child named outlet matches in a url like `/b` in a config like
        // * `{path: '', outlet: 'x' children: [{path: 'b'}]}`
        // This should only match if the url is `/(x:b)`.
        if (getOutlet(route) !== outlet &&
            (outlet === PRIMARY_OUTLET || !emptyPathMatch(rawSegment, segments, route))) {
            return false;
        }
        if (route.path === '**') {
            return true;
        }
        return match(rawSegment, route, segments).matched;
    }
    function noLeftoversInUrl(segmentGroup, segments, outlet) {
        return segments.length === 0 && !segmentGroup.children[outlet];
    }

    var NoMatch = /** @class */ (function () {
        function NoMatch(segmentGroup) {
            this.segmentGroup = segmentGroup || null;
        }
        return NoMatch;
    }());
    var AbsoluteRedirect = /** @class */ (function () {
        function AbsoluteRedirect(urlTree) {
            this.urlTree = urlTree;
        }
        return AbsoluteRedirect;
    }());
    function noMatch$1(segmentGroup) {
        return new rxjs.Observable(function (obs) { return obs.error(new NoMatch(segmentGroup)); });
    }
    function absoluteRedirect(newTree) {
        return new rxjs.Observable(function (obs) { return obs.error(new AbsoluteRedirect(newTree)); });
    }
    function namedOutletsRedirect(redirectTo) {
        return new rxjs.Observable(function (obs) { return obs.error(new Error("Only absolute redirects can have named outlets. redirectTo: '" + redirectTo + "'")); });
    }
    function canLoadFails(route) {
        return new rxjs.Observable(function (obs) { return obs.error(navigationCancelingError("Cannot load children because the guard of the route \"path: '" + route.path + "'\" returned false")); });
    }
    /**
     * Returns the `UrlTree` with the redirection applied.
     *
     * Lazy modules are loaded along the way.
     */
    function applyRedirects(moduleInjector, configLoader, urlSerializer, urlTree, config) {
        return new ApplyRedirects(moduleInjector, configLoader, urlSerializer, urlTree, config).apply();
    }
    var ApplyRedirects = /** @class */ (function () {
        function ApplyRedirects(moduleInjector, configLoader, urlSerializer, urlTree, config) {
            this.configLoader = configLoader;
            this.urlSerializer = urlSerializer;
            this.urlTree = urlTree;
            this.config = config;
            this.allowRedirects = true;
            this.ngModule = moduleInjector.get(core.NgModuleRef);
        }
        ApplyRedirects.prototype.apply = function () {
            var _this = this;
            var splitGroup = split(this.urlTree.root, [], [], this.config).segmentGroup;
            // TODO(atscott): creating a new segment removes the _sourceSegment _segmentIndexShift, which is
            // only necessary to prevent failures in tests which assert exact object matches. The `split` is
            // now shared between `applyRedirects` and `recognize` but only the `recognize` step needs these
            // properties. Before the implementations were merged, the `applyRedirects` would not assign
            // them. We should be able to remove this logic as a "breaking change" but should do some more
            // investigation into the failures first.
            var rootSegmentGroup = new UrlSegmentGroup(splitGroup.segments, splitGroup.children);
            var expanded$ = this.expandSegmentGroup(this.ngModule, this.config, rootSegmentGroup, PRIMARY_OUTLET);
            var urlTrees$ = expanded$.pipe(operators.map(function (rootSegmentGroup) {
                return _this.createUrlTree(squashSegmentGroup(rootSegmentGroup), _this.urlTree.queryParams, _this.urlTree.fragment);
            }));
            return urlTrees$.pipe(operators.catchError(function (e) {
                if (e instanceof AbsoluteRedirect) {
                    // after an absolute redirect we do not apply any more redirects!
                    _this.allowRedirects = false;
                    // we need to run matching, so we can fetch all lazy-loaded modules
                    return _this.match(e.urlTree);
                }
                if (e instanceof NoMatch) {
                    throw _this.noMatchError(e);
                }
                throw e;
            }));
        };
        ApplyRedirects.prototype.match = function (tree) {
            var _this = this;
            var expanded$ = this.expandSegmentGroup(this.ngModule, this.config, tree.root, PRIMARY_OUTLET);
            var mapped$ = expanded$.pipe(operators.map(function (rootSegmentGroup) {
                return _this.createUrlTree(squashSegmentGroup(rootSegmentGroup), tree.queryParams, tree.fragment);
            }));
            return mapped$.pipe(operators.catchError(function (e) {
                if (e instanceof NoMatch) {
                    throw _this.noMatchError(e);
                }
                throw e;
            }));
        };
        ApplyRedirects.prototype.noMatchError = function (e) {
            return new Error("Cannot match any routes. URL Segment: '" + e.segmentGroup + "'");
        };
        ApplyRedirects.prototype.createUrlTree = function (rootCandidate, queryParams, fragment) {
            var _a;
            var root = rootCandidate.segments.length > 0 ?
                new UrlSegmentGroup([], (_a = {}, _a[PRIMARY_OUTLET] = rootCandidate, _a)) :
                rootCandidate;
            return new UrlTree(root, queryParams, fragment);
        };
        ApplyRedirects.prototype.expandSegmentGroup = function (ngModule, routes, segmentGroup, outlet) {
            if (segmentGroup.segments.length === 0 && segmentGroup.hasChildren()) {
                return this.expandChildren(ngModule, routes, segmentGroup)
                    .pipe(operators.map(function (children) { return new UrlSegmentGroup([], children); }));
            }
            return this.expandSegment(ngModule, segmentGroup, routes, segmentGroup.segments, outlet, true);
        };
        // Recursively expand segment groups for all the child outlets
        ApplyRedirects.prototype.expandChildren = function (ngModule, routes, segmentGroup) {
            var e_1, _a;
            var _this = this;
            // Expand outlets one at a time, starting with the primary outlet. We need to do it this way
            // because an absolute redirect from the primary outlet takes precedence.
            var childOutlets = [];
            try {
                for (var _b = __values(Object.keys(segmentGroup.children)), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var child = _c.value;
                    if (child === 'primary') {
                        childOutlets.unshift(child);
                    }
                    else {
                        childOutlets.push(child);
                    }
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            return rxjs.from(childOutlets)
                .pipe(operators.concatMap(function (childOutlet) {
                var child = segmentGroup.children[childOutlet];
                // Sort the routes so routes with outlets that match the segment appear
                // first, followed by routes for other outlets, which might match if they have an
                // empty path.
                var sortedRoutes = sortByMatchingOutlets(routes, childOutlet);
                return _this.expandSegmentGroup(ngModule, sortedRoutes, child, childOutlet)
                    .pipe(operators.map(function (s) { return ({ segment: s, outlet: childOutlet }); }));
            }), operators.scan(function (children, expandedChild) {
                children[expandedChild.outlet] = expandedChild.segment;
                return children;
            }, {}), operators.last());
        };
        ApplyRedirects.prototype.expandSegment = function (ngModule, segmentGroup, routes, segments, outlet, allowRedirects) {
            var _this = this;
            return rxjs.from(routes).pipe(operators.concatMap(function (r) {
                var expanded$ = _this.expandSegmentAgainstRoute(ngModule, segmentGroup, routes, r, segments, outlet, allowRedirects);
                return expanded$.pipe(operators.catchError(function (e) {
                    if (e instanceof NoMatch) {
                        return rxjs.of(null);
                    }
                    throw e;
                }));
            }), operators.first(function (s) { return !!s; }), operators.catchError(function (e, _) {
                if (e instanceof rxjs.EmptyError || e.name === 'EmptyError') {
                    if (noLeftoversInUrl(segmentGroup, segments, outlet)) {
                        return rxjs.of(new UrlSegmentGroup([], {}));
                    }
                    throw new NoMatch(segmentGroup);
                }
                throw e;
            }));
        };
        ApplyRedirects.prototype.expandSegmentAgainstRoute = function (ngModule, segmentGroup, routes, route, paths, outlet, allowRedirects) {
            if (!isImmediateMatch(route, segmentGroup, paths, outlet)) {
                return noMatch$1(segmentGroup);
            }
            if (route.redirectTo === undefined) {
                return this.matchSegmentAgainstRoute(ngModule, segmentGroup, route, paths, outlet);
            }
            if (allowRedirects && this.allowRedirects) {
                return this.expandSegmentAgainstRouteUsingRedirect(ngModule, segmentGroup, routes, route, paths, outlet);
            }
            return noMatch$1(segmentGroup);
        };
        ApplyRedirects.prototype.expandSegmentAgainstRouteUsingRedirect = function (ngModule, segmentGroup, routes, route, segments, outlet) {
            if (route.path === '**') {
                return this.expandWildCardWithParamsAgainstRouteUsingRedirect(ngModule, routes, route, outlet);
            }
            return this.expandRegularSegmentAgainstRouteUsingRedirect(ngModule, segmentGroup, routes, route, segments, outlet);
        };
        ApplyRedirects.prototype.expandWildCardWithParamsAgainstRouteUsingRedirect = function (ngModule, routes, route, outlet) {
            var _this = this;
            var newTree = this.applyRedirectCommands([], route.redirectTo, {});
            if (route.redirectTo.startsWith('/')) {
                return absoluteRedirect(newTree);
            }
            return this.lineralizeSegments(route, newTree).pipe(operators.mergeMap(function (newSegments) {
                var group = new UrlSegmentGroup(newSegments, {});
                return _this.expandSegment(ngModule, group, routes, newSegments, outlet, false);
            }));
        };
        ApplyRedirects.prototype.expandRegularSegmentAgainstRouteUsingRedirect = function (ngModule, segmentGroup, routes, route, segments, outlet) {
            var _this = this;
            var _a = match(segmentGroup, route, segments), matched = _a.matched, consumedSegments = _a.consumedSegments, lastChild = _a.lastChild, positionalParamSegments = _a.positionalParamSegments;
            if (!matched)
                return noMatch$1(segmentGroup);
            var newTree = this.applyRedirectCommands(consumedSegments, route.redirectTo, positionalParamSegments);
            if (route.redirectTo.startsWith('/')) {
                return absoluteRedirect(newTree);
            }
            return this.lineralizeSegments(route, newTree).pipe(operators.mergeMap(function (newSegments) {
                return _this.expandSegment(ngModule, segmentGroup, routes, newSegments.concat(segments.slice(lastChild)), outlet, false);
            }));
        };
        ApplyRedirects.prototype.matchSegmentAgainstRoute = function (ngModule, rawSegmentGroup, route, segments, outlet) {
            var _this = this;
            if (route.path === '**') {
                if (route.loadChildren) {
                    var loaded$ = route._loadedConfig ? rxjs.of(route._loadedConfig) :
                        this.configLoader.load(ngModule.injector, route);
                    return loaded$.pipe(operators.map(function (cfg) {
                        route._loadedConfig = cfg;
                        return new UrlSegmentGroup(segments, {});
                    }));
                }
                return rxjs.of(new UrlSegmentGroup(segments, {}));
            }
            var _a = match(rawSegmentGroup, route, segments), matched = _a.matched, consumedSegments = _a.consumedSegments, lastChild = _a.lastChild;
            if (!matched)
                return noMatch$1(rawSegmentGroup);
            var rawSlicedSegments = segments.slice(lastChild);
            var childConfig$ = this.getChildConfig(ngModule, route, segments);
            return childConfig$.pipe(operators.mergeMap(function (routerConfig) {
                var childModule = routerConfig.module;
                var childConfig = routerConfig.routes;
                var _a = split(rawSegmentGroup, consumedSegments, rawSlicedSegments, childConfig), splitSegmentGroup = _a.segmentGroup, slicedSegments = _a.slicedSegments;
                // See comment on the other call to `split` about why this is necessary.
                var segmentGroup = new UrlSegmentGroup(splitSegmentGroup.segments, splitSegmentGroup.children);
                if (slicedSegments.length === 0 && segmentGroup.hasChildren()) {
                    var expanded$_1 = _this.expandChildren(childModule, childConfig, segmentGroup);
                    return expanded$_1.pipe(operators.map(function (children) { return new UrlSegmentGroup(consumedSegments, children); }));
                }
                if (childConfig.length === 0 && slicedSegments.length === 0) {
                    return rxjs.of(new UrlSegmentGroup(consumedSegments, {}));
                }
                var matchedOnOutlet = getOutlet(route) === outlet;
                var expanded$ = _this.expandSegment(childModule, segmentGroup, childConfig, slicedSegments, matchedOnOutlet ? PRIMARY_OUTLET : outlet, true);
                return expanded$.pipe(operators.map(function (cs) { return new UrlSegmentGroup(consumedSegments.concat(cs.segments), cs.children); }));
            }));
        };
        ApplyRedirects.prototype.getChildConfig = function (ngModule, route, segments) {
            var _this = this;
            if (route.children) {
                // The children belong to the same module
                return rxjs.of(new LoadedRouterConfig(route.children, ngModule));
            }
            if (route.loadChildren) {
                // lazy children belong to the loaded module
                if (route._loadedConfig !== undefined) {
                    return rxjs.of(route._loadedConfig);
                }
                return this.runCanLoadGuards(ngModule.injector, route, segments)
                    .pipe(operators.mergeMap(function (shouldLoadResult) {
                    if (shouldLoadResult) {
                        return _this.configLoader.load(ngModule.injector, route)
                            .pipe(operators.map(function (cfg) {
                            route._loadedConfig = cfg;
                            return cfg;
                        }));
                    }
                    return canLoadFails(route);
                }));
            }
            return rxjs.of(new LoadedRouterConfig([], ngModule));
        };
        ApplyRedirects.prototype.runCanLoadGuards = function (moduleInjector, route, segments) {
            var _this = this;
            var canLoad = route.canLoad;
            if (!canLoad || canLoad.length === 0)
                return rxjs.of(true);
            var canLoadObservables = canLoad.map(function (injectionToken) {
                var guard = moduleInjector.get(injectionToken);
                var guardVal;
                if (isCanLoad(guard)) {
                    guardVal = guard.canLoad(route, segments);
                }
                else if (isFunction(guard)) {
                    guardVal = guard(route, segments);
                }
                else {
                    throw new Error('Invalid CanLoad guard');
                }
                return wrapIntoObservable(guardVal);
            });
            return rxjs.of(canLoadObservables)
                .pipe(prioritizedGuardValue(), operators.tap(function (result) {
                if (!isUrlTree(result))
                    return;
                var error = navigationCancelingError("Redirecting to \"" + _this.urlSerializer.serialize(result) + "\"");
                error.url = result;
                throw error;
            }), operators.map(function (result) { return result === true; }));
        };
        ApplyRedirects.prototype.lineralizeSegments = function (route, urlTree) {
            var res = [];
            var c = urlTree.root;
            while (true) {
                res = res.concat(c.segments);
                if (c.numberOfChildren === 0) {
                    return rxjs.of(res);
                }
                if (c.numberOfChildren > 1 || !c.children[PRIMARY_OUTLET]) {
                    return namedOutletsRedirect(route.redirectTo);
                }
                c = c.children[PRIMARY_OUTLET];
            }
        };
        ApplyRedirects.prototype.applyRedirectCommands = function (segments, redirectTo, posParams) {
            return this.applyRedirectCreatreUrlTree(redirectTo, this.urlSerializer.parse(redirectTo), segments, posParams);
        };
        ApplyRedirects.prototype.applyRedirectCreatreUrlTree = function (redirectTo, urlTree, segments, posParams) {
            var newRoot = this.createSegmentGroup(redirectTo, urlTree.root, segments, posParams);
            return new UrlTree(newRoot, this.createQueryParams(urlTree.queryParams, this.urlTree.queryParams), urlTree.fragment);
        };
        ApplyRedirects.prototype.createQueryParams = function (redirectToParams, actualParams) {
            var res = {};
            forEach(redirectToParams, function (v, k) {
                var copySourceValue = typeof v === 'string' && v.startsWith(':');
                if (copySourceValue) {
                    var sourceName = v.substring(1);
                    res[k] = actualParams[sourceName];
                }
                else {
                    res[k] = v;
                }
            });
            return res;
        };
        ApplyRedirects.prototype.createSegmentGroup = function (redirectTo, group, segments, posParams) {
            var _this = this;
            var updatedSegments = this.createSegments(redirectTo, group.segments, segments, posParams);
            var children = {};
            forEach(group.children, function (child, name) {
                children[name] = _this.createSegmentGroup(redirectTo, child, segments, posParams);
            });
            return new UrlSegmentGroup(updatedSegments, children);
        };
        ApplyRedirects.prototype.createSegments = function (redirectTo, redirectToSegments, actualSegments, posParams) {
            var _this = this;
            return redirectToSegments.map(function (s) { return s.path.startsWith(':') ? _this.findPosParam(redirectTo, s, posParams) :
                _this.findOrReturn(s, actualSegments); });
        };
        ApplyRedirects.prototype.findPosParam = function (redirectTo, redirectToUrlSegment, posParams) {
            var pos = posParams[redirectToUrlSegment.path.substring(1)];
            if (!pos)
                throw new Error("Cannot redirect to '" + redirectTo + "'. Cannot find '" + redirectToUrlSegment.path + "'.");
            return pos;
        };
        ApplyRedirects.prototype.findOrReturn = function (redirectToUrlSegment, actualSegments) {
            var e_2, _a;
            var idx = 0;
            try {
                for (var actualSegments_1 = __values(actualSegments), actualSegments_1_1 = actualSegments_1.next(); !actualSegments_1_1.done; actualSegments_1_1 = actualSegments_1.next()) {
                    var s = actualSegments_1_1.value;
                    if (s.path === redirectToUrlSegment.path) {
                        actualSegments.splice(idx);
                        return s;
                    }
                    idx++;
                }
            }
            catch (e_2_1) { e_2 = { error: e_2_1 }; }
            finally {
                try {
                    if (actualSegments_1_1 && !actualSegments_1_1.done && (_a = actualSegments_1.return)) _a.call(actualSegments_1);
                }
                finally { if (e_2) throw e_2.error; }
            }
            return redirectToUrlSegment;
        };
        return ApplyRedirects;
    }());
    /**
     * When possible, merges the primary outlet child into the parent `UrlSegmentGroup`.
     *
     * When a segment group has only one child which is a primary outlet, merges that child into the
     * parent. That is, the child segment group's segments are merged into the `s` and the child's
     * children become the children of `s`. Think of this like a 'squash', merging the child segment
     * group into the parent.
     */
    function mergeTrivialChildren(s) {
        if (s.numberOfChildren === 1 && s.children[PRIMARY_OUTLET]) {
            var c = s.children[PRIMARY_OUTLET];
            return new UrlSegmentGroup(s.segments.concat(c.segments), c.children);
        }
        return s;
    }
    /**
     * Recursively merges primary segment children into their parents and also drops empty children
     * (those which have no segments and no children themselves). The latter prevents serializing a
     * group into something like `/a(aux:)`, where `aux` is an empty child segment.
     */
    function squashSegmentGroup(segmentGroup) {
        var e_3, _a;
        var newChildren = {};
        try {
            for (var _b = __values(Object.keys(segmentGroup.children)), _c = _b.next(); !_c.done; _c = _b.next()) {
                var childOutlet = _c.value;
                var child = segmentGroup.children[childOutlet];
                var childCandidate = squashSegmentGroup(child);
                // don't add empty children
                if (childCandidate.segments.length > 0 || childCandidate.hasChildren()) {
                    newChildren[childOutlet] = childCandidate;
                }
            }
        }
        catch (e_3_1) { e_3 = { error: e_3_1 }; }
        finally {
            try {
                if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
            }
            finally { if (e_3) throw e_3.error; }
        }
        var s = new UrlSegmentGroup(segmentGroup.segments, newChildren);
        return mergeTrivialChildren(s);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function applyRedirects$1(moduleInjector, configLoader, urlSerializer, config) {
        return operators.switchMap(function (t) { return applyRedirects(moduleInjector, configLoader, urlSerializer, t.extractedUrl, config)
            .pipe(operators.map(function (urlAfterRedirects) { return (Object.assign(Object.assign({}, t), { urlAfterRedirects: urlAfterRedirects })); })); });
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var CanActivate = /** @class */ (function () {
        function CanActivate(path) {
            this.path = path;
            this.route = this.path[this.path.length - 1];
        }
        return CanActivate;
    }());
    var CanDeactivate = /** @class */ (function () {
        function CanDeactivate(component, route) {
            this.component = component;
            this.route = route;
        }
        return CanDeactivate;
    }());
    function getAllRouteGuards(future, curr, parentContexts) {
        var futureRoot = future._root;
        var currRoot = curr ? curr._root : null;
        return getChildRouteGuards(futureRoot, currRoot, parentContexts, [futureRoot.value]);
    }
    function getCanActivateChild(p) {
        var canActivateChild = p.routeConfig ? p.routeConfig.canActivateChild : null;
        if (!canActivateChild || canActivateChild.length === 0)
            return null;
        return { node: p, guards: canActivateChild };
    }
    function getToken(token, snapshot, moduleInjector) {
        var config = getClosestLoadedConfig(snapshot);
        var injector = config ? config.module.injector : moduleInjector;
        return injector.get(token);
    }
    function getClosestLoadedConfig(snapshot) {
        if (!snapshot)
            return null;
        for (var s = snapshot.parent; s; s = s.parent) {
            var route = s.routeConfig;
            if (route && route._loadedConfig)
                return route._loadedConfig;
        }
        return null;
    }
    function getChildRouteGuards(futureNode, currNode, contexts, futurePath, checks) {
        if (checks === void 0) { checks = {
            canDeactivateChecks: [],
            canActivateChecks: []
        }; }
        var prevChildren = nodeChildrenAsMap(currNode);
        // Process the children of the future route
        futureNode.children.forEach(function (c) {
            getRouteGuards(c, prevChildren[c.value.outlet], contexts, futurePath.concat([c.value]), checks);
            delete prevChildren[c.value.outlet];
        });
        // Process any children left from the current route (not active for the future route)
        forEach(prevChildren, function (v, k) { return deactivateRouteAndItsChildren(v, contexts.getContext(k), checks); });
        return checks;
    }
    function getRouteGuards(futureNode, currNode, parentContexts, futurePath, checks) {
        if (checks === void 0) { checks = {
            canDeactivateChecks: [],
            canActivateChecks: []
        }; }
        var future = futureNode.value;
        var curr = currNode ? currNode.value : null;
        var context = parentContexts ? parentContexts.getContext(futureNode.value.outlet) : null;
        // reusing the node
        if (curr && future.routeConfig === curr.routeConfig) {
            var shouldRun = shouldRunGuardsAndResolvers(curr, future, future.routeConfig.runGuardsAndResolvers);
            if (shouldRun) {
                checks.canActivateChecks.push(new CanActivate(futurePath));
            }
            else {
                // we need to set the data
                future.data = curr.data;
                future._resolvedData = curr._resolvedData;
            }
            // If we have a component, we need to go through an outlet.
            if (future.component) {
                getChildRouteGuards(futureNode, currNode, context ? context.children : null, futurePath, checks);
                // if we have a componentless route, we recurse but keep the same outlet map.
            }
            else {
                getChildRouteGuards(futureNode, currNode, parentContexts, futurePath, checks);
            }
            if (shouldRun && context && context.outlet && context.outlet.isActivated) {
                checks.canDeactivateChecks.push(new CanDeactivate(context.outlet.component, curr));
            }
        }
        else {
            if (curr) {
                deactivateRouteAndItsChildren(currNode, context, checks);
            }
            checks.canActivateChecks.push(new CanActivate(futurePath));
            // If we have a component, we need to go through an outlet.
            if (future.component) {
                getChildRouteGuards(futureNode, null, context ? context.children : null, futurePath, checks);
                // if we have a componentless route, we recurse but keep the same outlet map.
            }
            else {
                getChildRouteGuards(futureNode, null, parentContexts, futurePath, checks);
            }
        }
        return checks;
    }
    function shouldRunGuardsAndResolvers(curr, future, mode) {
        if (typeof mode === 'function') {
            return mode(curr, future);
        }
        switch (mode) {
            case 'pathParamsChange':
                return !equalPath(curr.url, future.url);
            case 'pathParamsOrQueryParamsChange':
                return !equalPath(curr.url, future.url) ||
                    !shallowEqual(curr.queryParams, future.queryParams);
            case 'always':
                return true;
            case 'paramsOrQueryParamsChange':
                return !equalParamsAndUrlSegments(curr, future) ||
                    !shallowEqual(curr.queryParams, future.queryParams);
            case 'paramsChange':
            default:
                return !equalParamsAndUrlSegments(curr, future);
        }
    }
    function deactivateRouteAndItsChildren(route, context, checks) {
        var children = nodeChildrenAsMap(route);
        var r = route.value;
        forEach(children, function (node, childName) {
            if (!r.component) {
                deactivateRouteAndItsChildren(node, context, checks);
            }
            else if (context) {
                deactivateRouteAndItsChildren(node, context.children.getContext(childName), checks);
            }
            else {
                deactivateRouteAndItsChildren(node, null, checks);
            }
        });
        if (!r.component) {
            checks.canDeactivateChecks.push(new CanDeactivate(null, r));
        }
        else if (context && context.outlet && context.outlet.isActivated) {
            checks.canDeactivateChecks.push(new CanDeactivate(context.outlet.component, r));
        }
        else {
            checks.canDeactivateChecks.push(new CanDeactivate(null, r));
        }
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function checkGuards(moduleInjector, forwardEvent) {
        return operators.mergeMap(function (t) {
            var targetSnapshot = t.targetSnapshot, currentSnapshot = t.currentSnapshot, _a = t.guards, canActivateChecks = _a.canActivateChecks, canDeactivateChecks = _a.canDeactivateChecks;
            if (canDeactivateChecks.length === 0 && canActivateChecks.length === 0) {
                return rxjs.of(Object.assign(Object.assign({}, t), { guardsResult: true }));
            }
            return runCanDeactivateChecks(canDeactivateChecks, targetSnapshot, currentSnapshot, moduleInjector)
                .pipe(operators.mergeMap(function (canDeactivate) {
                return canDeactivate && isBoolean(canDeactivate) ?
                    runCanActivateChecks(targetSnapshot, canActivateChecks, moduleInjector, forwardEvent) :
                    rxjs.of(canDeactivate);
            }), operators.map(function (guardsResult) { return (Object.assign(Object.assign({}, t), { guardsResult: guardsResult })); }));
        });
    }
    function runCanDeactivateChecks(checks, futureRSS, currRSS, moduleInjector) {
        return rxjs.from(checks).pipe(operators.mergeMap(function (check) { return runCanDeactivate(check.component, check.route, currRSS, futureRSS, moduleInjector); }), operators.first(function (result) {
            return result !== true;
        }, true));
    }
    function runCanActivateChecks(futureSnapshot, checks, moduleInjector, forwardEvent) {
        return rxjs.from(checks).pipe(operators.concatMap(function (check) {
            return rxjs.concat(fireChildActivationStart(check.route.parent, forwardEvent), fireActivationStart(check.route, forwardEvent), runCanActivateChild(futureSnapshot, check.path, moduleInjector), runCanActivate(futureSnapshot, check.route, moduleInjector));
        }), operators.first(function (result) {
            return result !== true;
        }, true));
    }
    /**
     * This should fire off `ActivationStart` events for each route being activated at this
     * level.
     * In other words, if you're activating `a` and `b` below, `path` will contain the
     * `ActivatedRouteSnapshot`s for both and we will fire `ActivationStart` for both. Always
     * return
     * `true` so checks continue to run.
     */
    function fireActivationStart(snapshot, forwardEvent) {
        if (snapshot !== null && forwardEvent) {
            forwardEvent(new ActivationStart(snapshot));
        }
        return rxjs.of(true);
    }
    /**
     * This should fire off `ChildActivationStart` events for each route being activated at this
     * level.
     * In other words, if you're activating `a` and `b` below, `path` will contain the
     * `ActivatedRouteSnapshot`s for both and we will fire `ChildActivationStart` for both. Always
     * return
     * `true` so checks continue to run.
     */
    function fireChildActivationStart(snapshot, forwardEvent) {
        if (snapshot !== null && forwardEvent) {
            forwardEvent(new ChildActivationStart(snapshot));
        }
        return rxjs.of(true);
    }
    function runCanActivate(futureRSS, futureARS, moduleInjector) {
        var canActivate = futureARS.routeConfig ? futureARS.routeConfig.canActivate : null;
        if (!canActivate || canActivate.length === 0)
            return rxjs.of(true);
        var canActivateObservables = canActivate.map(function (c) {
            return rxjs.defer(function () {
                var guard = getToken(c, futureARS, moduleInjector);
                var observable;
                if (isCanActivate(guard)) {
                    observable = wrapIntoObservable(guard.canActivate(futureARS, futureRSS));
                }
                else if (isFunction(guard)) {
                    observable = wrapIntoObservable(guard(futureARS, futureRSS));
                }
                else {
                    throw new Error('Invalid CanActivate guard');
                }
                return observable.pipe(operators.first());
            });
        });
        return rxjs.of(canActivateObservables).pipe(prioritizedGuardValue());
    }
    function runCanActivateChild(futureRSS, path, moduleInjector) {
        var futureARS = path[path.length - 1];
        var canActivateChildGuards = path.slice(0, path.length - 1)
            .reverse()
            .map(function (p) { return getCanActivateChild(p); })
            .filter(function (_) { return _ !== null; });
        var canActivateChildGuardsMapped = canActivateChildGuards.map(function (d) {
            return rxjs.defer(function () {
                var guardsMapped = d.guards.map(function (c) {
                    var guard = getToken(c, d.node, moduleInjector);
                    var observable;
                    if (isCanActivateChild(guard)) {
                        observable = wrapIntoObservable(guard.canActivateChild(futureARS, futureRSS));
                    }
                    else if (isFunction(guard)) {
                        observable = wrapIntoObservable(guard(futureARS, futureRSS));
                    }
                    else {
                        throw new Error('Invalid CanActivateChild guard');
                    }
                    return observable.pipe(operators.first());
                });
                return rxjs.of(guardsMapped).pipe(prioritizedGuardValue());
            });
        });
        return rxjs.of(canActivateChildGuardsMapped).pipe(prioritizedGuardValue());
    }
    function runCanDeactivate(component, currARS, currRSS, futureRSS, moduleInjector) {
        var canDeactivate = currARS && currARS.routeConfig ? currARS.routeConfig.canDeactivate : null;
        if (!canDeactivate || canDeactivate.length === 0)
            return rxjs.of(true);
        var canDeactivateObservables = canDeactivate.map(function (c) {
            var guard = getToken(c, currARS, moduleInjector);
            var observable;
            if (isCanDeactivate(guard)) {
                observable = wrapIntoObservable(guard.canDeactivate(component, currARS, currRSS, futureRSS));
            }
            else if (isFunction(guard)) {
                observable = wrapIntoObservable(guard(component, currARS, currRSS, futureRSS));
            }
            else {
                throw new Error('Invalid CanDeactivate guard');
            }
            return observable.pipe(operators.first());
        });
        return rxjs.of(canDeactivateObservables).pipe(prioritizedGuardValue());
    }

    var NoMatch$1 = /** @class */ (function () {
        function NoMatch() {
        }
        return NoMatch;
    }());
    function newObservableError(e) {
        // TODO(atscott): This pattern is used throughout the router code and can be `throwError` instead.
        return new rxjs.Observable(function (obs) { return obs.error(e); });
    }
    function recognize(rootComponentType, config, urlTree, url, paramsInheritanceStrategy, relativeLinkResolution) {
        if (paramsInheritanceStrategy === void 0) { paramsInheritanceStrategy = 'emptyOnly'; }
        if (relativeLinkResolution === void 0) { relativeLinkResolution = 'legacy'; }
        try {
            var result = new Recognizer(rootComponentType, config, urlTree, url, paramsInheritanceStrategy, relativeLinkResolution)
                .recognize();
            if (result === null) {
                return newObservableError(new NoMatch$1());
            }
            else {
                return rxjs.of(result);
            }
        }
        catch (e) {
            // Catch the potential error from recognize due to duplicate outlet matches and return as an
            // `Observable` error instead.
            return newObservableError(e);
        }
    }
    var Recognizer = /** @class */ (function () {
        function Recognizer(rootComponentType, config, urlTree, url, paramsInheritanceStrategy, relativeLinkResolution) {
            this.rootComponentType = rootComponentType;
            this.config = config;
            this.urlTree = urlTree;
            this.url = url;
            this.paramsInheritanceStrategy = paramsInheritanceStrategy;
            this.relativeLinkResolution = relativeLinkResolution;
        }
        Recognizer.prototype.recognize = function () {
            var rootSegmentGroup = split(this.urlTree.root, [], [], this.config.filter(function (c) { return c.redirectTo === undefined; }), this.relativeLinkResolution)
                .segmentGroup;
            var children = this.processSegmentGroup(this.config, rootSegmentGroup, PRIMARY_OUTLET);
            if (children === null) {
                return null;
            }
            // Use Object.freeze to prevent readers of the Router state from modifying it outside of a
            // navigation, resulting in the router being out of sync with the browser.
            var root = new ActivatedRouteSnapshot([], Object.freeze({}), Object.freeze(Object.assign({}, this.urlTree.queryParams)), this.urlTree.fragment, {}, PRIMARY_OUTLET, this.rootComponentType, null, this.urlTree.root, -1, {});
            var rootNode = new TreeNode(root, children);
            var routeState = new RouterStateSnapshot(this.url, rootNode);
            this.inheritParamsAndData(routeState._root);
            return routeState;
        };
        Recognizer.prototype.inheritParamsAndData = function (routeNode) {
            var _this = this;
            var route = routeNode.value;
            var i = inheritedParamsDataResolve(route, this.paramsInheritanceStrategy);
            route.params = Object.freeze(i.params);
            route.data = Object.freeze(i.data);
            routeNode.children.forEach(function (n) { return _this.inheritParamsAndData(n); });
        };
        Recognizer.prototype.processSegmentGroup = function (config, segmentGroup, outlet) {
            if (segmentGroup.segments.length === 0 && segmentGroup.hasChildren()) {
                return this.processChildren(config, segmentGroup);
            }
            return this.processSegment(config, segmentGroup, segmentGroup.segments, outlet);
        };
        /**
         * Matches every child outlet in the `segmentGroup` to a `Route` in the config. Returns `null` if
         * we cannot find a match for _any_ of the children.
         *
         * @param config - The `Routes` to match against
         * @param segmentGroup - The `UrlSegmentGroup` whose children need to be matched against the
         *     config.
         */
        Recognizer.prototype.processChildren = function (config, segmentGroup) {
            var e_1, _a;
            var children = [];
            try {
                for (var _b = __values(Object.keys(segmentGroup.children)), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var childOutlet = _c.value;
                    var child = segmentGroup.children[childOutlet];
                    // Sort the config so that routes with outlets that match the one being activated appear
                    // first, followed by routes for other outlets, which might match if they have an empty path.
                    var sortedConfig = sortByMatchingOutlets(config, childOutlet);
                    var outletChildren = this.processSegmentGroup(sortedConfig, child, childOutlet);
                    if (outletChildren === null) {
                        // Configs must match all segment children so because we did not find a match for this
                        // outlet, return `null`.
                        return null;
                    }
                    children.push.apply(children, __spread(outletChildren));
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            // Because we may have matched two outlets to the same empty path segment, we can have multiple
            // activated results for the same outlet. We should merge the children of these results so the
            // final return value is only one `TreeNode` per outlet.
            var mergedChildren = mergeEmptyPathMatches(children);
            if (typeof ngDevMode === 'undefined' || ngDevMode) {
                // This should really never happen - we are only taking the first match for each outlet and
                // merge the empty path matches.
                checkOutletNameUniqueness(mergedChildren);
            }
            sortActivatedRouteSnapshots(mergedChildren);
            return mergedChildren;
        };
        Recognizer.prototype.processSegment = function (config, segmentGroup, segments, outlet) {
            var e_2, _a;
            try {
                for (var config_1 = __values(config), config_1_1 = config_1.next(); !config_1_1.done; config_1_1 = config_1.next()) {
                    var r = config_1_1.value;
                    var children = this.processSegmentAgainstRoute(r, segmentGroup, segments, outlet);
                    if (children !== null) {
                        return children;
                    }
                }
            }
            catch (e_2_1) { e_2 = { error: e_2_1 }; }
            finally {
                try {
                    if (config_1_1 && !config_1_1.done && (_a = config_1.return)) _a.call(config_1);
                }
                finally { if (e_2) throw e_2.error; }
            }
            if (noLeftoversInUrl(segmentGroup, segments, outlet)) {
                return [];
            }
            return null;
        };
        Recognizer.prototype.processSegmentAgainstRoute = function (route, rawSegment, segments, outlet) {
            if (route.redirectTo || !isImmediateMatch(route, rawSegment, segments, outlet))
                return null;
            var snapshot;
            var consumedSegments = [];
            var rawSlicedSegments = [];
            if (route.path === '**') {
                var params = segments.length > 0 ? last(segments).parameters : {};
                snapshot = new ActivatedRouteSnapshot(segments, params, Object.freeze(Object.assign({}, this.urlTree.queryParams)), this.urlTree.fragment, getData(route), getOutlet(route), route.component, route, getSourceSegmentGroup(rawSegment), getPathIndexShift(rawSegment) + segments.length, getResolve(route));
            }
            else {
                var result = match(rawSegment, route, segments);
                if (!result.matched) {
                    return null;
                }
                consumedSegments = result.consumedSegments;
                rawSlicedSegments = segments.slice(result.lastChild);
                snapshot = new ActivatedRouteSnapshot(consumedSegments, result.parameters, Object.freeze(Object.assign({}, this.urlTree.queryParams)), this.urlTree.fragment, getData(route), getOutlet(route), route.component, route, getSourceSegmentGroup(rawSegment), getPathIndexShift(rawSegment) + consumedSegments.length, getResolve(route));
            }
            var childConfig = getChildConfig(route);
            var _a = split(rawSegment, consumedSegments, rawSlicedSegments, 
            // Filter out routes with redirectTo because we are trying to create activated route
            // snapshots and don't handle redirects here. That should have been done in
            // `applyRedirects`.
            childConfig.filter(function (c) { return c.redirectTo === undefined; }), this.relativeLinkResolution), segmentGroup = _a.segmentGroup, slicedSegments = _a.slicedSegments;
            if (slicedSegments.length === 0 && segmentGroup.hasChildren()) {
                var children_1 = this.processChildren(childConfig, segmentGroup);
                if (children_1 === null) {
                    return null;
                }
                return [new TreeNode(snapshot, children_1)];
            }
            if (childConfig.length === 0 && slicedSegments.length === 0) {
                return [new TreeNode(snapshot, [])];
            }
            var matchedOnOutlet = getOutlet(route) === outlet;
            // If we matched a config due to empty path match on a different outlet, we need to continue
            // passing the current outlet for the segment rather than switch to PRIMARY.
            // Note that we switch to primary when we have a match because outlet configs look like this:
            // {path: 'a', outlet: 'a', children: [
            //  {path: 'b', component: B},
            //  {path: 'c', component: C},
            // ]}
            // Notice that the children of the named outlet are configured with the primary outlet
            var children = this.processSegment(childConfig, segmentGroup, slicedSegments, matchedOnOutlet ? PRIMARY_OUTLET : outlet);
            if (children === null) {
                return null;
            }
            return [new TreeNode(snapshot, children)];
        };
        return Recognizer;
    }());
    function sortActivatedRouteSnapshots(nodes) {
        nodes.sort(function (a, b) {
            if (a.value.outlet === PRIMARY_OUTLET)
                return -1;
            if (b.value.outlet === PRIMARY_OUTLET)
                return 1;
            return a.value.outlet.localeCompare(b.value.outlet);
        });
    }
    function getChildConfig(route) {
        if (route.children) {
            return route.children;
        }
        if (route.loadChildren) {
            return route._loadedConfig.routes;
        }
        return [];
    }
    function hasEmptyPathConfig(node) {
        var config = node.value.routeConfig;
        return config && config.path === '' && config.redirectTo === undefined;
    }
    /**
     * Finds `TreeNode`s with matching empty path route configs and merges them into `TreeNode` with the
     * children from each duplicate. This is necessary because different outlets can match a single
     * empty path route config and the results need to then be merged.
     */
    function mergeEmptyPathMatches(nodes) {
        var e_3, _a;
        var result = [];
        var _loop_1 = function (node) {
            var _a;
            if (!hasEmptyPathConfig(node)) {
                result.push(node);
                return "continue";
            }
            var duplicateEmptyPathNode = result.find(function (resultNode) { return node.value.routeConfig === resultNode.value.routeConfig; });
            if (duplicateEmptyPathNode !== undefined) {
                (_a = duplicateEmptyPathNode.children).push.apply(_a, __spread(node.children));
            }
            else {
                result.push(node);
            }
        };
        try {
            for (var nodes_1 = __values(nodes), nodes_1_1 = nodes_1.next(); !nodes_1_1.done; nodes_1_1 = nodes_1.next()) {
                var node = nodes_1_1.value;
                _loop_1(node);
            }
        }
        catch (e_3_1) { e_3 = { error: e_3_1 }; }
        finally {
            try {
                if (nodes_1_1 && !nodes_1_1.done && (_a = nodes_1.return)) _a.call(nodes_1);
            }
            finally { if (e_3) throw e_3.error; }
        }
        return result;
    }
    function checkOutletNameUniqueness(nodes) {
        var names = {};
        nodes.forEach(function (n) {
            var routeWithSameOutletName = names[n.value.outlet];
            if (routeWithSameOutletName) {
                var p = routeWithSameOutletName.url.map(function (s) { return s.toString(); }).join('/');
                var c = n.value.url.map(function (s) { return s.toString(); }).join('/');
                throw new Error("Two segments cannot have the same outlet name: '" + p + "' and '" + c + "'.");
            }
            names[n.value.outlet] = n.value;
        });
    }
    function getSourceSegmentGroup(segmentGroup) {
        var s = segmentGroup;
        while (s._sourceSegment) {
            s = s._sourceSegment;
        }
        return s;
    }
    function getPathIndexShift(segmentGroup) {
        var s = segmentGroup;
        var res = (s._segmentIndexShift ? s._segmentIndexShift : 0);
        while (s._sourceSegment) {
            s = s._sourceSegment;
            res += (s._segmentIndexShift ? s._segmentIndexShift : 0);
        }
        return res - 1;
    }
    function getData(route) {
        return route.data || {};
    }
    function getResolve(route) {
        return route.resolve || {};
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function recognize$1(rootComponentType, config, serializer, paramsInheritanceStrategy, relativeLinkResolution) {
        return operators.mergeMap(function (t) { return recognize(rootComponentType, config, t.urlAfterRedirects, serializer(t.urlAfterRedirects), paramsInheritanceStrategy, relativeLinkResolution)
            .pipe(operators.map(function (targetSnapshot) { return (Object.assign(Object.assign({}, t), { targetSnapshot: targetSnapshot })); })); });
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function resolveData(paramsInheritanceStrategy, moduleInjector) {
        return operators.mergeMap(function (t) {
            var targetSnapshot = t.targetSnapshot, canActivateChecks = t.guards.canActivateChecks;
            if (!canActivateChecks.length) {
                return rxjs.of(t);
            }
            var canActivateChecksResolved = 0;
            return rxjs.from(canActivateChecks)
                .pipe(operators.concatMap(function (check) { return runResolve(check.route, targetSnapshot, paramsInheritanceStrategy, moduleInjector); }), operators.tap(function () { return canActivateChecksResolved++; }), operators.takeLast(1), operators.mergeMap(function (_) { return canActivateChecksResolved === canActivateChecks.length ? rxjs.of(t) : rxjs.EMPTY; }));
        });
    }
    function runResolve(futureARS, futureRSS, paramsInheritanceStrategy, moduleInjector) {
        var resolve = futureARS._resolve;
        return resolveNode(resolve, futureARS, futureRSS, moduleInjector)
            .pipe(operators.map(function (resolvedData) {
            futureARS._resolvedData = resolvedData;
            futureARS.data = Object.assign(Object.assign({}, futureARS.data), inheritedParamsDataResolve(futureARS, paramsInheritanceStrategy).resolve);
            return null;
        }));
    }
    function resolveNode(resolve, futureARS, futureRSS, moduleInjector) {
        var keys = Object.keys(resolve);
        if (keys.length === 0) {
            return rxjs.of({});
        }
        var data = {};
        return rxjs.from(keys).pipe(operators.mergeMap(function (key) { return getResolver(resolve[key], futureARS, futureRSS, moduleInjector)
            .pipe(operators.tap(function (value) {
            data[key] = value;
        })); }), operators.takeLast(1), operators.mergeMap(function () {
            // Ensure all resolvers returned values, otherwise don't emit any "next" and just complete
            // the chain which will cancel navigation
            if (Object.keys(data).length === keys.length) {
                return rxjs.of(data);
            }
            return rxjs.EMPTY;
        }));
    }
    function getResolver(injectionToken, futureARS, futureRSS, moduleInjector) {
        var resolver = getToken(injectionToken, futureARS, moduleInjector);
        return resolver.resolve ? wrapIntoObservable(resolver.resolve(futureARS, futureRSS)) :
            wrapIntoObservable(resolver(futureARS, futureRSS));
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Perform a side effect through a switchMap for every emission on the source Observable,
     * but return an Observable that is identical to the source. It's essentially the same as
     * the `tap` operator, but if the side effectful `next` function returns an ObservableInput,
     * it will wait before continuing with the original value.
     */
    function switchTap(next) {
        return operators.switchMap(function (v) {
            var nextResult = next(v);
            if (nextResult) {
                return rxjs.from(nextResult).pipe(operators.map(function () { return v; }));
            }
            return rxjs.of(v);
        });
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @description
     *
     * Provides a way to customize when activated routes get reused.
     *
     * @publicApi
     */
    var RouteReuseStrategy = /** @class */ (function () {
        function RouteReuseStrategy() {
        }
        return RouteReuseStrategy;
    }());
    /**
     * @description
     *
     * This base route reuse strategy only reuses routes when the matched router configs are
     * identical. This prevents components from being destroyed and recreated
     * when just the fragment or query parameters change
     * (that is, the existing component is _reused_).
     *
     * This strategy does not store any routes for later reuse.
     *
     * Angular uses this strategy by default.
     *
     *
     * It can be used as a base class for custom route reuse strategies, i.e. you can create your own
     * class that extends the `BaseRouteReuseStrategy` one.
     * @publicApi
     */
    var BaseRouteReuseStrategy = /** @class */ (function () {
        function BaseRouteReuseStrategy() {
        }
        /**
         * Whether the given route should detach for later reuse.
         * Always returns false for `BaseRouteReuseStrategy`.
         * */
        BaseRouteReuseStrategy.prototype.shouldDetach = function (route) {
            return false;
        };
        /**
         * A no-op; the route is never stored since this strategy never detaches routes for later re-use.
         */
        BaseRouteReuseStrategy.prototype.store = function (route, detachedTree) { };
        /** Returns `false`, meaning the route (and its subtree) is never reattached */
        BaseRouteReuseStrategy.prototype.shouldAttach = function (route) {
            return false;
        };
        /** Returns `null` because this strategy does not store routes for later re-use. */
        BaseRouteReuseStrategy.prototype.retrieve = function (route) {
            return null;
        };
        /**
         * Determines if a route should be reused.
         * This strategy returns `true` when the future route config and current route config are
         * identical.
         */
        BaseRouteReuseStrategy.prototype.shouldReuseRoute = function (future, curr) {
            return future.routeConfig === curr.routeConfig;
        };
        return BaseRouteReuseStrategy;
    }());
    var DefaultRouteReuseStrategy = /** @class */ (function (_super) {
        __extends(DefaultRouteReuseStrategy, _super);
        function DefaultRouteReuseStrategy() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        return DefaultRouteReuseStrategy;
    }(BaseRouteReuseStrategy));

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * The [DI token](guide/glossary/#di-token) for a router configuration.
     * @see `ROUTES`
     * @publicApi
     */
    var ROUTES = new core.InjectionToken('ROUTES');
    var RouterConfigLoader = /** @class */ (function () {
        function RouterConfigLoader(loader, compiler, onLoadStartListener, onLoadEndListener) {
            this.loader = loader;
            this.compiler = compiler;
            this.onLoadStartListener = onLoadStartListener;
            this.onLoadEndListener = onLoadEndListener;
        }
        RouterConfigLoader.prototype.load = function (parentInjector, route) {
            var _this = this;
            if (route._loader$) {
                return route._loader$;
            }
            if (this.onLoadStartListener) {
                this.onLoadStartListener(route);
            }
            var moduleFactory$ = this.loadModuleFactory(route.loadChildren);
            var loadRunner = moduleFactory$.pipe(operators.map(function (factory) {
                if (_this.onLoadEndListener) {
                    _this.onLoadEndListener(route);
                }
                var module = factory.create(parentInjector);
                // When loading a module that doesn't provide `RouterModule.forChild()` preloader
                // will get stuck in an infinite loop. The child module's Injector will look to
                // its parent `Injector` when it doesn't find any ROUTES so it will return routes
                // for it's parent module instead.
                return new LoadedRouterConfig(flatten(module.injector.get(ROUTES, undefined, core.InjectFlags.Self | core.InjectFlags.Optional))
                    .map(standardizeConfig), module);
            }), operators.catchError(function (err) {
                route._loader$ = undefined;
                throw err;
            }));
            // Use custom ConnectableObservable as share in runners pipe increasing the bundle size too much
            route._loader$ = new rxjs.ConnectableObservable(loadRunner, function () { return new rxjs.Subject(); })
                .pipe(operators.refCount());
            return route._loader$;
        };
        RouterConfigLoader.prototype.loadModuleFactory = function (loadChildren) {
            var _this = this;
            if (typeof loadChildren === 'string') {
                return rxjs.from(this.loader.load(loadChildren));
            }
            else {
                return wrapIntoObservable(loadChildren()).pipe(operators.mergeMap(function (t) {
                    if (t instanceof core.NgModuleFactory) {
                        return rxjs.of(t);
                    }
                    else {
                        return rxjs.from(_this.compiler.compileModuleAsync(t));
                    }
                }));
            }
        };
        return RouterConfigLoader;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Store contextual information about a `RouterOutlet`
     *
     * @publicApi
     */
    var OutletContext = /** @class */ (function () {
        function OutletContext() {
            this.outlet = null;
            this.route = null;
            this.resolver = null;
            this.children = new ChildrenOutletContexts();
            this.attachRef = null;
        }
        return OutletContext;
    }());
    /**
     * Store contextual information about the children (= nested) `RouterOutlet`
     *
     * @publicApi
     */
    var ChildrenOutletContexts = /** @class */ (function () {
        function ChildrenOutletContexts() {
            // contexts for child outlets, by name.
            this.contexts = new Map();
        }
        /** Called when a `RouterOutlet` directive is instantiated */
        ChildrenOutletContexts.prototype.onChildOutletCreated = function (childName, outlet) {
            var context = this.getOrCreateContext(childName);
            context.outlet = outlet;
            this.contexts.set(childName, context);
        };
        /**
         * Called when a `RouterOutlet` directive is destroyed.
         * We need to keep the context as the outlet could be destroyed inside a NgIf and might be
         * re-created later.
         */
        ChildrenOutletContexts.prototype.onChildOutletDestroyed = function (childName) {
            var context = this.getContext(childName);
            if (context) {
                context.outlet = null;
            }
        };
        /**
         * Called when the corresponding route is deactivated during navigation.
         * Because the component get destroyed, all children outlet are destroyed.
         */
        ChildrenOutletContexts.prototype.onOutletDeactivated = function () {
            var contexts = this.contexts;
            this.contexts = new Map();
            return contexts;
        };
        ChildrenOutletContexts.prototype.onOutletReAttached = function (contexts) {
            this.contexts = contexts;
        };
        ChildrenOutletContexts.prototype.getOrCreateContext = function (childName) {
            var context = this.getContext(childName);
            if (!context) {
                context = new OutletContext();
                this.contexts.set(childName, context);
            }
            return context;
        };
        ChildrenOutletContexts.prototype.getContext = function (childName) {
            return this.contexts.get(childName) || null;
        };
        return ChildrenOutletContexts;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @description
     *
     * Provides a way to migrate AngularJS applications to Angular.
     *
     * @publicApi
     */
    var UrlHandlingStrategy = /** @class */ (function () {
        function UrlHandlingStrategy() {
        }
        return UrlHandlingStrategy;
    }());
    /**
     * @publicApi
     */
    var DefaultUrlHandlingStrategy = /** @class */ (function () {
        function DefaultUrlHandlingStrategy() {
        }
        DefaultUrlHandlingStrategy.prototype.shouldProcessUrl = function (url) {
            return true;
        };
        DefaultUrlHandlingStrategy.prototype.extract = function (url) {
            return url;
        };
        DefaultUrlHandlingStrategy.prototype.merge = function (newUrlPart, wholeUrl) {
            return newUrlPart;
        };
        return DefaultUrlHandlingStrategy;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function defaultErrorHandler(error) {
        throw error;
    }
    function defaultMalformedUriErrorHandler(error, urlSerializer, url) {
        return urlSerializer.parse('/');
    }
    /**
     * @internal
     */
    function defaultRouterHook(snapshot, runExtras) {
        return rxjs.of(null);
    }
    /**
     * @description
     *
     * A service that provides navigation among views and URL manipulation capabilities.
     *
     * @see `Route`.
     * @see [Routing and Navigation Guide](guide/router).
     *
     * @ngModule RouterModule
     *
     * @publicApi
     */
    var Router = /** @class */ (function () {
        /**
         * Creates the router service.
         */
        // TODO: vsavkin make internal after the final is out.
        function Router(rootComponentType, urlSerializer, rootContexts, location, injector, loader, compiler, config) {
            var _this = this;
            this.rootComponentType = rootComponentType;
            this.urlSerializer = urlSerializer;
            this.rootContexts = rootContexts;
            this.location = location;
            this.config = config;
            this.lastSuccessfulNavigation = null;
            this.currentNavigation = null;
            this.disposed = false;
            /**
             * Tracks the previously seen location change from the location subscription so we can compare
             * the two latest to see if they are duplicates. See setUpLocationChangeListener.
             */
            this.lastLocationChangeInfo = null;
            this.navigationId = 0;
            this.isNgZoneEnabled = false;
            /**
             * An event stream for routing events in this NgModule.
             */
            this.events = new rxjs.Subject();
            /**
             * A handler for navigation errors in this NgModule.
             */
            this.errorHandler = defaultErrorHandler;
            /**
             * A handler for errors thrown by `Router.parseUrl(url)`
             * when `url` contains an invalid character.
             * The most common case is a `%` sign
             * that's not encoded and is not part of a percent encoded sequence.
             */
            this.malformedUriErrorHandler = defaultMalformedUriErrorHandler;
            /**
             * True if at least one navigation event has occurred,
             * false otherwise.
             */
            this.navigated = false;
            this.lastSuccessfulId = -1;
            /**
             * Hooks that enable you to pause navigation,
             * either before or after the preactivation phase.
             * Used by `RouterModule`.
             *
             * @internal
             */
            this.hooks = { beforePreactivation: defaultRouterHook, afterPreactivation: defaultRouterHook };
            /**
             * A strategy for extracting and merging URLs.
             * Used for AngularJS to Angular migrations.
             */
            this.urlHandlingStrategy = new DefaultUrlHandlingStrategy();
            /**
             * A strategy for re-using routes.
             */
            this.routeReuseStrategy = new DefaultRouteReuseStrategy();
            /**
             * How to handle a navigation request to the current URL. One of:
             * - `'ignore'` :  The router ignores the request.
             * - `'reload'` : The router reloads the URL. Use to implement a "refresh" feature.
             */
            this.onSameUrlNavigation = 'ignore';
            /**
             * How to merge parameters, data, and resolved data from parent to child
             * routes. One of:
             *
             * - `'emptyOnly'` : Inherit parent parameters, data, and resolved data
             * for path-less or component-less routes.
             * - `'always'` : Inherit parent parameters, data, and resolved data
             * for all child routes.
             */
            this.paramsInheritanceStrategy = 'emptyOnly';
            /**
             * Determines when the router updates the browser URL.
             * By default (`"deferred"`), updates the browser URL after navigation has finished.
             * Set to `'eager'` to update the browser URL at the beginning of navigation.
             * You can choose to update early so that, if navigation fails,
             * you can show an error message with the URL that failed.
             */
            this.urlUpdateStrategy = 'deferred';
            /**
             * Enables a bug fix that corrects relative link resolution in components with empty paths.
             * @see `RouterModule`
             */
            this.relativeLinkResolution = 'corrected';
            var onLoadStart = function (r) { return _this.triggerEvent(new RouteConfigLoadStart(r)); };
            var onLoadEnd = function (r) { return _this.triggerEvent(new RouteConfigLoadEnd(r)); };
            this.ngModule = injector.get(core.NgModuleRef);
            this.console = injector.get(core.ɵConsole);
            var ngZone = injector.get(core.NgZone);
            this.isNgZoneEnabled = ngZone instanceof core.NgZone && core.NgZone.isInAngularZone();
            this.resetConfig(config);
            this.currentUrlTree = createEmptyUrlTree();
            this.rawUrlTree = this.currentUrlTree;
            this.browserUrlTree = this.currentUrlTree;
            this.configLoader = new RouterConfigLoader(loader, compiler, onLoadStart, onLoadEnd);
            this.routerState = createEmptyState(this.currentUrlTree, this.rootComponentType);
            this.transitions = new rxjs.BehaviorSubject({
                id: 0,
                currentUrlTree: this.currentUrlTree,
                currentRawUrl: this.currentUrlTree,
                extractedUrl: this.urlHandlingStrategy.extract(this.currentUrlTree),
                urlAfterRedirects: this.urlHandlingStrategy.extract(this.currentUrlTree),
                rawUrl: this.currentUrlTree,
                extras: {},
                resolve: null,
                reject: null,
                promise: Promise.resolve(true),
                source: 'imperative',
                restoredState: null,
                currentSnapshot: this.routerState.snapshot,
                targetSnapshot: null,
                currentRouterState: this.routerState,
                targetRouterState: null,
                guards: { canActivateChecks: [], canDeactivateChecks: [] },
                guardsResult: null,
            });
            this.navigations = this.setupNavigations(this.transitions);
            this.processNavigations();
        }
        Router.prototype.setupNavigations = function (transitions) {
            var _this = this;
            var eventsSubject = this.events;
            return transitions.pipe(operators.filter(function (t) { return t.id !== 0; }), 
            // Extract URL
            operators.map(function (t) { return (Object.assign(Object.assign({}, t), { extractedUrl: _this.urlHandlingStrategy.extract(t.rawUrl) })); }), 
            // Using switchMap so we cancel executing navigations when a new one comes in
            operators.switchMap(function (t) {
                var completed = false;
                var errored = false;
                return rxjs.of(t).pipe(
                // Store the Navigation object
                operators.tap(function (t) {
                    _this.currentNavigation = {
                        id: t.id,
                        initialUrl: t.currentRawUrl,
                        extractedUrl: t.extractedUrl,
                        trigger: t.source,
                        extras: t.extras,
                        previousNavigation: _this.lastSuccessfulNavigation ? Object.assign(Object.assign({}, _this.lastSuccessfulNavigation), { previousNavigation: null }) :
                            null
                    };
                }), operators.switchMap(function (t) {
                    var urlTransition = !_this.navigated ||
                        t.extractedUrl.toString() !== _this.browserUrlTree.toString();
                    var processCurrentUrl = (_this.onSameUrlNavigation === 'reload' ? true : urlTransition) &&
                        _this.urlHandlingStrategy.shouldProcessUrl(t.rawUrl);
                    if (processCurrentUrl) {
                        return rxjs.of(t).pipe(
                        // Fire NavigationStart event
                        operators.switchMap(function (t) {
                            var transition = _this.transitions.getValue();
                            eventsSubject.next(new NavigationStart(t.id, _this.serializeUrl(t.extractedUrl), t.source, t.restoredState));
                            if (transition !== _this.transitions.getValue()) {
                                return rxjs.EMPTY;
                            }
                            // This delay is required to match old behavior that forced
                            // navigation to always be async
                            return Promise.resolve(t);
                        }), 
                        // ApplyRedirects
                        applyRedirects$1(_this.ngModule.injector, _this.configLoader, _this.urlSerializer, _this.config), 
                        // Update the currentNavigation
                        operators.tap(function (t) {
                            _this.currentNavigation = Object.assign(Object.assign({}, _this.currentNavigation), { finalUrl: t.urlAfterRedirects });
                        }), 
                        // Recognize
                        recognize$1(_this.rootComponentType, _this.config, function (url) { return _this.serializeUrl(url); }, _this.paramsInheritanceStrategy, _this.relativeLinkResolution), 
                        // Update URL if in `eager` update mode
                        operators.tap(function (t) {
                            if (_this.urlUpdateStrategy === 'eager') {
                                if (!t.extras.skipLocationChange) {
                                    _this.setBrowserUrl(t.urlAfterRedirects, !!t.extras.replaceUrl, t.id, t.extras.state);
                                }
                                _this.browserUrlTree = t.urlAfterRedirects;
                            }
                            // Fire RoutesRecognized
                            var routesRecognized = new RoutesRecognized(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(t.urlAfterRedirects), t.targetSnapshot);
                            eventsSubject.next(routesRecognized);
                        }));
                    }
                    else {
                        var processPreviousUrl = urlTransition && _this.rawUrlTree &&
                            _this.urlHandlingStrategy.shouldProcessUrl(_this.rawUrlTree);
                        /* When the current URL shouldn't be processed, but the previous one was,
                         * we handle this "error condition" by navigating to the previously
                         * successful URL, but leaving the URL intact.*/
                        if (processPreviousUrl) {
                            var id = t.id, extractedUrl = t.extractedUrl, source = t.source, restoredState = t.restoredState, extras = t.extras;
                            var navStart = new NavigationStart(id, _this.serializeUrl(extractedUrl), source, restoredState);
                            eventsSubject.next(navStart);
                            var targetSnapshot = createEmptyState(extractedUrl, _this.rootComponentType).snapshot;
                            return rxjs.of(Object.assign(Object.assign({}, t), { targetSnapshot: targetSnapshot, urlAfterRedirects: extractedUrl, extras: Object.assign(Object.assign({}, extras), { skipLocationChange: false, replaceUrl: false }) }));
                        }
                        else {
                            /* When neither the current or previous URL can be processed, do nothing
                             * other than update router's internal reference to the current "settled"
                             * URL. This way the next navigation will be coming from the current URL
                             * in the browser.
                             */
                            _this.rawUrlTree = t.rawUrl;
                            _this.browserUrlTree = t.urlAfterRedirects;
                            t.resolve(null);
                            return rxjs.EMPTY;
                        }
                    }
                }), 
                // Before Preactivation
                switchTap(function (t) {
                    var targetSnapshot = t.targetSnapshot, navigationId = t.id, appliedUrlTree = t.extractedUrl, rawUrlTree = t.rawUrl, _b = t.extras, skipLocationChange = _b.skipLocationChange, replaceUrl = _b.replaceUrl;
                    return _this.hooks.beforePreactivation(targetSnapshot, {
                        navigationId: navigationId,
                        appliedUrlTree: appliedUrlTree,
                        rawUrlTree: rawUrlTree,
                        skipLocationChange: !!skipLocationChange,
                        replaceUrl: !!replaceUrl,
                    });
                }), 
                // --- GUARDS ---
                operators.tap(function (t) {
                    var guardsStart = new GuardsCheckStart(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(t.urlAfterRedirects), t.targetSnapshot);
                    _this.triggerEvent(guardsStart);
                }), operators.map(function (t) { return (Object.assign(Object.assign({}, t), { guards: getAllRouteGuards(t.targetSnapshot, t.currentSnapshot, _this.rootContexts) })); }), checkGuards(_this.ngModule.injector, function (evt) { return _this.triggerEvent(evt); }), operators.tap(function (t) {
                    if (isUrlTree(t.guardsResult)) {
                        var error = navigationCancelingError("Redirecting to \"" + _this.serializeUrl(t.guardsResult) + "\"");
                        error.url = t.guardsResult;
                        throw error;
                    }
                    var guardsEnd = new GuardsCheckEnd(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(t.urlAfterRedirects), t.targetSnapshot, !!t.guardsResult);
                    _this.triggerEvent(guardsEnd);
                }), operators.filter(function (t) {
                    if (!t.guardsResult) {
                        _this.resetUrlToCurrentUrlTree();
                        var navCancel = new NavigationCancel(t.id, _this.serializeUrl(t.extractedUrl), '');
                        eventsSubject.next(navCancel);
                        t.resolve(false);
                        return false;
                    }
                    return true;
                }), 
                // --- RESOLVE ---
                switchTap(function (t) {
                    if (t.guards.canActivateChecks.length) {
                        return rxjs.of(t).pipe(operators.tap(function (t) {
                            var resolveStart = new ResolveStart(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(t.urlAfterRedirects), t.targetSnapshot);
                            _this.triggerEvent(resolveStart);
                        }), operators.switchMap(function (t) {
                            var dataResolved = false;
                            return rxjs.of(t).pipe(resolveData(_this.paramsInheritanceStrategy, _this.ngModule.injector), operators.tap({
                                next: function () { return dataResolved = true; },
                                complete: function () {
                                    if (!dataResolved) {
                                        var navCancel = new NavigationCancel(t.id, _this.serializeUrl(t.extractedUrl), "At least one route resolver didn't emit any value.");
                                        eventsSubject.next(navCancel);
                                        t.resolve(false);
                                    }
                                }
                            }));
                        }), operators.tap(function (t) {
                            var resolveEnd = new ResolveEnd(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(t.urlAfterRedirects), t.targetSnapshot);
                            _this.triggerEvent(resolveEnd);
                        }));
                    }
                    return undefined;
                }), 
                // --- AFTER PREACTIVATION ---
                switchTap(function (t) {
                    var targetSnapshot = t.targetSnapshot, navigationId = t.id, appliedUrlTree = t.extractedUrl, rawUrlTree = t.rawUrl, _b = t.extras, skipLocationChange = _b.skipLocationChange, replaceUrl = _b.replaceUrl;
                    return _this.hooks.afterPreactivation(targetSnapshot, {
                        navigationId: navigationId,
                        appliedUrlTree: appliedUrlTree,
                        rawUrlTree: rawUrlTree,
                        skipLocationChange: !!skipLocationChange,
                        replaceUrl: !!replaceUrl,
                    });
                }), operators.map(function (t) {
                    var targetRouterState = createRouterState(_this.routeReuseStrategy, t.targetSnapshot, t.currentRouterState);
                    return (Object.assign(Object.assign({}, t), { targetRouterState: targetRouterState }));
                }), 
                /* Once here, we are about to activate syncronously. The assumption is this
                   will succeed, and user code may read from the Router service. Therefore
                   before activation, we need to update router properties storing the current
                   URL and the RouterState, as well as updated the browser URL. All this should
                   happen *before* activating. */
                operators.tap(function (t) {
                    _this.currentUrlTree = t.urlAfterRedirects;
                    _this.rawUrlTree =
                        _this.urlHandlingStrategy.merge(_this.currentUrlTree, t.rawUrl);
                    _this.routerState = t.targetRouterState;
                    if (_this.urlUpdateStrategy === 'deferred') {
                        if (!t.extras.skipLocationChange) {
                            _this.setBrowserUrl(_this.rawUrlTree, !!t.extras.replaceUrl, t.id, t.extras.state);
                        }
                        _this.browserUrlTree = t.urlAfterRedirects;
                    }
                }), activateRoutes(_this.rootContexts, _this.routeReuseStrategy, function (evt) { return _this.triggerEvent(evt); }), operators.tap({
                    next: function () {
                        completed = true;
                    },
                    complete: function () {
                        completed = true;
                    }
                }), operators.finalize(function () {
                    /* When the navigation stream finishes either through error or success, we
                     * set the `completed` or `errored` flag. However, there are some situations
                     * where we could get here without either of those being set. For instance, a
                     * redirect during NavigationStart. Therefore, this is a catch-all to make
                     * sure the NavigationCancel
                     * event is fired when a navigation gets cancelled but not caught by other
                     * means. */
                    if (!completed && !errored) {
                        // Must reset to current URL tree here to ensure history.state is set. On a
                        // fresh page load, if a new navigation comes in before a successful
                        // navigation completes, there will be nothing in
                        // history.state.navigationId. This can cause sync problems with AngularJS
                        // sync code which looks for a value here in order to determine whether or
                        // not to handle a given popstate event or to leave it to the Angular
                        // router.
                        _this.resetUrlToCurrentUrlTree();
                        var navCancel = new NavigationCancel(t.id, _this.serializeUrl(t.extractedUrl), "Navigation ID " + t.id + " is not equal to the current navigation id " + _this.navigationId);
                        eventsSubject.next(navCancel);
                        t.resolve(false);
                    }
                    // currentNavigation should always be reset to null here. If navigation was
                    // successful, lastSuccessfulTransition will have already been set. Therefore
                    // we can safely set currentNavigation to null here.
                    _this.currentNavigation = null;
                }), operators.catchError(function (e) {
                    errored = true;
                    /* This error type is issued during Redirect, and is handled as a
                     * cancellation rather than an error. */
                    if (isNavigationCancelingError(e)) {
                        var redirecting = isUrlTree(e.url);
                        if (!redirecting) {
                            // Set property only if we're not redirecting. If we landed on a page and
                            // redirect to `/` route, the new navigation is going to see the `/`
                            // isn't a change from the default currentUrlTree and won't navigate.
                            // This is only applicable with initial navigation, so setting
                            // `navigated` only when not redirecting resolves this scenario.
                            _this.navigated = true;
                            _this.resetStateAndUrl(t.currentRouterState, t.currentUrlTree, t.rawUrl);
                        }
                        var navCancel = new NavigationCancel(t.id, _this.serializeUrl(t.extractedUrl), e.message);
                        eventsSubject.next(navCancel);
                        // When redirecting, we need to delay resolving the navigation
                        // promise and push it to the redirect navigation
                        if (!redirecting) {
                            t.resolve(false);
                        }
                        else {
                            // setTimeout is required so this navigation finishes with
                            // the return EMPTY below. If it isn't allowed to finish
                            // processing, there can be multiple navigations to the same
                            // URL.
                            setTimeout(function () {
                                var mergedTree = _this.urlHandlingStrategy.merge(e.url, _this.rawUrlTree);
                                var extras = {
                                    skipLocationChange: t.extras.skipLocationChange,
                                    replaceUrl: _this.urlUpdateStrategy === 'eager'
                                };
                                _this.scheduleNavigation(mergedTree, 'imperative', null, extras, { resolve: t.resolve, reject: t.reject, promise: t.promise });
                            }, 0);
                        }
                        /* All other errors should reset to the router's internal URL reference to
                         * the pre-error state. */
                    }
                    else {
                        _this.resetStateAndUrl(t.currentRouterState, t.currentUrlTree, t.rawUrl);
                        var navError = new NavigationError(t.id, _this.serializeUrl(t.extractedUrl), e);
                        eventsSubject.next(navError);
                        try {
                            t.resolve(_this.errorHandler(e));
                        }
                        catch (ee) {
                            t.reject(ee);
                        }
                    }
                    return rxjs.EMPTY;
                }));
                // TODO(jasonaden): remove cast once g3 is on updated TypeScript
            }));
        };
        /**
         * @internal
         * TODO: this should be removed once the constructor of the router made internal
         */
        Router.prototype.resetRootComponentType = function (rootComponentType) {
            this.rootComponentType = rootComponentType;
            // TODO: vsavkin router 4.0 should make the root component set to null
            // this will simplify the lifecycle of the router.
            this.routerState.root.component = this.rootComponentType;
        };
        Router.prototype.getTransition = function () {
            var transition = this.transitions.value;
            // This value needs to be set. Other values such as extractedUrl are set on initial navigation
            // but the urlAfterRedirects may not get set if we aren't processing the new URL *and* not
            // processing the previous URL.
            transition.urlAfterRedirects = this.browserUrlTree;
            return transition;
        };
        Router.prototype.setTransition = function (t) {
            this.transitions.next(Object.assign(Object.assign({}, this.getTransition()), t));
        };
        /**
         * Sets up the location change listener and performs the initial navigation.
         */
        Router.prototype.initialNavigation = function () {
            this.setUpLocationChangeListener();
            if (this.navigationId === 0) {
                this.navigateByUrl(this.location.path(true), { replaceUrl: true });
            }
        };
        /**
         * Sets up the location change listener. This listener detects navigations triggered from outside
         * the Router (the browser back/forward buttons, for example) and schedules a corresponding Router
         * navigation so that the correct events, guards, etc. are triggered.
         */
        Router.prototype.setUpLocationChangeListener = function () {
            var _this = this;
            // Don't need to use Zone.wrap any more, because zone.js
            // already patch onPopState, so location change callback will
            // run into ngZone
            if (!this.locationSubscription) {
                this.locationSubscription = this.location.subscribe(function (event) {
                    var currentChange = _this.extractLocationChangeInfoFromEvent(event);
                    if (_this.shouldScheduleNavigation(_this.lastLocationChangeInfo, currentChange)) {
                        // The `setTimeout` was added in #12160 and is likely to support Angular/AngularJS
                        // hybrid apps.
                        setTimeout(function () {
                            var source = currentChange.source, state = currentChange.state, urlTree = currentChange.urlTree;
                            var extras = { replaceUrl: true };
                            if (state) {
                                var stateCopy = Object.assign({}, state);
                                delete stateCopy.navigationId;
                                if (Object.keys(stateCopy).length !== 0) {
                                    extras.state = stateCopy;
                                }
                            }
                            _this.scheduleNavigation(urlTree, source, state, extras);
                        }, 0);
                    }
                    _this.lastLocationChangeInfo = currentChange;
                });
            }
        };
        /** Extracts router-related information from a `PopStateEvent`. */
        Router.prototype.extractLocationChangeInfoFromEvent = function (change) {
            var _a;
            return {
                source: change['type'] === 'popstate' ? 'popstate' : 'hashchange',
                urlTree: this.parseUrl(change['url']),
                // Navigations coming from Angular router have a navigationId state
                // property. When this exists, restore the state.
                state: ((_a = change.state) === null || _a === void 0 ? void 0 : _a.navigationId) ? change.state : null,
                transitionId: this.getTransition().id
            };
        };
        /**
         * Determines whether two events triggered by the Location subscription are due to the same
         * navigation. The location subscription can fire two events (popstate and hashchange) for a
         * single navigation. The second one should be ignored, that is, we should not schedule another
         * navigation in the Router.
         */
        Router.prototype.shouldScheduleNavigation = function (previous, current) {
            if (!previous)
                return true;
            var sameDestination = current.urlTree.toString() === previous.urlTree.toString();
            var eventsOccurredAtSameTime = current.transitionId === previous.transitionId;
            if (!eventsOccurredAtSameTime || !sameDestination) {
                return true;
            }
            if ((current.source === 'hashchange' && previous.source === 'popstate') ||
                (current.source === 'popstate' && previous.source === 'hashchange')) {
                return false;
            }
            return true;
        };
        Object.defineProperty(Router.prototype, "url", {
            /** The current URL. */
            get: function () {
                return this.serializeUrl(this.currentUrlTree);
            },
            enumerable: false,
            configurable: true
        });
        /** The current Navigation object if one exists */
        Router.prototype.getCurrentNavigation = function () {
            return this.currentNavigation;
        };
        /** @internal */
        Router.prototype.triggerEvent = function (event) {
            this.events.next(event);
        };
        /**
         * Resets the route configuration used for navigation and generating links.
         *
         * @param config The route array for the new configuration.
         *
         * @usageNotes
         *
         * ```
         * router.resetConfig([
         *  { path: 'team/:id', component: TeamCmp, children: [
         *    { path: 'simple', component: SimpleCmp },
         *    { path: 'user/:name', component: UserCmp }
         *  ]}
         * ]);
         * ```
         */
        Router.prototype.resetConfig = function (config) {
            validateConfig(config);
            this.config = config.map(standardizeConfig);
            this.navigated = false;
            this.lastSuccessfulId = -1;
        };
        /** @nodoc */
        Router.prototype.ngOnDestroy = function () {
            this.dispose();
        };
        /** Disposes of the router. */
        Router.prototype.dispose = function () {
            this.transitions.complete();
            if (this.locationSubscription) {
                this.locationSubscription.unsubscribe();
                this.locationSubscription = undefined;
            }
            this.disposed = true;
        };
        /**
         * Appends URL segments to the current URL tree to create a new URL tree.
         *
         * @param commands An array of URL fragments with which to construct the new URL tree.
         * If the path is static, can be the literal URL string. For a dynamic path, pass an array of path
         * segments, followed by the parameters for each segment.
         * The fragments are applied to the current URL tree or the one provided  in the `relativeTo`
         * property of the options object, if supplied.
         * @param navigationExtras Options that control the navigation strategy.
         * @returns The new URL tree.
         *
         * @usageNotes
         *
         * ```
         * // create /team/33/user/11
         * router.createUrlTree(['/team', 33, 'user', 11]);
         *
         * // create /team/33;expand=true/user/11
         * router.createUrlTree(['/team', 33, {expand: true}, 'user', 11]);
         *
         * // you can collapse static segments like this (this works only with the first passed-in value):
         * router.createUrlTree(['/team/33/user', userId]);
         *
         * // If the first segment can contain slashes, and you do not want the router to split it,
         * // you can do the following:
         * router.createUrlTree([{segmentPath: '/one/two'}]);
         *
         * // create /team/33/(user/11//right:chat)
         * router.createUrlTree(['/team', 33, {outlets: {primary: 'user/11', right: 'chat'}}]);
         *
         * // remove the right secondary node
         * router.createUrlTree(['/team', 33, {outlets: {primary: 'user/11', right: null}}]);
         *
         * // assuming the current url is `/team/33/user/11` and the route points to `user/11`
         *
         * // navigate to /team/33/user/11/details
         * router.createUrlTree(['details'], {relativeTo: route});
         *
         * // navigate to /team/33/user/22
         * router.createUrlTree(['../22'], {relativeTo: route});
         *
         * // navigate to /team/44/user/22
         * router.createUrlTree(['../../team/44/user/22'], {relativeTo: route});
         *
         * Note that a value of `null` or `undefined` for `relativeTo` indicates that the
         * tree should be created relative to the root.
         * ```
         */
        Router.prototype.createUrlTree = function (commands, navigationExtras) {
            if (navigationExtras === void 0) { navigationExtras = {}; }
            var relativeTo = navigationExtras.relativeTo, queryParams = navigationExtras.queryParams, fragment = navigationExtras.fragment, queryParamsHandling = navigationExtras.queryParamsHandling, preserveFragment = navigationExtras.preserveFragment;
            var a = relativeTo || this.routerState.root;
            var f = preserveFragment ? this.currentUrlTree.fragment : fragment;
            var q = null;
            switch (queryParamsHandling) {
                case 'merge':
                    q = Object.assign(Object.assign({}, this.currentUrlTree.queryParams), queryParams);
                    break;
                case 'preserve':
                    q = this.currentUrlTree.queryParams;
                    break;
                default:
                    q = queryParams || null;
            }
            if (q !== null) {
                q = this.removeEmptyProps(q);
            }
            return createUrlTree(a, this.currentUrlTree, commands, q, f);
        };
        /**
         * Navigates to a view using an absolute route path.
         *
         * @param url An absolute path for a defined route. The function does not apply any delta to the
         *     current URL.
         * @param extras An object containing properties that modify the navigation strategy.
         *
         * @returns A Promise that resolves to 'true' when navigation succeeds,
         * to 'false' when navigation fails, or is rejected on error.
         *
         * @usageNotes
         *
         * The following calls request navigation to an absolute path.
         *
         * ```
         * router.navigateByUrl("/team/33/user/11");
         *
         * // Navigate without updating the URL
         * router.navigateByUrl("/team/33/user/11", { skipLocationChange: true });
         * ```
         *
         * @see [Routing and Navigation guide](guide/router)
         *
         */
        Router.prototype.navigateByUrl = function (url, extras) {
            if (extras === void 0) { extras = {
                skipLocationChange: false
            }; }
            if (typeof ngDevMode === 'undefined' ||
                ngDevMode && this.isNgZoneEnabled && !core.NgZone.isInAngularZone()) {
                this.console.warn("Navigation triggered outside Angular zone, did you forget to call 'ngZone.run()'?");
            }
            var urlTree = isUrlTree(url) ? url : this.parseUrl(url);
            var mergedTree = this.urlHandlingStrategy.merge(urlTree, this.rawUrlTree);
            return this.scheduleNavigation(mergedTree, 'imperative', null, extras);
        };
        /**
         * Navigate based on the provided array of commands and a starting point.
         * If no starting route is provided, the navigation is absolute.
         *
         * @param commands An array of URL fragments with which to construct the target URL.
         * If the path is static, can be the literal URL string. For a dynamic path, pass an array of path
         * segments, followed by the parameters for each segment.
         * The fragments are applied to the current URL or the one provided  in the `relativeTo` property
         * of the options object, if supplied.
         * @param extras An options object that determines how the URL should be constructed or
         *     interpreted.
         *
         * @returns A Promise that resolves to `true` when navigation succeeds, to `false` when navigation
         *     fails,
         * or is rejected on error.
         *
         * @usageNotes
         *
         * The following calls request navigation to a dynamic route path relative to the current URL.
         *
         * ```
         * router.navigate(['team', 33, 'user', 11], {relativeTo: route});
         *
         * // Navigate without updating the URL, overriding the default behavior
         * router.navigate(['team', 33, 'user', 11], {relativeTo: route, skipLocationChange: true});
         * ```
         *
         * @see [Routing and Navigation guide](guide/router)
         *
         */
        Router.prototype.navigate = function (commands, extras) {
            if (extras === void 0) { extras = { skipLocationChange: false }; }
            validateCommands(commands);
            return this.navigateByUrl(this.createUrlTree(commands, extras), extras);
        };
        /** Serializes a `UrlTree` into a string */
        Router.prototype.serializeUrl = function (url) {
            return this.urlSerializer.serialize(url);
        };
        /** Parses a string into a `UrlTree` */
        Router.prototype.parseUrl = function (url) {
            var urlTree;
            try {
                urlTree = this.urlSerializer.parse(url);
            }
            catch (e) {
                urlTree = this.malformedUriErrorHandler(e, this.urlSerializer, url);
            }
            return urlTree;
        };
        /** Returns whether the url is activated */
        Router.prototype.isActive = function (url, exact) {
            if (isUrlTree(url)) {
                return containsTree(this.currentUrlTree, url, exact);
            }
            var urlTree = this.parseUrl(url);
            return containsTree(this.currentUrlTree, urlTree, exact);
        };
        Router.prototype.removeEmptyProps = function (params) {
            return Object.keys(params).reduce(function (result, key) {
                var value = params[key];
                if (value !== null && value !== undefined) {
                    result[key] = value;
                }
                return result;
            }, {});
        };
        Router.prototype.processNavigations = function () {
            var _this = this;
            this.navigations.subscribe(function (t) {
                _this.navigated = true;
                _this.lastSuccessfulId = t.id;
                _this.events
                    .next(new NavigationEnd(t.id, _this.serializeUrl(t.extractedUrl), _this.serializeUrl(_this.currentUrlTree)));
                _this.lastSuccessfulNavigation = _this.currentNavigation;
                _this.currentNavigation = null;
                t.resolve(true);
            }, function (e) {
                _this.console.warn("Unhandled Navigation Error: ");
            });
        };
        Router.prototype.scheduleNavigation = function (rawUrl, source, restoredState, extras, priorPromise) {
            if (this.disposed) {
                return Promise.resolve(false);
            }
            // * Imperative navigations (router.navigate) might trigger additional navigations to the same
            //   URL via a popstate event and the locationChangeListener. We should skip these duplicate
            //   navs. Duplicates may also be triggered by attempts to sync AngularJS and Angular router
            //   states.
            // * Imperative navigations can be cancelled by router guards, meaning the URL won't change. If
            //   the user follows that with a navigation using the back/forward button or manual URL change,
            //   the destination may be the same as the previous imperative attempt. We should not skip
            //   these navigations because it's a separate case from the one above -- it's not a duplicate
            //   navigation.
            var lastNavigation = this.getTransition();
            // We don't want to skip duplicate successful navs if they're imperative because
            // onSameUrlNavigation could be 'reload' (so the duplicate is intended).
            var browserNavPrecededByRouterNav = source !== 'imperative' && (lastNavigation === null || lastNavigation === void 0 ? void 0 : lastNavigation.source) === 'imperative';
            var lastNavigationSucceeded = this.lastSuccessfulId === lastNavigation.id;
            // If the last navigation succeeded or is in flight, we can use the rawUrl as the comparison.
            // However, if it failed, we should compare to the final result (urlAfterRedirects).
            var lastNavigationUrl = (lastNavigationSucceeded || this.currentNavigation) ?
                lastNavigation.rawUrl :
                lastNavigation.urlAfterRedirects;
            var duplicateNav = lastNavigationUrl.toString() === rawUrl.toString();
            if (browserNavPrecededByRouterNav && duplicateNav) {
                return Promise.resolve(true); // return value is not used
            }
            var resolve;
            var reject;
            var promise;
            if (priorPromise) {
                resolve = priorPromise.resolve;
                reject = priorPromise.reject;
                promise = priorPromise.promise;
            }
            else {
                promise = new Promise(function (res, rej) {
                    resolve = res;
                    reject = rej;
                });
            }
            var id = ++this.navigationId;
            this.setTransition({
                id: id,
                source: source,
                restoredState: restoredState,
                currentUrlTree: this.currentUrlTree,
                currentRawUrl: this.rawUrlTree,
                rawUrl: rawUrl,
                extras: extras,
                resolve: resolve,
                reject: reject,
                promise: promise,
                currentSnapshot: this.routerState.snapshot,
                currentRouterState: this.routerState
            });
            // Make sure that the error is propagated even though `processNavigations` catch
            // handler does not rethrow
            return promise.catch(function (e) {
                return Promise.reject(e);
            });
        };
        Router.prototype.setBrowserUrl = function (url, replaceUrl, id, state) {
            var path = this.urlSerializer.serialize(url);
            state = state || {};
            if (this.location.isCurrentPathEqualTo(path) || replaceUrl) {
                // TODO(jasonaden): Remove first `navigationId` and rely on `ng` namespace.
                this.location.replaceState(path, '', Object.assign(Object.assign({}, state), { navigationId: id }));
            }
            else {
                this.location.go(path, '', Object.assign(Object.assign({}, state), { navigationId: id }));
            }
        };
        Router.prototype.resetStateAndUrl = function (storedState, storedUrl, rawUrl) {
            this.routerState = storedState;
            this.currentUrlTree = storedUrl;
            this.rawUrlTree = this.urlHandlingStrategy.merge(this.currentUrlTree, rawUrl);
            this.resetUrlToCurrentUrlTree();
        };
        Router.prototype.resetUrlToCurrentUrlTree = function () {
            this.location.replaceState(this.urlSerializer.serialize(this.rawUrlTree), '', { navigationId: this.lastSuccessfulId });
        };
        return Router;
    }());
    Router.decorators = [
        { type: core.Injectable }
    ];
    Router.ctorParameters = function () { return [
        { type: core.Type },
        { type: UrlSerializer },
        { type: ChildrenOutletContexts },
        { type: common.Location },
        { type: core.Injector },
        { type: core.NgModuleFactoryLoader },
        { type: core.Compiler },
        { type: undefined }
    ]; };
    function validateCommands(commands) {
        for (var i = 0; i < commands.length; i++) {
            var cmd = commands[i];
            if (cmd == null) {
                throw new Error("The requested path contains " + cmd + " segment at index " + i);
            }
        }
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @description
     *
     * When applied to an element in a template, makes that element a link
     * that initiates navigation to a route. Navigation opens one or more routed components
     * in one or more `<router-outlet>` locations on the page.
     *
     * Given a route configuration `[{ path: 'user/:name', component: UserCmp }]`,
     * the following creates a static link to the route:
     * `<a routerLink="/user/bob">link to user component</a>`
     *
     * You can use dynamic values to generate the link.
     * For a dynamic link, pass an array of path segments,
     * followed by the params for each segment.
     * For example, `['/team', teamId, 'user', userName, {details: true}]`
     * generates a link to `/team/11/user/bob;details=true`.
     *
     * Multiple static segments can be merged into one term and combined with dynamic segements.
     * For example, `['/team/11/user', userName, {details: true}]`
     *
     * The input that you provide to the link is treated as a delta to the current URL.
     * For instance, suppose the current URL is `/user/(box//aux:team)`.
     * The link `<a [routerLink]="['/user/jim']">Jim</a>` creates the URL
     * `/user/(jim//aux:team)`.
     * See {@link Router#createUrlTree createUrlTree} for more information.
     *
     * @usageNotes
     *
     * You can use absolute or relative paths in a link, set query parameters,
     * control how parameters are handled, and keep a history of navigation states.
     *
     * ### Relative link paths
     *
     * The first segment name can be prepended with `/`, `./`, or `../`.
     * * If the first segment begins with `/`, the router looks up the route from the root of the
     *   app.
     * * If the first segment begins with `./`, or doesn't begin with a slash, the router
     *   looks in the children of the current activated route.
     * * If the first segment begins with `../`, the router goes up one level in the route tree.
     *
     * ### Setting and handling query params and fragments
     *
     * The following link adds a query parameter and a fragment to the generated URL:
     *
     * ```
     * <a [routerLink]="['/user/bob']" [queryParams]="{debug: true}" fragment="education">
     *   link to user component
     * </a>
     * ```
     * By default, the directive constructs the new URL using the given query parameters.
     * The example generates the link: `/user/bob?debug=true#education`.
     *
     * You can instruct the directive to handle query parameters differently
     * by specifying the `queryParamsHandling` option in the link.
     * Allowed values are:
     *
     *  - `'merge'`: Merge the given `queryParams` into the current query params.
     *  - `'preserve'`: Preserve the current query params.
     *
     * For example:
     *
     * ```
     * <a [routerLink]="['/user/bob']" [queryParams]="{debug: true}" queryParamsHandling="merge">
     *   link to user component
     * </a>
     * ```
     *
     * See {@link UrlCreationOptions.queryParamsHandling UrlCreationOptions#queryParamsHandling}.
     *
     * ### Preserving navigation history
     *
     * You can provide a `state` value to be persisted to the browser's
     * [`History.state` property](https://developer.mozilla.org/en-US/docs/Web/API/History#Properties).
     * For example:
     *
     * ```
     * <a [routerLink]="['/user/bob']" [state]="{tracingId: 123}">
     *   link to user component
     * </a>
     * ```
     *
     * Use {@link Router.getCurrentNavigation() Router#getCurrentNavigation} to retrieve a saved
     * navigation-state value. For example, to capture the `tracingId` during the `NavigationStart`
     * event:
     *
     * ```
     * // Get NavigationStart events
     * router.events.pipe(filter(e => e instanceof NavigationStart)).subscribe(e => {
     *   const navigation = router.getCurrentNavigation();
     *   tracingService.trace({id: navigation.extras.state.tracingId});
     * });
     * ```
     *
     * @ngModule RouterModule
     *
     * @publicApi
     */
    var RouterLink = /** @class */ (function () {
        function RouterLink(router, route, tabIndex, renderer, el) {
            this.router = router;
            this.route = route;
            this.commands = [];
            /** @internal */
            this.onChanges = new rxjs.Subject();
            if (tabIndex == null) {
                renderer.setAttribute(el.nativeElement, 'tabindex', '0');
            }
        }
        /** @nodoc */
        RouterLink.prototype.ngOnChanges = function (changes) {
            // This is subscribed to by `RouterLinkActive` so that it knows to update when there are changes
            // to the RouterLinks it's tracking.
            this.onChanges.next(this);
        };
        Object.defineProperty(RouterLink.prototype, "routerLink", {
            /**
             * Commands to pass to {@link Router#createUrlTree Router#createUrlTree}.
             *   - **array**: commands to pass to {@link Router#createUrlTree Router#createUrlTree}.
             *   - **string**: shorthand for array of commands with just the string, i.e. `['/route']`
             *   - **null|undefined**: shorthand for an empty array of commands, i.e. `[]`
             * @see {@link Router#createUrlTree Router#createUrlTree}
             */
            set: function (commands) {
                if (commands != null) {
                    this.commands = Array.isArray(commands) ? commands : [commands];
                }
                else {
                    this.commands = [];
                }
            },
            enumerable: false,
            configurable: true
        });
        /** @nodoc */
        RouterLink.prototype.onClick = function () {
            var extras = {
                skipLocationChange: attrBoolValue(this.skipLocationChange),
                replaceUrl: attrBoolValue(this.replaceUrl),
                state: this.state,
            };
            this.router.navigateByUrl(this.urlTree, extras);
            return true;
        };
        Object.defineProperty(RouterLink.prototype, "urlTree", {
            get: function () {
                return this.router.createUrlTree(this.commands, {
                    // If the `relativeTo` input is not defined, we want to use `this.route` by default.
                    // Otherwise, we should use the value provided by the user in the input.
                    relativeTo: this.relativeTo !== undefined ? this.relativeTo : this.route,
                    queryParams: this.queryParams,
                    fragment: this.fragment,
                    queryParamsHandling: this.queryParamsHandling,
                    preserveFragment: attrBoolValue(this.preserveFragment),
                });
            },
            enumerable: false,
            configurable: true
        });
        return RouterLink;
    }());
    RouterLink.decorators = [
        { type: core.Directive, args: [{ selector: ':not(a):not(area)[routerLink]' },] }
    ];
    RouterLink.ctorParameters = function () { return [
        { type: Router },
        { type: ActivatedRoute },
        { type: String, decorators: [{ type: core.Attribute, args: ['tabindex',] }] },
        { type: core.Renderer2 },
        { type: core.ElementRef }
    ]; };
    RouterLink.propDecorators = {
        queryParams: [{ type: core.Input }],
        fragment: [{ type: core.Input }],
        queryParamsHandling: [{ type: core.Input }],
        preserveFragment: [{ type: core.Input }],
        skipLocationChange: [{ type: core.Input }],
        replaceUrl: [{ type: core.Input }],
        state: [{ type: core.Input }],
        relativeTo: [{ type: core.Input }],
        routerLink: [{ type: core.Input }],
        onClick: [{ type: core.HostListener, args: ['click',] }]
    };
    /**
     * @description
     *
     * Lets you link to specific routes in your app.
     *
     * See `RouterLink` for more information.
     *
     * @ngModule RouterModule
     *
     * @publicApi
     */
    var RouterLinkWithHref = /** @class */ (function () {
        function RouterLinkWithHref(router, route, locationStrategy) {
            var _this = this;
            this.router = router;
            this.route = route;
            this.locationStrategy = locationStrategy;
            this.commands = [];
            /** @internal */
            this.onChanges = new rxjs.Subject();
            this.subscription = router.events.subscribe(function (s) {
                if (s instanceof NavigationEnd) {
                    _this.updateTargetUrlAndHref();
                }
            });
        }
        Object.defineProperty(RouterLinkWithHref.prototype, "routerLink", {
            /**
             * Commands to pass to {@link Router#createUrlTree Router#createUrlTree}.
             *   - **array**: commands to pass to {@link Router#createUrlTree Router#createUrlTree}.
             *   - **string**: shorthand for array of commands with just the string, i.e. `['/route']`
             *   - **null|undefined**: shorthand for an empty array of commands, i.e. `[]`
             * @see {@link Router#createUrlTree Router#createUrlTree}
             */
            set: function (commands) {
                if (commands != null) {
                    this.commands = Array.isArray(commands) ? commands : [commands];
                }
                else {
                    this.commands = [];
                }
            },
            enumerable: false,
            configurable: true
        });
        /** @nodoc */
        RouterLinkWithHref.prototype.ngOnChanges = function (changes) {
            this.updateTargetUrlAndHref();
            this.onChanges.next(this);
        };
        /** @nodoc */
        RouterLinkWithHref.prototype.ngOnDestroy = function () {
            this.subscription.unsubscribe();
        };
        /** @nodoc */
        RouterLinkWithHref.prototype.onClick = function (button, ctrlKey, shiftKey, altKey, metaKey) {
            if (button !== 0 || ctrlKey || shiftKey || altKey || metaKey) {
                return true;
            }
            if (typeof this.target === 'string' && this.target != '_self') {
                return true;
            }
            var extras = {
                skipLocationChange: attrBoolValue(this.skipLocationChange),
                replaceUrl: attrBoolValue(this.replaceUrl),
                state: this.state
            };
            this.router.navigateByUrl(this.urlTree, extras);
            return false;
        };
        RouterLinkWithHref.prototype.updateTargetUrlAndHref = function () {
            this.href = this.locationStrategy.prepareExternalUrl(this.router.serializeUrl(this.urlTree));
        };
        Object.defineProperty(RouterLinkWithHref.prototype, "urlTree", {
            get: function () {
                return this.router.createUrlTree(this.commands, {
                    // If the `relativeTo` input is not defined, we want to use `this.route` by default.
                    // Otherwise, we should use the value provided by the user in the input.
                    relativeTo: this.relativeTo !== undefined ? this.relativeTo : this.route,
                    queryParams: this.queryParams,
                    fragment: this.fragment,
                    queryParamsHandling: this.queryParamsHandling,
                    preserveFragment: attrBoolValue(this.preserveFragment),
                });
            },
            enumerable: false,
            configurable: true
        });
        return RouterLinkWithHref;
    }());
    RouterLinkWithHref.decorators = [
        { type: core.Directive, args: [{ selector: 'a[routerLink],area[routerLink]' },] }
    ];
    RouterLinkWithHref.ctorParameters = function () { return [
        { type: Router },
        { type: ActivatedRoute },
        { type: common.LocationStrategy }
    ]; };
    RouterLinkWithHref.propDecorators = {
        target: [{ type: core.HostBinding, args: ['attr.target',] }, { type: core.Input }],
        queryParams: [{ type: core.Input }],
        fragment: [{ type: core.Input }],
        queryParamsHandling: [{ type: core.Input }],
        preserveFragment: [{ type: core.Input }],
        skipLocationChange: [{ type: core.Input }],
        replaceUrl: [{ type: core.Input }],
        state: [{ type: core.Input }],
        relativeTo: [{ type: core.Input }],
        href: [{ type: core.HostBinding }],
        routerLink: [{ type: core.Input }],
        onClick: [{ type: core.HostListener, args: ['click',
                    ['$event.button', '$event.ctrlKey', '$event.shiftKey', '$event.altKey', '$event.metaKey'],] }]
    };
    function attrBoolValue(s) {
        return s === '' || !!s;
    }

    /**
     *
     * @description
     *
     * Tracks whether the linked route of an element is currently active, and allows you
     * to specify one or more CSS classes to add to the element when the linked route
     * is active.
     *
     * Use this directive to create a visual distinction for elements associated with an active route.
     * For example, the following code highlights the word "Bob" when the router
     * activates the associated route:
     *
     * ```
     * <a routerLink="/user/bob" routerLinkActive="active-link">Bob</a>
     * ```
     *
     * Whenever the URL is either '/user' or '/user/bob', the "active-link" class is
     * added to the anchor tag. If the URL changes, the class is removed.
     *
     * You can set more than one class using a space-separated string or an array.
     * For example:
     *
     * ```
     * <a routerLink="/user/bob" routerLinkActive="class1 class2">Bob</a>
     * <a routerLink="/user/bob" [routerLinkActive]="['class1', 'class2']">Bob</a>
     * ```
     *
     * To add the classes only when the URL matches the link exactly, add the option `exact: true`:
     *
     * ```
     * <a routerLink="/user/bob" routerLinkActive="active-link" [routerLinkActiveOptions]="{exact:
     * true}">Bob</a>
     * ```
     *
     * To directly check the `isActive` status of the link, assign the `RouterLinkActive`
     * instance to a template variable.
     * For example, the following checks the status without assigning any CSS classes:
     *
     * ```
     * <a routerLink="/user/bob" routerLinkActive #rla="routerLinkActive">
     *   Bob {{ rla.isActive ? '(already open)' : ''}}
     * </a>
     * ```
     *
     * You can apply the `RouterLinkActive` directive to an ancestor of linked elements.
     * For example, the following sets the active-link class on the `<div>`  parent tag
     * when the URL is either '/user/jim' or '/user/bob'.
     *
     * ```
     * <div routerLinkActive="active-link" [routerLinkActiveOptions]="{exact: true}">
     *   <a routerLink="/user/jim">Jim</a>
     *   <a routerLink="/user/bob">Bob</a>
     * </div>
     * ```
     *
     * @ngModule RouterModule
     *
     * @publicApi
     */
    var RouterLinkActive = /** @class */ (function () {
        function RouterLinkActive(router, element, renderer, cdr, link, linkWithHref) {
            var _this = this;
            this.router = router;
            this.element = element;
            this.renderer = renderer;
            this.cdr = cdr;
            this.link = link;
            this.linkWithHref = linkWithHref;
            this.classes = [];
            this.isActive = false;
            this.routerLinkActiveOptions = { exact: false };
            this.routerEventsSubscription = router.events.subscribe(function (s) {
                if (s instanceof NavigationEnd) {
                    _this.update();
                }
            });
        }
        /** @nodoc */
        RouterLinkActive.prototype.ngAfterContentInit = function () {
            var _this = this;
            // `of(null)` is used to force subscribe body to execute once immediately (like `startWith`).
            rxjs.of(this.links.changes, this.linksWithHrefs.changes, rxjs.of(null)).pipe(operators.mergeAll()).subscribe(function (_) {
                _this.update();
                _this.subscribeToEachLinkOnChanges();
            });
        };
        RouterLinkActive.prototype.subscribeToEachLinkOnChanges = function () {
            var _this = this;
            var _a;
            (_a = this.linkInputChangesSubscription) === null || _a === void 0 ? void 0 : _a.unsubscribe();
            var allLinkChanges = __spread(this.links.toArray(), this.linksWithHrefs.toArray(), [this.link, this.linkWithHref]).filter(function (link) { return !!link; })
                .map(function (link) { return link.onChanges; });
            this.linkInputChangesSubscription = rxjs.from(allLinkChanges).pipe(operators.mergeAll()).subscribe(function (link) {
                if (_this.isActive !== _this.isLinkActive(_this.router)(link)) {
                    _this.update();
                }
            });
        };
        Object.defineProperty(RouterLinkActive.prototype, "routerLinkActive", {
            set: function (data) {
                var classes = Array.isArray(data) ? data : data.split(' ');
                this.classes = classes.filter(function (c) { return !!c; });
            },
            enumerable: false,
            configurable: true
        });
        /** @nodoc */
        RouterLinkActive.prototype.ngOnChanges = function (changes) {
            this.update();
        };
        /** @nodoc */
        RouterLinkActive.prototype.ngOnDestroy = function () {
            var _a;
            this.routerEventsSubscription.unsubscribe();
            (_a = this.linkInputChangesSubscription) === null || _a === void 0 ? void 0 : _a.unsubscribe();
        };
        RouterLinkActive.prototype.update = function () {
            var _this = this;
            if (!this.links || !this.linksWithHrefs || !this.router.navigated)
                return;
            Promise.resolve().then(function () {
                var hasActiveLinks = _this.hasActiveLinks();
                if (_this.isActive !== hasActiveLinks) {
                    _this.isActive = hasActiveLinks;
                    _this.cdr.markForCheck();
                    _this.classes.forEach(function (c) {
                        if (hasActiveLinks) {
                            _this.renderer.addClass(_this.element.nativeElement, c);
                        }
                        else {
                            _this.renderer.removeClass(_this.element.nativeElement, c);
                        }
                    });
                }
            });
        };
        RouterLinkActive.prototype.isLinkActive = function (router) {
            var _this = this;
            return function (link) { return router.isActive(link.urlTree, _this.routerLinkActiveOptions.exact); };
        };
        RouterLinkActive.prototype.hasActiveLinks = function () {
            var isActiveCheckFn = this.isLinkActive(this.router);
            return this.link && isActiveCheckFn(this.link) ||
                this.linkWithHref && isActiveCheckFn(this.linkWithHref) ||
                this.links.some(isActiveCheckFn) || this.linksWithHrefs.some(isActiveCheckFn);
        };
        return RouterLinkActive;
    }());
    RouterLinkActive.decorators = [
        { type: core.Directive, args: [{
                    selector: '[routerLinkActive]',
                    exportAs: 'routerLinkActive',
                },] }
    ];
    RouterLinkActive.ctorParameters = function () { return [
        { type: Router },
        { type: core.ElementRef },
        { type: core.Renderer2 },
        { type: core.ChangeDetectorRef },
        { type: RouterLink, decorators: [{ type: core.Optional }] },
        { type: RouterLinkWithHref, decorators: [{ type: core.Optional }] }
    ]; };
    RouterLinkActive.propDecorators = {
        links: [{ type: core.ContentChildren, args: [RouterLink, { descendants: true },] }],
        linksWithHrefs: [{ type: core.ContentChildren, args: [RouterLinkWithHref, { descendants: true },] }],
        routerLinkActiveOptions: [{ type: core.Input }],
        routerLinkActive: [{ type: core.Input }]
    };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @description
     *
     * Acts as a placeholder that Angular dynamically fills based on the current router state.
     *
     * Each outlet can have a unique name, determined by the optional `name` attribute.
     * The name cannot be set or changed dynamically. If not set, default value is "primary".
     *
     * ```
     * <router-outlet></router-outlet>
     * <router-outlet name='left'></router-outlet>
     * <router-outlet name='right'></router-outlet>
     * ```
     *
     * Named outlets can be the targets of secondary routes.
     * The `Route` object for a secondary route has an `outlet` property to identify the target outlet:
     *
     * `{path: <base-path>, component: <component>, outlet: <target_outlet_name>}`
     *
     * Using named outlets and secondary routes, you can target multiple outlets in
     * the same `RouterLink` directive.
     *
     * The router keeps track of separate branches in a navigation tree for each named outlet and
     * generates a representation of that tree in the URL.
     * The URL for a secondary route uses the following syntax to specify both the primary and secondary
     * routes at the same time:
     *
     * `http://base-path/primary-route-path(outlet-name:route-path)`
     *
     * A router outlet emits an activate event when a new component is instantiated,
     * and a deactivate event when a component is destroyed.
     *
     * ```
     * <router-outlet
     *   (activate)='onActivate($event)'
     *   (deactivate)='onDeactivate($event)'></router-outlet>
     * ```
     *
     * @see [Routing tutorial](guide/router-tutorial-toh#named-outlets "Example of a named
     * outlet and secondary route configuration").
     * @see `RouterLink`
     * @see `Route`
     * @ngModule RouterModule
     *
     * @publicApi
     */
    var RouterOutlet = /** @class */ (function () {
        function RouterOutlet(parentContexts, location, resolver, name, changeDetector) {
            this.parentContexts = parentContexts;
            this.location = location;
            this.resolver = resolver;
            this.changeDetector = changeDetector;
            this.activated = null;
            this._activatedRoute = null;
            this.activateEvents = new core.EventEmitter();
            this.deactivateEvents = new core.EventEmitter();
            this.name = name || PRIMARY_OUTLET;
            parentContexts.onChildOutletCreated(this.name, this);
        }
        /** @nodoc */
        RouterOutlet.prototype.ngOnDestroy = function () {
            this.parentContexts.onChildOutletDestroyed(this.name);
        };
        /** @nodoc */
        RouterOutlet.prototype.ngOnInit = function () {
            if (!this.activated) {
                // If the outlet was not instantiated at the time the route got activated we need to populate
                // the outlet when it is initialized (ie inside a NgIf)
                var context = this.parentContexts.getContext(this.name);
                if (context && context.route) {
                    if (context.attachRef) {
                        // `attachRef` is populated when there is an existing component to mount
                        this.attach(context.attachRef, context.route);
                    }
                    else {
                        // otherwise the component defined in the configuration is created
                        this.activateWith(context.route, context.resolver || null);
                    }
                }
            }
        };
        Object.defineProperty(RouterOutlet.prototype, "isActivated", {
            get: function () {
                return !!this.activated;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(RouterOutlet.prototype, "component", {
            get: function () {
                if (!this.activated)
                    throw new Error('Outlet is not activated');
                return this.activated.instance;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(RouterOutlet.prototype, "activatedRoute", {
            get: function () {
                if (!this.activated)
                    throw new Error('Outlet is not activated');
                return this._activatedRoute;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(RouterOutlet.prototype, "activatedRouteData", {
            get: function () {
                if (this._activatedRoute) {
                    return this._activatedRoute.snapshot.data;
                }
                return {};
            },
            enumerable: false,
            configurable: true
        });
        /**
         * Called when the `RouteReuseStrategy` instructs to detach the subtree
         */
        RouterOutlet.prototype.detach = function () {
            if (!this.activated)
                throw new Error('Outlet is not activated');
            this.location.detach();
            var cmp = this.activated;
            this.activated = null;
            this._activatedRoute = null;
            return cmp;
        };
        /**
         * Called when the `RouteReuseStrategy` instructs to re-attach a previously detached subtree
         */
        RouterOutlet.prototype.attach = function (ref, activatedRoute) {
            this.activated = ref;
            this._activatedRoute = activatedRoute;
            this.location.insert(ref.hostView);
        };
        RouterOutlet.prototype.deactivate = function () {
            if (this.activated) {
                var c = this.component;
                this.activated.destroy();
                this.activated = null;
                this._activatedRoute = null;
                this.deactivateEvents.emit(c);
            }
        };
        RouterOutlet.prototype.activateWith = function (activatedRoute, resolver) {
            if (this.isActivated) {
                throw new Error('Cannot activate an already activated outlet');
            }
            this._activatedRoute = activatedRoute;
            var snapshot = activatedRoute._futureSnapshot;
            var component = snapshot.routeConfig.component;
            resolver = resolver || this.resolver;
            var factory = resolver.resolveComponentFactory(component);
            var childContexts = this.parentContexts.getOrCreateContext(this.name).children;
            var injector = new OutletInjector(activatedRoute, childContexts, this.location.injector);
            this.activated = this.location.createComponent(factory, this.location.length, injector);
            // Calling `markForCheck` to make sure we will run the change detection when the
            // `RouterOutlet` is inside a `ChangeDetectionStrategy.OnPush` component.
            this.changeDetector.markForCheck();
            this.activateEvents.emit(this.activated.instance);
        };
        return RouterOutlet;
    }());
    RouterOutlet.decorators = [
        { type: core.Directive, args: [{ selector: 'router-outlet', exportAs: 'outlet' },] }
    ];
    RouterOutlet.ctorParameters = function () { return [
        { type: ChildrenOutletContexts },
        { type: core.ViewContainerRef },
        { type: core.ComponentFactoryResolver },
        { type: String, decorators: [{ type: core.Attribute, args: ['name',] }] },
        { type: core.ChangeDetectorRef }
    ]; };
    RouterOutlet.propDecorators = {
        activateEvents: [{ type: core.Output, args: ['activate',] }],
        deactivateEvents: [{ type: core.Output, args: ['deactivate',] }]
    };
    var OutletInjector = /** @class */ (function () {
        function OutletInjector(route, childContexts, parent) {
            this.route = route;
            this.childContexts = childContexts;
            this.parent = parent;
        }
        OutletInjector.prototype.get = function (token, notFoundValue) {
            if (token === ActivatedRoute) {
                return this.route;
            }
            if (token === ChildrenOutletContexts) {
                return this.childContexts;
            }
            return this.parent.get(token, notFoundValue);
        };
        return OutletInjector;
    }());

    /**
     * @description
     *
     * Provides a preloading strategy.
     *
     * @publicApi
     */
    var PreloadingStrategy = /** @class */ (function () {
        function PreloadingStrategy() {
        }
        return PreloadingStrategy;
    }());
    /**
     * @description
     *
     * Provides a preloading strategy that preloads all modules as quickly as possible.
     *
     * ```
     * RouterModule.forRoot(ROUTES, {preloadingStrategy: PreloadAllModules})
     * ```
     *
     * @publicApi
     */
    var PreloadAllModules = /** @class */ (function () {
        function PreloadAllModules() {
        }
        PreloadAllModules.prototype.preload = function (route, fn) {
            return fn().pipe(operators.catchError(function () { return rxjs.of(null); }));
        };
        return PreloadAllModules;
    }());
    /**
     * @description
     *
     * Provides a preloading strategy that does not preload any modules.
     *
     * This strategy is enabled by default.
     *
     * @publicApi
     */
    var NoPreloading = /** @class */ (function () {
        function NoPreloading() {
        }
        NoPreloading.prototype.preload = function (route, fn) {
            return rxjs.of(null);
        };
        return NoPreloading;
    }());
    /**
     * The preloader optimistically loads all router configurations to
     * make navigations into lazily-loaded sections of the application faster.
     *
     * The preloader runs in the background. When the router bootstraps, the preloader
     * starts listening to all navigation events. After every such event, the preloader
     * will check if any configurations can be loaded lazily.
     *
     * If a route is protected by `canLoad` guards, the preloaded will not load it.
     *
     * @publicApi
     */
    var RouterPreloader = /** @class */ (function () {
        function RouterPreloader(router, moduleLoader, compiler, injector, preloadingStrategy) {
            this.router = router;
            this.injector = injector;
            this.preloadingStrategy = preloadingStrategy;
            var onStartLoad = function (r) { return router.triggerEvent(new RouteConfigLoadStart(r)); };
            var onEndLoad = function (r) { return router.triggerEvent(new RouteConfigLoadEnd(r)); };
            this.loader = new RouterConfigLoader(moduleLoader, compiler, onStartLoad, onEndLoad);
        }
        RouterPreloader.prototype.setUpPreloading = function () {
            var _this = this;
            this.subscription =
                this.router.events
                    .pipe(operators.filter(function (e) { return e instanceof NavigationEnd; }), operators.concatMap(function () { return _this.preload(); }))
                    .subscribe(function () { });
        };
        RouterPreloader.prototype.preload = function () {
            var ngModule = this.injector.get(core.NgModuleRef);
            return this.processRoutes(ngModule, this.router.config);
        };
        /** @nodoc */
        RouterPreloader.prototype.ngOnDestroy = function () {
            if (this.subscription) {
                this.subscription.unsubscribe();
            }
        };
        RouterPreloader.prototype.processRoutes = function (ngModule, routes) {
            var e_1, _a;
            var res = [];
            try {
                for (var routes_1 = __values(routes), routes_1_1 = routes_1.next(); !routes_1_1.done; routes_1_1 = routes_1.next()) {
                    var route = routes_1_1.value;
                    // we already have the config loaded, just recurse
                    if (route.loadChildren && !route.canLoad && route._loadedConfig) {
                        var childConfig = route._loadedConfig;
                        res.push(this.processRoutes(childConfig.module, childConfig.routes));
                        // no config loaded, fetch the config
                    }
                    else if (route.loadChildren && !route.canLoad) {
                        res.push(this.preloadConfig(ngModule, route));
                        // recurse into children
                    }
                    else if (route.children) {
                        res.push(this.processRoutes(ngModule, route.children));
                    }
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (routes_1_1 && !routes_1_1.done && (_a = routes_1.return)) _a.call(routes_1);
                }
                finally { if (e_1) throw e_1.error; }
            }
            return rxjs.from(res).pipe(operators.mergeAll(), operators.map(function (_) { return void 0; }));
        };
        RouterPreloader.prototype.preloadConfig = function (ngModule, route) {
            var _this = this;
            return this.preloadingStrategy.preload(route, function () {
                var loaded$ = route._loadedConfig ? rxjs.of(route._loadedConfig) :
                    _this.loader.load(ngModule.injector, route);
                return loaded$.pipe(operators.mergeMap(function (config) {
                    route._loadedConfig = config;
                    return _this.processRoutes(config.module, config.routes);
                }));
            });
        };
        return RouterPreloader;
    }());
    RouterPreloader.decorators = [
        { type: core.Injectable }
    ];
    RouterPreloader.ctorParameters = function () { return [
        { type: Router },
        { type: core.NgModuleFactoryLoader },
        { type: core.Compiler },
        { type: core.Injector },
        { type: PreloadingStrategy }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var RouterScroller = /** @class */ (function () {
        function RouterScroller(router, 
        /** @docsNotRequired */ viewportScroller, options) {
            if (options === void 0) { options = {}; }
            this.router = router;
            this.viewportScroller = viewportScroller;
            this.options = options;
            this.lastId = 0;
            this.lastSource = 'imperative';
            this.restoredId = 0;
            this.store = {};
            // Default both options to 'disabled'
            options.scrollPositionRestoration = options.scrollPositionRestoration || 'disabled';
            options.anchorScrolling = options.anchorScrolling || 'disabled';
        }
        RouterScroller.prototype.init = function () {
            // we want to disable the automatic scrolling because having two places
            // responsible for scrolling results race conditions, especially given
            // that browser don't implement this behavior consistently
            if (this.options.scrollPositionRestoration !== 'disabled') {
                this.viewportScroller.setHistoryScrollRestoration('manual');
            }
            this.routerEventsSubscription = this.createScrollEvents();
            this.scrollEventsSubscription = this.consumeScrollEvents();
        };
        RouterScroller.prototype.createScrollEvents = function () {
            var _this = this;
            return this.router.events.subscribe(function (e) {
                if (e instanceof NavigationStart) {
                    // store the scroll position of the current stable navigations.
                    _this.store[_this.lastId] = _this.viewportScroller.getScrollPosition();
                    _this.lastSource = e.navigationTrigger;
                    _this.restoredId = e.restoredState ? e.restoredState.navigationId : 0;
                }
                else if (e instanceof NavigationEnd) {
                    _this.lastId = e.id;
                    _this.scheduleScrollEvent(e, _this.router.parseUrl(e.urlAfterRedirects).fragment);
                }
            });
        };
        RouterScroller.prototype.consumeScrollEvents = function () {
            var _this = this;
            return this.router.events.subscribe(function (e) {
                if (!(e instanceof Scroll))
                    return;
                // a popstate event. The pop state event will always ignore anchor scrolling.
                if (e.position) {
                    if (_this.options.scrollPositionRestoration === 'top') {
                        _this.viewportScroller.scrollToPosition([0, 0]);
                    }
                    else if (_this.options.scrollPositionRestoration === 'enabled') {
                        _this.viewportScroller.scrollToPosition(e.position);
                    }
                    // imperative navigation "forward"
                }
                else {
                    if (e.anchor && _this.options.anchorScrolling === 'enabled') {
                        _this.viewportScroller.scrollToAnchor(e.anchor);
                    }
                    else if (_this.options.scrollPositionRestoration !== 'disabled') {
                        _this.viewportScroller.scrollToPosition([0, 0]);
                    }
                }
            });
        };
        RouterScroller.prototype.scheduleScrollEvent = function (routerEvent, anchor) {
            this.router.triggerEvent(new Scroll(routerEvent, this.lastSource === 'popstate' ? this.store[this.restoredId] : null, anchor));
        };
        /** @nodoc */
        RouterScroller.prototype.ngOnDestroy = function () {
            if (this.routerEventsSubscription) {
                this.routerEventsSubscription.unsubscribe();
            }
            if (this.scrollEventsSubscription) {
                this.scrollEventsSubscription.unsubscribe();
            }
        };
        return RouterScroller;
    }());
    RouterScroller.decorators = [
        { type: core.Injectable }
    ];
    RouterScroller.ctorParameters = function () { return [
        { type: Router },
        { type: common.ViewportScroller },
        { type: undefined }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * The directives defined in the `RouterModule`.
     */
    var ROUTER_DIRECTIVES = [RouterOutlet, RouterLink, RouterLinkWithHref, RouterLinkActive, ɵEmptyOutletComponent];
    /**
     * A [DI token](guide/glossary/#di-token) for the router service.
     *
     * @publicApi
     */
    var ROUTER_CONFIGURATION = new core.InjectionToken('ROUTER_CONFIGURATION');
    /**
     * @docsNotRequired
     */
    var ROUTER_FORROOT_GUARD = new core.InjectionToken('ROUTER_FORROOT_GUARD');
    var ɵ0 = { enableTracing: false };
    var ROUTER_PROVIDERS = [
        common.Location,
        { provide: UrlSerializer, useClass: DefaultUrlSerializer },
        {
            provide: Router,
            useFactory: setupRouter,
            deps: [
                UrlSerializer, ChildrenOutletContexts, common.Location, core.Injector, core.NgModuleFactoryLoader, core.Compiler,
                ROUTES, ROUTER_CONFIGURATION, [UrlHandlingStrategy, new core.Optional()],
                [RouteReuseStrategy, new core.Optional()]
            ]
        },
        ChildrenOutletContexts,
        { provide: ActivatedRoute, useFactory: rootRoute, deps: [Router] },
        { provide: core.NgModuleFactoryLoader, useClass: core.SystemJsNgModuleLoader },
        RouterPreloader,
        NoPreloading,
        PreloadAllModules,
        { provide: ROUTER_CONFIGURATION, useValue: ɵ0 },
    ];
    function routerNgProbeToken() {
        return new core.NgProbeToken('Router', Router);
    }
    /**
     * @description
     *
     * Adds directives and providers for in-app navigation among views defined in an application.
     * Use the Angular `Router` service to declaratively specify application states and manage state
     * transitions.
     *
     * You can import this NgModule multiple times, once for each lazy-loaded bundle.
     * However, only one `Router` service can be active.
     * To ensure this, there are two ways to register routes when importing this module:
     *
     * * The `forRoot()` method creates an `NgModule` that contains all the directives, the given
     * routes, and the `Router` service itself.
     * * The `forChild()` method creates an `NgModule` that contains all the directives and the given
     * routes, but does not include the `Router` service.
     *
     * @see [Routing and Navigation guide](guide/router) for an
     * overview of how the `Router` service should be used.
     *
     * @publicApi
     */
    var RouterModule = /** @class */ (function () {
        // Note: We are injecting the Router so it gets created eagerly...
        function RouterModule(guard, router) {
        }
        /**
         * Creates and configures a module with all the router providers and directives.
         * Optionally sets up an application listener to perform an initial navigation.
         *
         * When registering the NgModule at the root, import as follows:
         *
         * ```
         * @NgModule({
         *   imports: [RouterModule.forRoot(ROUTES)]
         * })
         * class MyNgModule {}
         * ```
         *
         * @param routes An array of `Route` objects that define the navigation paths for the application.
         * @param config An `ExtraOptions` configuration object that controls how navigation is performed.
         * @return The new `NgModule`.
         *
         */
        RouterModule.forRoot = function (routes, config) {
            return {
                ngModule: RouterModule,
                providers: [
                    ROUTER_PROVIDERS,
                    provideRoutes(routes),
                    {
                        provide: ROUTER_FORROOT_GUARD,
                        useFactory: provideForRootGuard,
                        deps: [[Router, new core.Optional(), new core.SkipSelf()]]
                    },
                    { provide: ROUTER_CONFIGURATION, useValue: config ? config : {} },
                    {
                        provide: common.LocationStrategy,
                        useFactory: provideLocationStrategy,
                        deps: [common.PlatformLocation, [new core.Inject(common.APP_BASE_HREF), new core.Optional()], ROUTER_CONFIGURATION]
                    },
                    {
                        provide: RouterScroller,
                        useFactory: createRouterScroller,
                        deps: [Router, common.ViewportScroller, ROUTER_CONFIGURATION]
                    },
                    {
                        provide: PreloadingStrategy,
                        useExisting: config && config.preloadingStrategy ? config.preloadingStrategy :
                            NoPreloading
                    },
                    { provide: core.NgProbeToken, multi: true, useFactory: routerNgProbeToken },
                    provideRouterInitializer(),
                ],
            };
        };
        /**
         * Creates a module with all the router directives and a provider registering routes,
         * without creating a new Router service.
         * When registering for submodules and lazy-loaded submodules, create the NgModule as follows:
         *
         * ```
         * @NgModule({
         *   imports: [RouterModule.forChild(ROUTES)]
         * })
         * class MyNgModule {}
         * ```
         *
         * @param routes An array of `Route` objects that define the navigation paths for the submodule.
         * @return The new NgModule.
         *
         */
        RouterModule.forChild = function (routes) {
            return { ngModule: RouterModule, providers: [provideRoutes(routes)] };
        };
        return RouterModule;
    }());
    RouterModule.decorators = [
        { type: core.NgModule, args: [{
                    declarations: ROUTER_DIRECTIVES,
                    exports: ROUTER_DIRECTIVES,
                    entryComponents: [ɵEmptyOutletComponent]
                },] }
    ];
    RouterModule.ctorParameters = function () { return [
        { type: undefined, decorators: [{ type: core.Optional }, { type: core.Inject, args: [ROUTER_FORROOT_GUARD,] }] },
        { type: Router, decorators: [{ type: core.Optional }] }
    ]; };
    function createRouterScroller(router, viewportScroller, config) {
        if (config.scrollOffset) {
            viewportScroller.setOffset(config.scrollOffset);
        }
        return new RouterScroller(router, viewportScroller, config);
    }
    function provideLocationStrategy(platformLocationStrategy, baseHref, options) {
        if (options === void 0) { options = {}; }
        return options.useHash ? new common.HashLocationStrategy(platformLocationStrategy, baseHref) :
            new common.PathLocationStrategy(platformLocationStrategy, baseHref);
    }
    function provideForRootGuard(router) {
        if ((typeof ngDevMode === 'undefined' || ngDevMode) && router) {
            throw new Error("RouterModule.forRoot() called twice. Lazy loaded modules should use RouterModule.forChild() instead.");
        }
        return 'guarded';
    }
    /**
     * Registers a [DI provider](guide/glossary#provider) for a set of routes.
     * @param routes The route configuration to provide.
     *
     * @usageNotes
     *
     * ```
     * @NgModule({
     *   imports: [RouterModule.forChild(ROUTES)],
     *   providers: [provideRoutes(EXTRA_ROUTES)]
     * })
     * class MyNgModule {}
     * ```
     *
     * @publicApi
     */
    function provideRoutes(routes) {
        return [
            { provide: core.ANALYZE_FOR_ENTRY_COMPONENTS, multi: true, useValue: routes },
            { provide: ROUTES, multi: true, useValue: routes },
        ];
    }
    function setupRouter(urlSerializer, contexts, location, injector, loader, compiler, config, opts, urlHandlingStrategy, routeReuseStrategy) {
        if (opts === void 0) { opts = {}; }
        var router = new Router(null, urlSerializer, contexts, location, injector, loader, compiler, flatten(config));
        if (urlHandlingStrategy) {
            router.urlHandlingStrategy = urlHandlingStrategy;
        }
        if (routeReuseStrategy) {
            router.routeReuseStrategy = routeReuseStrategy;
        }
        assignExtraOptionsToRouter(opts, router);
        if (opts.enableTracing) {
            var dom_1 = common.ɵgetDOM();
            router.events.subscribe(function (e) {
                dom_1.logGroup("Router Event: " + e.constructor.name);
                dom_1.log(e.toString());
                dom_1.log(e);
                dom_1.logGroupEnd();
            });
        }
        return router;
    }
    function assignExtraOptionsToRouter(opts, router) {
        if (opts.errorHandler) {
            router.errorHandler = opts.errorHandler;
        }
        if (opts.malformedUriErrorHandler) {
            router.malformedUriErrorHandler = opts.malformedUriErrorHandler;
        }
        if (opts.onSameUrlNavigation) {
            router.onSameUrlNavigation = opts.onSameUrlNavigation;
        }
        if (opts.paramsInheritanceStrategy) {
            router.paramsInheritanceStrategy = opts.paramsInheritanceStrategy;
        }
        if (opts.relativeLinkResolution) {
            router.relativeLinkResolution = opts.relativeLinkResolution;
        }
        if (opts.urlUpdateStrategy) {
            router.urlUpdateStrategy = opts.urlUpdateStrategy;
        }
    }
    function rootRoute(router) {
        return router.routerState.root;
    }
    /**
     * Router initialization requires two steps:
     *
     * First, we start the navigation in a `APP_INITIALIZER` to block the bootstrap if
     * a resolver or a guard executes asynchronously.
     *
     * Next, we actually run activation in a `BOOTSTRAP_LISTENER`, using the
     * `afterPreactivation` hook provided by the router.
     * The router navigation starts, reaches the point when preactivation is done, and then
     * pauses. It waits for the hook to be resolved. We then resolve it only in a bootstrap listener.
     */
    var RouterInitializer = /** @class */ (function () {
        function RouterInitializer(injector) {
            this.injector = injector;
            this.initNavigation = false;
            this.resultOfPreactivationDone = new rxjs.Subject();
        }
        RouterInitializer.prototype.appInitializer = function () {
            var _this = this;
            var p = this.injector.get(common.LOCATION_INITIALIZED, Promise.resolve(null));
            return p.then(function () {
                var resolve = null;
                var res = new Promise(function (r) { return resolve = r; });
                var router = _this.injector.get(Router);
                var opts = _this.injector.get(ROUTER_CONFIGURATION);
                if (opts.initialNavigation === 'disabled') {
                    router.setUpLocationChangeListener();
                    resolve(true);
                }
                else if (
                // TODO: enabled is deprecated as of v11, can be removed in v13
                opts.initialNavigation === 'enabled' || opts.initialNavigation === 'enabledBlocking') {
                    router.hooks.afterPreactivation = function () {
                        // only the initial navigation should be delayed
                        if (!_this.initNavigation) {
                            _this.initNavigation = true;
                            resolve(true);
                            return _this.resultOfPreactivationDone;
                            // subsequent navigations should not be delayed
                        }
                        else {
                            return rxjs.of(null);
                        }
                    };
                    router.initialNavigation();
                }
                else {
                    resolve(true);
                }
                return res;
            });
        };
        RouterInitializer.prototype.bootstrapListener = function (bootstrappedComponentRef) {
            var opts = this.injector.get(ROUTER_CONFIGURATION);
            var preloader = this.injector.get(RouterPreloader);
            var routerScroller = this.injector.get(RouterScroller);
            var router = this.injector.get(Router);
            var ref = this.injector.get(core.ApplicationRef);
            if (bootstrappedComponentRef !== ref.components[0]) {
                return;
            }
            // Default case
            if (opts.initialNavigation === 'enabledNonBlocking' || opts.initialNavigation === undefined) {
                router.initialNavigation();
            }
            preloader.setUpPreloading();
            routerScroller.init();
            router.resetRootComponentType(ref.componentTypes[0]);
            this.resultOfPreactivationDone.next(null);
            this.resultOfPreactivationDone.complete();
        };
        return RouterInitializer;
    }());
    RouterInitializer.decorators = [
        { type: core.Injectable }
    ];
    RouterInitializer.ctorParameters = function () { return [
        { type: core.Injector }
    ]; };
    function getAppInitializer(r) {
        return r.appInitializer.bind(r);
    }
    function getBootstrapListener(r) {
        return r.bootstrapListener.bind(r);
    }
    /**
     * A [DI token](guide/glossary/#di-token) for the router initializer that
     * is called after the app is bootstrapped.
     *
     * @publicApi
     */
    var ROUTER_INITIALIZER = new core.InjectionToken('Router Initializer');
    function provideRouterInitializer() {
        return [
            RouterInitializer,
            {
                provide: core.APP_INITIALIZER,
                multi: true,
                useFactory: getAppInitializer,
                deps: [RouterInitializer]
            },
            { provide: ROUTER_INITIALIZER, useFactory: getBootstrapListener, deps: [RouterInitializer] },
            { provide: core.APP_BOOTSTRAP_LISTENER, multi: true, useExisting: ROUTER_INITIALIZER },
        ];
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * @publicApi
     */
    var VERSION = new core.Version('11.2.7');

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

    exports.ActivatedRoute = ActivatedRoute;
    exports.ActivatedRouteSnapshot = ActivatedRouteSnapshot;
    exports.ActivationEnd = ActivationEnd;
    exports.ActivationStart = ActivationStart;
    exports.BaseRouteReuseStrategy = BaseRouteReuseStrategy;
    exports.ChildActivationEnd = ChildActivationEnd;
    exports.ChildActivationStart = ChildActivationStart;
    exports.ChildrenOutletContexts = ChildrenOutletContexts;
    exports.DefaultUrlSerializer = DefaultUrlSerializer;
    exports.GuardsCheckEnd = GuardsCheckEnd;
    exports.GuardsCheckStart = GuardsCheckStart;
    exports.NavigationCancel = NavigationCancel;
    exports.NavigationEnd = NavigationEnd;
    exports.NavigationError = NavigationError;
    exports.NavigationStart = NavigationStart;
    exports.NoPreloading = NoPreloading;
    exports.OutletContext = OutletContext;
    exports.PRIMARY_OUTLET = PRIMARY_OUTLET;
    exports.PreloadAllModules = PreloadAllModules;
    exports.PreloadingStrategy = PreloadingStrategy;
    exports.ROUTER_CONFIGURATION = ROUTER_CONFIGURATION;
    exports.ROUTER_INITIALIZER = ROUTER_INITIALIZER;
    exports.ROUTES = ROUTES;
    exports.ResolveEnd = ResolveEnd;
    exports.ResolveStart = ResolveStart;
    exports.RouteConfigLoadEnd = RouteConfigLoadEnd;
    exports.RouteConfigLoadStart = RouteConfigLoadStart;
    exports.RouteReuseStrategy = RouteReuseStrategy;
    exports.Router = Router;
    exports.RouterEvent = RouterEvent;
    exports.RouterLink = RouterLink;
    exports.RouterLinkActive = RouterLinkActive;
    exports.RouterLinkWithHref = RouterLinkWithHref;
    exports.RouterModule = RouterModule;
    exports.RouterOutlet = RouterOutlet;
    exports.RouterPreloader = RouterPreloader;
    exports.RouterState = RouterState;
    exports.RouterStateSnapshot = RouterStateSnapshot;
    exports.RoutesRecognized = RoutesRecognized;
    exports.Scroll = Scroll;
    exports.UrlHandlingStrategy = UrlHandlingStrategy;
    exports.UrlSegment = UrlSegment;
    exports.UrlSegmentGroup = UrlSegmentGroup;
    exports.UrlSerializer = UrlSerializer;
    exports.UrlTree = UrlTree;
    exports.VERSION = VERSION;
    exports.convertToParamMap = convertToParamMap;
    exports.provideRoutes = provideRoutes;
    exports.ɵEmptyOutletComponent = ɵEmptyOutletComponent;
    exports.ɵROUTER_PROVIDERS = ROUTER_PROVIDERS;
    exports.ɵangular_packages_router_router_a = ROUTER_FORROOT_GUARD;
    exports.ɵangular_packages_router_router_b = routerNgProbeToken;
    exports.ɵangular_packages_router_router_c = createRouterScroller;
    exports.ɵangular_packages_router_router_d = provideLocationStrategy;
    exports.ɵangular_packages_router_router_e = provideForRootGuard;
    exports.ɵangular_packages_router_router_f = setupRouter;
    exports.ɵangular_packages_router_router_g = rootRoute;
    exports.ɵangular_packages_router_router_h = RouterInitializer;
    exports.ɵangular_packages_router_router_i = getAppInitializer;
    exports.ɵangular_packages_router_router_j = getBootstrapListener;
    exports.ɵangular_packages_router_router_k = provideRouterInitializer;
    exports.ɵangular_packages_router_router_l = ɵEmptyOutletComponent;
    exports.ɵangular_packages_router_router_m = Tree;
    exports.ɵangular_packages_router_router_n = TreeNode;
    exports.ɵangular_packages_router_router_o = RouterScroller;
    exports.ɵassignExtraOptionsToRouter = assignExtraOptionsToRouter;
    exports.ɵflatten = flatten;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=router.umd.js.map
