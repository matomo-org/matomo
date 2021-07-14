(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/upgrade/static'), require('@angular/platform-browser-dynamic'), require('@angular/platform-browser'), require('rxjs'), require('rxjs/operators'), require('@angular/common/http'), require('@angular/common'), require('@angular/forms')) :
    typeof define === 'function' && define.amd ? define('@matomo/core-home', ['exports', '@angular/core', '@angular/upgrade/static', '@angular/platform-browser-dynamic', '@angular/platform-browser', 'rxjs', 'rxjs/operators', '@angular/common/http', '@angular/common', '@angular/forms'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory((global.matomo = global.matomo || {}, global.matomo['core-home'] = {}), global.ng.core, global.ng.upgrade.static, global.ng.platformBrowserDynamic, global.ng.platformBrowser, global.rxjs, global.rxjs.operators, global.ng.common.http, global.ng.common, global.ng.forms));
}(this, (function (exports, i0, _static, platformBrowserDynamic, i2, rxjs, operators, i1, i4, i5) { 'use strict';

    function _interopNamespace(e) {
        if (e && e.__esModule) return e;
        var n = Object.create(null);
        if (e) {
            Object.keys(e).forEach(function (k) {
                if (k !== 'default') {
                    var d = Object.getOwnPropertyDescriptor(e, k);
                    Object.defineProperty(n, k, d.get ? d : {
                        enumerable: true,
                        get: function () {
                            return e[k];
                        }
                    });
                }
            });
        }
        n['default'] = e;
        return Object.freeze(n);
    }

    var i0__namespace = /*#__PURE__*/_interopNamespace(i0);
    var i2__namespace = /*#__PURE__*/_interopNamespace(i2);
    var i1__namespace = /*#__PURE__*/_interopNamespace(i1);
    var i4__namespace = /*#__PURE__*/_interopNamespace(i4);
    var i5__namespace = /*#__PURE__*/_interopNamespace(i5);

    piwikSiteselectorShim.$inject = ['$timeout'];
    function piwikSiteselectorShim($timeout) {
        return {
            restrict: 'A',
            scope: {
                showSelectedSite: '=',
                showAllSitesItem: '=',
                switchSiteOnSelect: '=',
                onlySitesWithAdminAccess: '=',
                inputName: '@name',
                allSitesText: '@',
                allSitesLocation: '@',
                placeholder: '@'
            },
            require: "?ngModel",
            template: "<piwik-siteselector-downgrade\n            show-selected-site=\"shim.showSelectedSite\"\n            show-all-sites-item=\"shim.showAllSitesItem\"\n            switch-site-on-select=\"shim.switchSiteOnSelect\"\n            only-sites-with-admin-access=\"shim.onlySitesWithAdminAccess\"\n            name=\"shim.inputName\"\n            all-sites-text=\"shim.allSitesText\"\n            all-sites-location=\"shim.allSitesLocation\"\n            placeholder=\"shim.placeholder\"\n            on-selected-site-change=\"shim.onSelectedSiteChange($event)\"\n        ></piwik-siteselector-downgrade>",
            controllerAs: 'shim',
            link: function (scope, element, attrs, ngModel) {
                scope.inputName = attrs.inputName;
                scope.allSitesText = attrs.allSitesText;
                scope.allSitesLocation = attrs.allSitesLocation;
                scope.placeholder = attrs.placeholder;
                scope.siteid = attrs.siteid;
                scope.sitename = attrs.sitename;
                scope.onSelectedSiteChange = function ($event) {
                    scope.selectedSite = $event.data;
                    ngModel.$setViewValue($event.data); // TODO: does this work?
                };
                if (ngModel) {
                    ngModel.$setViewValue(scope.selectedSite);
                }
                if (ngModel) {
                    ngModel.$render = function () {
                        if (angular.isString(ngModel.$viewValue)) {
                            scope.selectedSite = JSON.parse(ngModel.$viewValue);
                        }
                        else {
                            scope.selectedSite = ngModel.$viewValue;
                        }
                    };
                }
                $timeout(function () {
                    window.initTopControls();
                });
            },
        };
    }

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
                if (Object.prototype.hasOwnProperty.call(b, p))
                    d[p] = b[p]; };
        return extendStatics(d, b);
    };
    function __extends(d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
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
    function __exportStar(m, o) {
        for (var p in m)
            if (p !== "default" && !Object.prototype.hasOwnProperty.call(o, p))
                __createBinding(o, m, p);
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
    /** @deprecated */
    function __spread() {
        for (var ar = [], i = 0; i < arguments.length; i++)
            ar = ar.concat(__read(arguments[i]));
        return ar;
    }
    /** @deprecated */
    function __spreadArrays() {
        for (var s = 0, i = 0, il = arguments.length; i < il; i++)
            s += arguments[i].length;
        for (var r = Array(s), k = 0, i = 0; i < il; i++)
            for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
                r[k] = a[j];
        return r;
    }
    function __spreadArray(to, from, pack) {
        if (pack || arguments.length === 2)
            for (var i = 0, l = from.length, ar; i < l; i++) {
                if (ar || !(i in from)) {
                    if (!ar)
                        ar = Array.prototype.slice.call(from, 0, i);
                    ar[i] = from[i];
                }
            }
        return to.concat(ar || from);
    }
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
                if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k))
                    __createBinding(result, mod, k);
        __setModuleDefault(result, mod);
        return result;
    }
    function __importDefault(mod) {
        return (mod && mod.__esModule) ? mod : { default: mod };
    }
    function __classPrivateFieldGet(receiver, state, kind, f) {
        if (kind === "a" && !f)
            throw new TypeError("Private accessor was defined without a getter");
        if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver))
            throw new TypeError("Cannot read private member from an object whose class did not declare it");
        return kind === "m" ? f : kind === "a" ? f.call(receiver) : f ? f.value : state.get(receiver);
    }
    function __classPrivateFieldSet(receiver, state, value, kind, f) {
        if (kind === "m")
            throw new TypeError("Private method is not writable");
        if (kind === "a" && !f)
            throw new TypeError("Private accessor was defined without a setter");
        if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver))
            throw new TypeError("Cannot write private member to an object whose class did not declare it");
        return (kind === "a" ? f.call(receiver, value) : f ? f.value = value : state.set(receiver, value)), value;
    }

    var piwik$1 = window.piwik;
    var MatomoApiService = /** @class */ (function () {
        function MatomoApiService(http) {
            this.http = http;
        }
        MatomoApiService.prototype.fetch = function (params) {
            var body = {
                token_auth: piwik$1.token_auth,
                force_api_session: piwik$1.broadcast.isWidgetizeRequestWithoutSession() ? 0 : 1,
            };
            var mergedParams = Object.assign(this.getCurrentUrlParams(), this.getCurrentHashParams(), params);
            var query = new URLSearchParams(mergedParams).toString();
            var headers = {
                'Content-Type': 'application/x-www-form-urlencoded',
                // ie 8,9,10 caches ajax requests, prevent this
                'cache-control': 'no-cache'
            };
            return this.http.post('index.php?' + query, body, {
                headers: headers,
            });
        };
        MatomoApiService.prototype.getCurrentUrlParams = function () {
            return new URLSearchParams(window.location.search);
        };
        MatomoApiService.prototype.getCurrentHashParams = function () {
            return new URLSearchParams(window.location.hash.replace(/^\//g, ''));
        };
        return MatomoApiService;
    }());
    MatomoApiService.ɵfac = function MatomoApiService_Factory(t) { return new (t || MatomoApiService)(i0__namespace.ɵɵinject(i1__namespace.HttpClient)); };
    MatomoApiService.ɵprov = /*@__PURE__*/ i0__namespace.ɵɵdefineInjectable({ token: MatomoApiService, factory: MatomoApiService.ɵfac });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(MatomoApiService, [{
                type: i0.Injectable
            }], function () { return [{ type: i1__namespace.HttpClient }]; }, null);
    })();

    // NOTE: all public methods return Observables. all functions used w/ map() are pure, they return new objects/arrays to avoid
    // side effects.
    var SitesService = /** @class */ (function () {
        function SitesService(_matomoApi) {
            this._matomoApi = _matomoApi;
            this.onlySitesWithAdminAccess = true;
            // TODO: this should technically be in a global display options service
            this._numWebsitesToDisplayPerPage$ = this._matomoApi.fetch({ method: 'SitesManager.getNumWebsitesToDisplayPerPage' })
                .pipe(operators.map(function (response) { return parseInt(response.value); }), operators.shareReplay(1));
        }
        SitesService.prototype.getNumWebsitesToDisplayPerPage = function () {
            return this._numWebsitesToDisplayPerPage$;
        };
        SitesService.prototype.loadInitialSites = function () {
            if (!this.initialSites$) {
                this.initialSites$ = this.searchSites('%').pipe(operators.shareReplay(1));
            }
            return this.initialSites$;
        };
        SitesService.prototype.searchSites = function (term) {
            var _this = this;
            if (!term) {
                return this.loadInitialSites();
            }
            var methodToCall = 'SitesManager.getPatternMatchSites';
            if (this.onlySitesWithAdminAccess) {
                methodToCall = 'SitesManager.getSitesWithAdminAccess';
            }
            return this._numWebsitesToDisplayPerPage$.pipe(operators.switchMap(function (limit) { return _this._matomoApi.fetch({
                method: methodToCall,
                limit: limit,
                pattern: term,
            }); }), operators.filter(function (response) { return response instanceof Array; }), operators.map(function (sites) { return _this._sortSites(sites); }), operators.map(function (sites) { return _this._enrichSites(sites); }));
        };
        SitesService.prototype._enrichSites = function (sites) {
            return sites.map(function (site) {
                if (!site.group) {
                    return site;
                }
                return Object.assign({}, site, { name: "[" + site.group + "] " + site.name });
            });
        };
        SitesService.prototype._sortSites = function (sites) {
            return sites.concat([]).sort(function (lhs, rhs) {
                if (lhs < rhs) {
                    return -1;
                }
                return lhs > rhs ? 1 : 0;
            });
            return sites;
        };
        return SitesService;
    }());
    SitesService.ɵfac = function SitesService_Factory(t) { return new (t || SitesService)(i0__namespace.ɵɵinject(MatomoApiService)); };
    SitesService.ɵprov = /*@__PURE__*/ i0__namespace.ɵɵdefineInjectable({ token: SitesService, factory: SitesService.ɵfac });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(SitesService, [{
                type: i0.Injectable
            }], function () { return [{ type: MatomoApiService }]; }, null);
    })();

    var FocusAnywhereButHereDirective = /** @class */ (function () {
        function FocusAnywhereButHereDirective(el) {
            this.el = el;
            this.isMouseDown = false;
            this.hasScrolled = false;
            this.onLoseFocus = new i0.EventEmitter();
        }
        FocusAnywhereButHereDirective.prototype.onEscapeHandler = function (event) {
            if (event.key.charCodeAt(0) === 27) { // TODO: test
                this.isMouseDown = false;
                this.hasScrolled = false;
                this.onLoseFocus.emit();
            }
        };
        FocusAnywhereButHereDirective.prototype.onClickOutsideElement = function (event) {
            var hadUsedScrollbar = this.isMouseDown && this.hasScrolled;
            this.isMouseDown = false;
            this.hasScrolled = false;
            if (hadUsedScrollbar) {
                return;
            }
            if (!this.el.nativeElement.contains(event.target)) {
                this.onLoseFocus.emit();
            }
        };
        FocusAnywhereButHereDirective.prototype.onScroll = function () {
            this.hasScrolled = true;
        };
        FocusAnywhereButHereDirective.prototype.onMouseDown = function () {
            this.isMouseDown = true;
            this.hasScrolled = false;
        };
        return FocusAnywhereButHereDirective;
    }());
    FocusAnywhereButHereDirective.ɵfac = function FocusAnywhereButHereDirective_Factory(t) { return new (t || FocusAnywhereButHereDirective)(i0__namespace.ɵɵdirectiveInject(i0__namespace.ElementRef)); };
    FocusAnywhereButHereDirective.ɵdir = /*@__PURE__*/ i0__namespace.ɵɵdefineDirective({ type: FocusAnywhereButHereDirective, selectors: [["", "focusAnywhereButHere", ""]], hostBindings: function FocusAnywhereButHereDirective_HostBindings(rf, ctx) {
            if (rf & 1) {
                i0__namespace.ɵɵlistener("keyup", function FocusAnywhereButHereDirective_keyup_HostBindingHandler() { return ctx.onEscapeHandler(); }, false, i0__namespace.ɵɵresolveDocument)("mouseup", function FocusAnywhereButHereDirective_mouseup_HostBindingHandler() { return ctx.onClickOutsideElement(); }, false, i0__namespace.ɵɵresolveDocument)("scroll", function FocusAnywhereButHereDirective_scroll_HostBindingHandler() { return ctx.onScroll(); }, false, i0__namespace.ɵɵresolveDocument)("mousedown", function FocusAnywhereButHereDirective_mousedown_HostBindingHandler() { return ctx.onMouseDown(); }, false, i0__namespace.ɵɵresolveDocument);
            }
        }, outputs: { onLoseFocus: "onLoseFocus" } });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(FocusAnywhereButHereDirective, [{
                type: i0.Directive,
                args: [{
                        selector: '[focusAnywhereButHere]'
                    }]
            }], function () { return [{ type: i0__namespace.ElementRef }]; }, { onLoseFocus: [{
                    type: i0.Output
                }], onEscapeHandler: [{
                    type: i0.HostListener,
                    args: ['document:keyup']
                }], onClickOutsideElement: [{
                    type: i0.HostListener,
                    args: ['document:mouseup']
                }], onScroll: [{
                    type: i0.HostListener,
                    args: ['document:scroll']
                }], onMouseDown: [{
                    type: i0.HostListener,
                    args: ['document:mousedown']
                }] });
    })();

    var TranslatePipe = /** @class */ (function () {
        function TranslatePipe() {
        }
        TranslatePipe.prototype.transform = function (key) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            return _pk_translate.apply(void 0, __spreadArray([key], __read(args)));
        };
        return TranslatePipe;
    }());
    TranslatePipe.ɵfac = function TranslatePipe_Factory(t) { return new (t || TranslatePipe)(); };
    TranslatePipe.ɵpipe = /*@__PURE__*/ i0__namespace.ɵɵdefinePipe({ name: "translate", type: TranslatePipe, pure: true });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(TranslatePipe, [{
                type: i0.Pipe,
                args: [{ name: 'translate' }]
            }], null, null);
    })();

    var _c0 = ["customSelectInput"];
    var _c1 = ["selectedSiteDisplay"];
    var _c2 = ["listLink"];
    function SiteSelectorComponent_input_3_Template(rf, ctx) {
        if (rf & 1) {
            i0__namespace.ɵɵelement(0, "input", 7);
        }
        if (rf & 2) {
            var ctx_r1 = i0__namespace.ɵɵnextContext();
            i0__namespace.ɵɵproperty("value", ctx_r1.selectedSite == null ? null : ctx_r1.selectedSite.id);
            i0__namespace.ɵɵattribute("name", ctx_r1.name);
        }
    }
    function SiteSelectorComponent_span_10_Template(rf, ctx) {
        if (rf & 1) {
            i0__namespace.ɵɵelementStart(0, "span");
            i0__namespace.ɵɵtext(1);
            i0__namespace.ɵɵpipe(2, "async");
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r3 = i0__namespace.ɵɵnextContext();
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵtextInterpolate((ctx_r3.selectedSite == null ? null : ctx_r3.selectedSite.name) || i0__namespace.ɵɵpipeBind1(2, 1, ctx_r3._firstSiteName$));
        }
    }
    function SiteSelectorComponent_span_11_Template(rf, ctx) {
        if (rf & 1) {
            i0__namespace.ɵɵelementStart(0, "span");
            i0__namespace.ɵɵtext(1);
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r4 = i0__namespace.ɵɵnextContext();
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵtextInterpolate(ctx_r4.placeholder);
        }
    }
    function SiteSelectorComponent_div_12_div_1_img_4_Template(rf, ctx) {
        if (rf & 1) {
            var _r14_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "img", 18);
            i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_div_12_div_1_img_4_Template_img_click_0_listener() { i0__namespace.ɵɵrestoreView(_r14_1); var ctx_r13 = i0__namespace.ɵɵnextContext(3); return ctx_r13.clearSearchTerm(); });
            i0__namespace.ɵɵelementEnd();
        }
    }
    function SiteSelectorComponent_div_12_div_1_Template(rf, ctx) {
        if (rf & 1) {
            var _r16_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "div", 14);
            i0__namespace.ɵɵelementStart(1, "input", 15, 16);
            i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_div_12_div_1_Template_input_click_1_listener() { i0__namespace.ɵɵrestoreView(_r16_1); var ctx_r15 = i0__namespace.ɵɵnextContext(2); return ctx_r15.searchTerm = ""; })("ngModelChange", function SiteSelectorComponent_div_12_div_1_Template_input_ngModelChange_1_listener($event) { i0__namespace.ɵɵrestoreView(_r16_1); var ctx_r17 = i0__namespace.ɵɵnextContext(2); return ctx_r17.searchTerm = $event; })("change", function SiteSelectorComponent_div_12_div_1_Template_input_change_1_listener() { i0__namespace.ɵɵrestoreView(_r16_1); var ctx_r18 = i0__namespace.ɵɵnextContext(2); return ctx_r18.searchSite(); });
            i0__namespace.ɵɵpipe(3, "translate");
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵtemplate(4, SiteSelectorComponent_div_12_div_1_img_4_Template, 1, 0, "img", 17);
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r6 = i0__namespace.ɵɵnextContext(2);
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵproperty("ngModel", ctx_r6.searchTerm);
            i0__namespace.ɵɵattribute("placeholder", i0__namespace.ɵɵpipeBind1(3, 3, "General_Search"));
            i0__namespace.ɵɵadvance(3);
            i0__namespace.ɵɵproperty("ngIf", ctx_r6.searchTerm);
        }
    }
    function SiteSelectorComponent_div_12_div_4_Template(rf, ctx) {
        if (rf & 1) {
            var _r20_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "div");
            i0__namespace.ɵɵelementStart(1, "site-selector-all-sites-link", 19);
            i0__namespace.ɵɵlistener("onClickLink", function SiteSelectorComponent_div_12_div_4_Template_site_selector_all_sites_link_onClickLink_1_listener($event) { i0__namespace.ɵɵrestoreView(_r20_1); var ctx_r19 = i0__namespace.ɵɵnextContext(2); return ctx_r19.onClickAllSitesLink($event); });
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r7 = i0__namespace.ɵɵnextContext(2);
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵproperty("allSitesText", ctx_r7.allSitesText);
        }
    }
    function SiteSelectorComponent_div_12_li_7_Template(rf, ctx) {
        if (rf & 1) {
            var _r24_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "li", 20);
            i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_div_12_li_7_Template_li_click_0_listener($event) { var restoredCtx = i0__namespace.ɵɵrestoreView(_r24_1); var site_r21 = restoredCtx.$implicit; var ctx_r23 = i0__namespace.ɵɵnextContext(2); return ctx_r23.switchSite(site_r21, $event); });
            i0__namespace.ɵɵelementStart(1, "a", 21, 22);
            i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_div_12_li_7_Template_a_click_1_listener($event) { return $event.preventDefault(); });
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var site_r21 = ctx.$implicit;
            var ctx_r8 = i0__namespace.ɵɵnextContext(2);
            i0__namespace.ɵɵproperty("hidden", !ctx_r8.showSelectedSite && ctx_r8.activeSiteId == site_r21.idsite);
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵproperty("innerHTML", site_r21.name, i0__namespace.ɵɵsanitizeHtml);
            i0__namespace.ɵɵattribute("href", ctx_r8.getUrlForSiteId(site_r21.idsite), i0__namespace.ɵɵsanitizeUrl)("title", site_r21.name);
        }
    }
    function SiteSelectorComponent_div_12_ul_9_Template(rf, ctx) {
        if (rf & 1) {
            i0__namespace.ɵɵelementStart(0, "ul", 23);
            i0__namespace.ɵɵelementStart(1, "li", 24);
            i0__namespace.ɵɵelementStart(2, "a", 25);
            i0__namespace.ɵɵtext(3);
            i0__namespace.ɵɵpipe(4, "translate");
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r9 = i0__namespace.ɵɵnextContext(2);
            i0__namespace.ɵɵadvance(3);
            i0__namespace.ɵɵtextInterpolate(i0__namespace.ɵɵpipeBind1(4, 1, "SitesManager_NotFound") + " " + ctx_r9.searchTerm);
        }
    }
    function SiteSelectorComponent_div_12_div_11_Template(rf, ctx) {
        if (rf & 1) {
            var _r27_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "div");
            i0__namespace.ɵɵelementStart(1, "site-selector-all-sites-link", 19);
            i0__namespace.ɵɵlistener("onClickLink", function SiteSelectorComponent_div_12_div_11_Template_site_selector_all_sites_link_onClickLink_1_listener($event) { i0__namespace.ɵɵrestoreView(_r27_1); var ctx_r26 = i0__namespace.ɵɵnextContext(2); return ctx_r26.onClickAllSitesLink($event); });
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r10 = i0__namespace.ɵɵnextContext(2);
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵproperty("allSitesText", ctx_r10.allSitesText);
        }
    }
    function SiteSelectorComponent_div_12_Template(rf, ctx) {
        if (rf & 1) {
            var _r29_1 = i0__namespace.ɵɵgetCurrentView();
            i0__namespace.ɵɵelementStart(0, "div", 8);
            i0__namespace.ɵɵtemplate(1, SiteSelectorComponent_div_12_div_1_Template, 5, 5, "div", 9);
            i0__namespace.ɵɵpipe(2, "async");
            i0__namespace.ɵɵpipe(3, "async");
            i0__namespace.ɵɵtemplate(4, SiteSelectorComponent_div_12_div_4_Template, 2, 1, "div", 5);
            i0__namespace.ɵɵelementStart(5, "div", 10);
            i0__namespace.ɵɵelementStart(6, "ul", 11);
            i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_div_12_Template_ul_click_6_listener() { i0__namespace.ɵɵrestoreView(_r29_1); var ctx_r28 = i0__namespace.ɵɵnextContext(); return ctx_r28.showSitesList = false; });
            i0__namespace.ɵɵtemplate(7, SiteSelectorComponent_div_12_li_7_Template, 3, 4, "li", 12);
            i0__namespace.ɵɵpipe(8, "async");
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵtemplate(9, SiteSelectorComponent_div_12_ul_9_Template, 5, 3, "ul", 13);
            i0__namespace.ɵɵpipe(10, "async");
            i0__namespace.ɵɵelementEnd();
            i0__namespace.ɵɵtemplate(11, SiteSelectorComponent_div_12_div_11_Template, 2, 1, "div", 5);
            i0__namespace.ɵɵelementEnd();
        }
        if (rf & 2) {
            var ctx_r5 = i0__namespace.ɵɵnextContext();
            i0__namespace.ɵɵadvance(1);
            i0__namespace.ɵɵproperty("ngIf", (i0__namespace.ɵɵpipeBind1(2, 5, ctx_r5.autocompleteMinSites$) || 0) <= (i0__namespace.ɵɵpipeBind1(3, 7, ctx_r5._sitesLength$) || 0) || ctx_r5.searchTerm);
            i0__namespace.ɵɵadvance(3);
            i0__namespace.ɵɵproperty("ngIf", ctx_r5.allSitesLocation == "top" && ctx_r5.showAllSitesItem);
            i0__namespace.ɵɵadvance(3);
            i0__namespace.ɵɵproperty("ngForOf", i0__namespace.ɵɵpipeBind1(8, 9, ctx_r5._sites$));
            i0__namespace.ɵɵadvance(2);
            i0__namespace.ɵɵproperty("ngIf", !i0__namespace.ɵɵpipeBind1(10, 11, ctx_r5._sitesLength$) && ctx_r5.searchTerm);
            i0__namespace.ɵɵadvance(2);
            i0__namespace.ɵɵproperty("ngIf", ctx_r5.allSitesLocation == "bottom" && ctx_r5.showAllSitesItem);
        }
    }
    var _c3 = function (a0, a1) { return { expanded: a0, disabled: a1 }; };
    var _c4 = function (a0) { return { loading: a0, title: true }; };
    var _c5 = function (a2, a3) { return { icon: true, "icon-arrow-bottom": true, iconHidden: a2, collapsed: a3 }; };
    var piwik = window.piwik;
    var piwikHelper = window.piwikHelper;
    var SiteSelectorAllSitesLink = /** @class */ (function () {
        function SiteSelectorAllSitesLink() {
            this.allSitesText = '';
            this.onClickLink = new i0.EventEmitter();
        }
        SiteSelectorAllSitesLink.prototype.getUrlAllSites = function () {
            var newParameters = 'module=MultiSites&action=index';
            return window.piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
        };
        return SiteSelectorAllSitesLink;
    }());
    SiteSelectorAllSitesLink.ɵfac = function SiteSelectorAllSitesLink_Factory(t) { return new (t || SiteSelectorAllSitesLink)(); };
    SiteSelectorAllSitesLink.ɵcmp = /*@__PURE__*/ i0__namespace.ɵɵdefineComponent({ type: SiteSelectorAllSitesLink, selectors: [["site-selector-all-sites-link"]], inputs: { allSitesText: "allSitesText" }, outputs: { onClickLink: "onClickLink" }, decls: 2, vars: 2, consts: [[1, "custom_select_all", 3, "click"], ["tabindex", "4", 3, "innerHTML", "click"]], template: function SiteSelectorAllSitesLink_Template(rf, ctx) {
            if (rf & 1) {
                i0__namespace.ɵɵelementStart(0, "div", 0);
                i0__namespace.ɵɵlistener("click", function SiteSelectorAllSitesLink_Template_div_click_0_listener($event) { return ctx.onClickLink.next($event); });
                i0__namespace.ɵɵelementStart(1, "a", 1);
                i0__namespace.ɵɵlistener("click", function SiteSelectorAllSitesLink_Template_a_click_1_listener($event) { return $event.preventDefault(); });
                i0__namespace.ɵɵelementEnd();
                i0__namespace.ɵɵelementEnd();
            }
            if (rf & 2) {
                i0__namespace.ɵɵadvance(1);
                i0__namespace.ɵɵproperty("innerHTML", ctx.allSitesText, i0__namespace.ɵɵsanitizeHtml);
                i0__namespace.ɵɵattribute("href", ctx.getUrlAllSites(), i0__namespace.ɵɵsanitizeUrl);
            }
        }, encapsulation: 2 });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(SiteSelectorAllSitesLink, [{
                type: i0.Component,
                args: [{
                        selector: 'site-selector-all-sites-link',
                        template: "\n        <div\n            (click)=\"onClickLink.next($event)\"\n            class=\"custom_select_all\"\n        >\n            <a \n                [attr.href]=\"getUrlAllSites()\"\n                (click)=\"$event.preventDefault()\"\n                tabindex=\"4\"\n                [innerHTML]=\"allSitesText\"\n            >\n            </a>\n        </div>\n    ",
                    }]
            }], null, { allSitesText: [{
                    type: i0.Input
                }], onClickLink: [{
                    type: i0.Output
                }] });
    })();
    var SiteSelectorComponent = /** @class */ (function () {
        function SiteSelectorComponent(sitesService, sanitizer) {
            this.sitesService = sitesService;
            this.sanitizer = sanitizer;
            this.showSelectedSite = false;
            this.showAllSitesItem = true;
            this.switchSiteOnSelect = true;
            this.onlySitesWithAdminAccess = false;
            this.name = '';
            this.allSitesText = _pk_translate('General_MultiSitesSummary');
            this.allSitesLocation = 'bottom';
            this.placeholder = '';
            this.onSelectedSiteChange = new i0.EventEmitter();
            this.showSitesList = false;
            this.isLoading = false;
            this.searchTerm = '';
            this.activeSiteId = window.piwik.idSite;
            this._firstLoad = true;
            this._sitesSubject = new rxjs.BehaviorSubject([]);
            this._changes$ = new rxjs.BehaviorSubject({});
            this.autocompleteMinSites$ = this.sitesService.getNumWebsitesToDisplayPerPage();
            this._sites$ = this._sitesSubject.asObservable();
            this._hasMultipleWebsites$ = this._sites$.pipe(operators.map(function (sites) { return sites.length > 1; }));
            this._hasOnlyOneSite$ = this._hasMultipleWebsites$.pipe(operators.map(function (x) { return !x; }));
            this._firstSiteName$ = this._sites$.pipe(operators.map(function (x) { return x === null || x === void 0 ? void 0 : x[0].name; }));
            this._sitesLength$ = this._sites$.pipe(operators.map(function (x) { return x.length; }));
        }
        SiteSelectorComponent.prototype.onClickSelector = function () {
            this.showSitesList = !this.showSitesList;
            if (!this.showSitesList) {
                return;
            }
            if (this.isLoading) {
                return;
            }
            this._loadInitialSites();
        };
        SiteSelectorComponent.prototype.ngOnInit = function () {
            this._setInitialSelectedSite();
            this._onShowingSiteListGrabFocus();
            this._onSearchTermChangeHighlightSiteList();
            this._registerShortcuts();
        };
        SiteSelectorComponent.prototype._setInitialSelectedSite = function () {
            if (this.siteid && this.sitename) {
                this.selectedSite = { id: this.siteid, name: this.sitename };
            }
        };
        SiteSelectorComponent.prototype._onShowingSiteListGrabFocus = function () {
            var _this = this;
            // NOTE: equivalent of a $watch on the expression in site selector
            this._changes$.pipe(operators.combineLatest(this.autocompleteMinSites$, this._sitesLength$), operators.map(function (_c) {
                var _d = __read(_c, 3), changes = _d[0], autoCompleteMinSites = _d[1], sitesLength = _d[2];
                return _this.showSitesList && (autoCompleteMinSites <= sitesLength || _this.searchTerm);
            })).subscribe(function (autoFocus) {
                var _a, _b;
                if (autoFocus) {
                    (_b = (_a = _this._customSelectInput) === null || _a === void 0 ? void 0 : _a.nativeElement) === null || _b === void 0 ? void 0 : _b.focus();
                }
            });
        };
        SiteSelectorComponent.prototype._onSearchTermChangeHighlightSiteList = function () {
            var _this = this;
            this._changes$.pipe(operators.filter(function (changes) { return !!changes.searchTerm; }), operators.map(function (changes) { return changes.searchTerm.currentValue; })).subscribe(function (newSearchTermValue) {
                var _a;
                if (!newSearchTermValue) {
                    return;
                }
                (_a = _this._siteLinks) === null || _a === void 0 ? void 0 : _a.forEach(function (_c) {
                    var nativeElement = _c.nativeElement;
                    var content = piwikHelper.htmlEntities(nativeElement.textContent);
                    var startTerm = content.toLowerCase().indexOf(newSearchTermValue.toLowerCase());
                    if (-1 !== startTerm) {
                        var word = content.substring(startTerm, newSearchTermValue.length);
                        var escapedWord = _this.sanitizer.sanitize(i0.SecurityContext.NONE, word);
                        content = content.replace(word, '<span class="autocompleteMatched">' + escapedWord + '</span>');
                        nativeElement.innerHTML = content;
                    }
                });
            });
        };
        SiteSelectorComponent.prototype.ngOnChanges = function (changes) {
            this._changes$.next(changes);
        };
        SiteSelectorComponent.prototype.searchSite = function () {
            var _this = this;
            this.sitesService.searchSites(this.searchTerm).subscribe(function (sites) { return _this._sitesSubject.next(sites); });
        };
        SiteSelectorComponent.prototype.clearSearchTerm = function () {
            this.searchTerm = '';
            this._loadInitialSites();
        };
        SiteSelectorComponent.prototype._loadInitialSites = function () {
            var _this = this;
            this.sitesService.loadInitialSites().subscribe(function (sites) {
                if (_this._firstLoad) {
                    _this._selectInitialSite(sites);
                }
                _this._firstLoad = false;
                _this._sitesSubject.next(sites);
            });
        };
        SiteSelectorComponent.prototype._selectInitialSite = function (sites) {
            if (!this.selectedSite && sites.length == 1) {
                this.selectedSite = { id: sites[0].idsite, name: sites[0].name };
            }
        };
        SiteSelectorComponent.prototype.onClickAllSitesLink = function (event) {
            this.switchSite({ idsite: 'all', name: this.allSitesText }, event);
            this.showSitesList = false;
        };
        SiteSelectorComponent.prototype.switchSite = function (switchToSite, event) {
            var _a;
            // for Mac OS cmd key needs to be pressed, ctrl key on other systems
            var controlKey = navigator.userAgent.indexOf("Mac OS X") !== -1 ? event.metaKey : event.ctrlKey;
            if (event && controlKey && ((_a = event.target) === null || _a === void 0 ? void 0 : _a.href)) {
                window.open(event.target.href, "_blank");
                return;
            }
            this.selectedSite = { id: switchToSite.idsite, name: switchToSite.name };
            var activeSiteId = window.piwik.idSite;
            if (!this.switchSiteOnSelect || activeSiteId == switchToSite.idsite) {
                this.onSelectedSiteChange.emit(this.selectedSite);
                return;
            }
            this.loadSite(switchToSite.idsite);
        };
        SiteSelectorComponent.prototype.loadSite = function (idSite) {
            if (idSite == 'all') {
                document.location.href = piwikHelper.getCurrentQueryStringWithParametersModified(piwikHelper.getQueryStringFromParameters({
                    module: 'MultiSites',
                    action: 'index',
                    date: piwik.currentDateString,
                    period: piwik.period,
                }));
            }
            else {
                piwik.broadcast.propagateNewPage('segment=&idSite=' + idSite, false);
            }
        };
        SiteSelectorComponent.prototype.getUrlForSiteId = function (idSite) {
            var idSiteParam = 'idSite=' + idSite;
            var newParameters = 'segment=&' + idSiteParam;
            var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
            return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
                '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
        };
        SiteSelectorComponent.prototype._registerShortcuts = function () {
            var _this = this;
            piwikHelper.registerShortcut('w', _pk_translate('CoreHome_ShortcutWebsiteSelector'), function (event) {
                var _a;
                if (event.altKey) {
                    return;
                }
                if (event.preventDefault) {
                    event.preventDefault();
                }
                else {
                    event.returnValue = false; // IE
                }
                _this.onClickSelector();
                (_a = _this._selectedSiteDisplay) === null || _a === void 0 ? void 0 : _a.nativeElement.focus();
            });
        };
        return SiteSelectorComponent;
    }());
    SiteSelectorComponent.ɵfac = function SiteSelectorComponent_Factory(t) { return new (t || SiteSelectorComponent)(i0__namespace.ɵɵdirectiveInject(SitesService), i0__namespace.ɵɵdirectiveInject(i2__namespace.DomSanitizer)); };
    SiteSelectorComponent.ɵcmp = /*@__PURE__*/ i0__namespace.ɵɵdefineComponent({ type: SiteSelectorComponent, selectors: [["piwik-siteselector"]], viewQuery: function SiteSelectorComponent_Query(rf, ctx) {
            if (rf & 1) {
                i0__namespace.ɵɵviewQuery(_c0, 5);
                i0__namespace.ɵɵviewQuery(_c1, 5);
                i0__namespace.ɵɵviewQuery(_c2, 5);
            }
            if (rf & 2) {
                var _t = void 0;
                i0__namespace.ɵɵqueryRefresh(_t = i0__namespace.ɵɵloadQuery()) && (ctx._customSelectInput = _t.first);
                i0__namespace.ɵɵqueryRefresh(_t = i0__namespace.ɵɵloadQuery()) && (ctx._selectedSiteDisplay = _t.first);
                i0__namespace.ɵɵqueryRefresh(_t = i0__namespace.ɵɵloadQuery()) && (ctx._siteLinks = _t);
            }
        }, inputs: { showSelectedSite: "showSelectedSite", showAllSitesItem: "showAllSitesItem", switchSiteOnSelect: "switchSiteOnSelect", onlySitesWithAdminAccess: "onlySitesWithAdminAccess", name: "name", allSitesText: "allSitesText", allSitesLocation: "allSitesLocation", placeholder: "placeholder", siteid: "siteid", sitename: "sitename" }, outputs: { onSelectedSiteChange: "onSelectedSiteChange" }, features: [i0__namespace.ɵɵNgOnChangesFeature], decls: 13, vars: 22, consts: [["focusAnywhereButHere", "", 1, "siteSelector", "piwikSelector", "borderedControl", 3, "onLoseFocus"], ["rootdiv", ""], ["type", "hidden", 3, "value", 4, "ngIf"], ["href", "javascript:void(0)", "tabindex", "4", 3, "click", "keyup.enter"], ["selectedSiteDisplay", ""], [4, "ngIf"], ["class", "dropdown", 4, "ngIf"], ["type", "hidden", 3, "value"], [1, "dropdown"], ["class", "custom_select_search", 4, "ngIf"], [1, "custom_select_container"], [1, "custom_select_ul_list", 3, "click"], [3, "hidden", "click", 4, "ngFor", "ngForOf"], ["class", "ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect", 4, "ngIf"], [1, "custom_select_search"], ["type", "text", "tabindex", "4", 1, "websiteSearch", "inp", "browser-default", 3, "ngModel", "click", "ngModelChange", "change"], ["customSelectInput", ""], ["title", "Clear", "class", "reset", "src", "plugins/CoreHome/images/reset_search.png", 3, "click", 4, "ngIf"], ["title", "Clear", "src", "plugins/CoreHome/images/reset_search.png", 1, "reset", 3, "click"], [3, "allSitesText", "onClickLink"], [3, "hidden", "click"], ["tabindex", "4", 3, "innerHTML", "click"], ["listLink", ""], [1, "ui-autocomplete", "ui-front", "ui-menu", "ui-widget", "ui-widget-content", "ui-corner-all", "siteSelect"], [1, "ui-menu-item"], ["tabindex", "-1", 1, "ui-corner-all"]], template: function SiteSelectorComponent_Template(rf, ctx) {
            if (rf & 1) {
                i0__namespace.ɵɵelementStart(0, "div", 0, 1);
                i0__namespace.ɵɵlistener("onLoseFocus", function SiteSelectorComponent_Template_div_onLoseFocus_0_listener() { return ctx.showSitesList = false; });
                i0__namespace.ɵɵpipe(2, "async");
                i0__namespace.ɵɵtemplate(3, SiteSelectorComponent_input_3_Template, 1, 2, "input", 2);
                i0__namespace.ɵɵelementStart(4, "a", 3, 4);
                i0__namespace.ɵɵlistener("click", function SiteSelectorComponent_Template_a_click_4_listener() { return ctx.onClickSelector(); })("keyup.enter", function SiteSelectorComponent_Template_a_keyup_enter_4_listener() { return ctx.onClickSelector(); });
                i0__namespace.ɵɵpipe(6, "async");
                i0__namespace.ɵɵpipe(7, "translate");
                i0__namespace.ɵɵelement(8, "span");
                i0__namespace.ɵɵelementStart(9, "span");
                i0__namespace.ɵɵtemplate(10, SiteSelectorComponent_span_10_Template, 3, 3, "span", 5);
                i0__namespace.ɵɵtemplate(11, SiteSelectorComponent_span_11_Template, 2, 1, "span", 5);
                i0__namespace.ɵɵelementEnd();
                i0__namespace.ɵɵelementEnd();
                i0__namespace.ɵɵtemplate(12, SiteSelectorComponent_div_12_Template, 12, 13, "div", 6);
                i0__namespace.ɵɵelementEnd();
            }
            if (rf & 2) {
                i0__namespace.ɵɵattribute("class", i0__namespace.ɵɵpureFunction2(14, _c3, ctx.showSitesList, i0__namespace.ɵɵpipeBind1(2, 8, ctx._hasOnlyOneSite$)));
                i0__namespace.ɵɵadvance(3);
                i0__namespace.ɵɵproperty("ngIf", ctx.name);
                i0__namespace.ɵɵadvance(1);
                i0__namespace.ɵɵattribute("title", i0__namespace.ɵɵpipeBind1(6, 10, ctx._hasMultipleWebsites$) ? i0__namespace.ɵɵpipeBind1(7, 12, "CoreHome_ChangeCurrentWebsite") : "")("class", i0__namespace.ɵɵpureFunction1(17, _c4, ctx.isLoading));
                i0__namespace.ɵɵadvance(4);
                i0__namespace.ɵɵattribute("class", i0__namespace.ɵɵpureFunction2(19, _c5, ctx.isLoading, !ctx.showSitesList));
                i0__namespace.ɵɵadvance(2);
                i0__namespace.ɵɵproperty("ngIf", (ctx.selectedSite == null ? null : ctx.selectedSite.name) || !ctx.placeholder);
                i0__namespace.ɵɵadvance(1);
                i0__namespace.ɵɵproperty("ngIf", !(ctx.selectedSite == null ? null : ctx.selectedSite.name) && ctx.placeholder);
                i0__namespace.ɵɵadvance(1);
                i0__namespace.ɵɵproperty("ngIf", ctx.showSitesList);
            }
        }, directives: [FocusAnywhereButHereDirective, i4__namespace.NgIf, i4__namespace.NgForOf, i5__namespace.DefaultValueAccessor, i5__namespace.NgControlStatus, i5__namespace.NgModel, SiteSelectorAllSitesLink], pipes: [i4__namespace.AsyncPipe, TranslatePipe], encapsulation: 2 });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(SiteSelectorComponent, [{
                type: i0.Component,
                args: [{
                        selector: 'piwik-siteselector',
                        template: "<div #rootdiv\n                    focusAnywhereButHere\n                    (onLoseFocus)=\"showSitesList=false\"\n                    class=\"siteSelector piwikSelector borderedControl\"\n                    [attr.class]=\"{expanded: showSitesList, disabled: (_hasOnlyOneSite$ | async)}\">\n\n        <input *ngIf=\"name\" type=\"hidden\" [attr.name]=\"name\" [value]=\"selectedSite?.id\"/>\n\n        <a\n                #selectedSiteDisplay\n                (click)=\"onClickSelector()\"\n                (keyup.enter)=\"onClickSelector()\"\n                href=\"javascript:void(0)\"\n                [attr.title]=\"(_hasMultipleWebsites$ | async) ? ('CoreHome_ChangeCurrentWebsite'|translate) : ''\"\n                [attr.class]=\"{loading:isLoading,title:true}\"\n                tabindex=\"4\"\n        >\n            <span [attr.class]=\"{icon:true,'icon-arrow-bottom':true,iconHidden:isLoading,collapsed:!showSitesList}\"></span>\n            <span>\n            <span *ngIf=\"selectedSite?.name || !placeholder\">{{ selectedSite?.name || (_firstSiteName$|async) }}</span>\n            <span *ngIf=\"!selectedSite?.name && placeholder\">{{ placeholder }}</span>\n        </span>\n        </a>\n\n        <div *ngIf=\"showSitesList\" class=\"dropdown\">\n            <div class=\"custom_select_search\" *ngIf=\"((autocompleteMinSites$|async) || 0) <= ((_sitesLength$|async) || 0) || searchTerm\">\n                <input\n                        #customSelectInput\n                        type=\"text\"\n                        (click)=\"searchTerm=''\"\n                        [(ngModel)]=\"searchTerm\"\n                        (change)=\"searchSite()\"\n                        [attr.placeholder]=\"'General_Search'|translate\"\n                        tabindex=\"4\"\n                        class=\"websiteSearch inp browser-default\"\n                />\n                <!-- TODO: translate Clear? -->\n                <img title=\"Clear\"\n                     *ngIf=\"searchTerm\"\n                     (click)=\"clearSearchTerm()\"\n                     class=\"reset\"\n                     src=\"plugins/CoreHome/images/reset_search.png\"\n                />\n            </div>\n\n            <div\n                    *ngIf=\"allSitesLocation=='top' && showAllSitesItem\"\n            >\n                <site-selector-all-sites-link\n                        [allSitesText]=\"allSitesText\"\n                        (onClickLink)=\"onClickAllSitesLink($event)\"\n                >\n                </site-selector-all-sites-link>\n            </div>\n\n            <div class=\"custom_select_container\">\n                <ul class=\"custom_select_ul_list\" (click)=\"showSitesList=false\">\n                    <!-- !showSelectedSite && activeSiteId==site.idsite -->\n                    <li\n                            *ngFor=\"let site of (_sites$|async)\"\n                            (click)=\"switchSite(site, $event)\"\n                            [hidden]=\"!showSelectedSite && activeSiteId == site.idsite\"\n                    >\n                        <a\n                                #listLink\n                                (click)=\"$event.preventDefault()\"\n                                [attr.href]=\"getUrlForSiteId(site.idsite)\"\n                                [attr.title]=\"site.name\"\n                                [innerHTML]=\"site.name\"\n                                tabindex=\"4\"\n                        >\n                        </a>\n                    </li>\n                </ul>\n\n                <ul\n                        *ngIf=\"!(_sitesLength$ | async) && searchTerm\"\n                        class=\"ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect\"\n                >\n                    <li class=\"ui-menu-item\">\n                        <a class=\"ui-corner-all\" tabindex=\"-1\">{{ ('SitesManager_NotFound'|translate) + ' ' + searchTerm }}</a>\n                    </li>\n                </ul>\n            </div>\n\n            <div\n                    *ngIf=\"allSitesLocation=='bottom' && showAllSitesItem\"\n            >\n                <site-selector-all-sites-link\n                        [allSitesText]=\"allSitesText\"\n                        (onClickLink)=\"onClickAllSitesLink($event)\"\n                >\n                </site-selector-all-sites-link>\n            </div>\n        </div>\n    </div>\n    ",
                    }]
            }], function () { return [{ type: SitesService }, { type: i2__namespace.DomSanitizer }]; }, { showSelectedSite: [{
                    type: i0.Input
                }], showAllSitesItem: [{
                    type: i0.Input
                }], switchSiteOnSelect: [{
                    type: i0.Input
                }], onlySitesWithAdminAccess: [{
                    type: i0.Input
                }], name: [{
                    type: i0.Input
                }], allSitesText: [{
                    type: i0.Input
                }], allSitesLocation: [{
                    type: i0.Input
                }], placeholder: [{
                    type: i0.Input
                }], siteid: [{
                    type: i0.Input
                }], sitename: [{
                    type: i0.Input
                }], onSelectedSiteChange: [{
                    type: i0.Output
                }], _customSelectInput: [{
                    type: i0.ViewChild,
                    args: ['customSelectInput']
                }], _selectedSiteDisplay: [{
                    type: i0.ViewChild,
                    args: ['selectedSiteDisplay']
                }], _siteLinks: [{
                    type: i0.ViewChildren,
                    args: ['listLink']
                }] });
    })();

    var CoreHomeModule = /** @class */ (function () {
        function CoreHomeModule() {
        }
        CoreHomeModule.prototype.ngDoBootstrap = function () {
            // empty
        };
        return CoreHomeModule;
    }());
    CoreHomeModule.ɵfac = function CoreHomeModule_Factory(t) { return new (t || CoreHomeModule)(); };
    CoreHomeModule.ɵmod = /*@__PURE__*/ i0__namespace.ɵɵdefineNgModule({ type: CoreHomeModule });
    CoreHomeModule.ɵinj = /*@__PURE__*/ i0__namespace.ɵɵdefineInjector({ providers: [
            FocusAnywhereButHereDirective,
            TranslatePipe,
            MatomoApiService,
            SitesService,
        ], imports: [[
                i2.BrowserModule,
                _static.UpgradeModule,
                i1.HttpClientModule,
                i5.FormsModule,
            ]] });
    (function () {
        (typeof ngDevMode === "undefined" || ngDevMode) && i0__namespace.ɵsetClassMetadata(CoreHomeModule, [{
                type: i0.NgModule,
                args: [{
                        declarations: [
                            SiteSelectorComponent,
                            FocusAnywhereButHereDirective,
                            TranslatePipe,
                            SiteSelectorAllSitesLink,
                        ],
                        imports: [
                            i2.BrowserModule,
                            _static.UpgradeModule,
                            i1.HttpClientModule,
                            i5.FormsModule,
                        ],
                        exports: [
                            SiteSelectorComponent,
                            FocusAnywhereButHereDirective,
                            TranslatePipe,
                        ],
                        providers: [
                            FocusAnywhereButHereDirective,
                            TranslatePipe,
                            MatomoApiService,
                            SitesService,
                        ],
                        entryComponents: [
                            SiteSelectorComponent,
                        ],
                    }]
            }], null, null);
    })();
    (function () {
        (typeof ngJitMode === "undefined" || ngJitMode) && i0__namespace.ɵɵsetNgModuleScope(CoreHomeModule, { declarations: [SiteSelectorComponent,
                FocusAnywhereButHereDirective,
                TranslatePipe,
                SiteSelectorAllSitesLink], imports: [i2.BrowserModule,
                _static.UpgradeModule,
                i1.HttpClientModule,
                i5.FormsModule], exports: [SiteSelectorComponent,
                FocusAnywhereButHereDirective,
                TranslatePipe] });
    })();
    var ng2BootstrapFn = function (extraProviders) { return platformBrowserDynamic.platformBrowserDynamic(extraProviders).bootstrapModule(CoreHomeModule); };
    var angularModuleName = _static.downgradeModule(ng2BootstrapFn);
    angular.module(angularModuleName).directive('piwikSiteselectorDowngrade', _static.downgradeComponent({ component: SiteSelectorComponent, downgradedModule: angularModuleName }));
    angular.module(angularModuleName).directive('piwikSiteselector', piwikSiteselectorShim);

    /*
     * Public API Surface of library
     */

    /**
     * Generated bundle index. Do not edit.
     */

    exports.CoreHomeModule = CoreHomeModule;
    exports.FocusAnywhereButHereDirective = FocusAnywhereButHereDirective;
    exports.MatomoApiService = MatomoApiService;
    exports.SiteSelectorAllSitesLink = SiteSelectorAllSitesLink;
    exports.SiteSelectorComponent = SiteSelectorComponent;
    exports.SitesService = SitesService;
    exports.TranslatePipe = TranslatePipe;
    exports.angularModuleName = angularModuleName;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=matomo-core-home.umd.js.map
