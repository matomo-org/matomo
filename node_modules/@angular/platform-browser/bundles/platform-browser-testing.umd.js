/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/platform-browser'), require('@angular/common')) :
    typeof define === 'function' && define.amd ? define('@angular/platform-browser/testing', ['exports', '@angular/core', '@angular/platform-browser', '@angular/common'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.platformBrowser = global.ng.platformBrowser || {}, global.ng.platformBrowser.testing = {}), global.ng.core, global.ng.platformBrowser, global.ng.common));
}(this, (function (exports, core, platformBrowser, common) { 'use strict';

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

    var BrowserDetection = /** @class */ (function () {
        function BrowserDetection(ua) {
            this._overrideUa = ua;
        }
        Object.defineProperty(BrowserDetection.prototype, "_ua", {
            get: function () {
                if (typeof this._overrideUa === 'string') {
                    return this._overrideUa;
                }
                return common.ɵgetDOM() ? common.ɵgetDOM().getUserAgent() : '';
            },
            enumerable: false,
            configurable: true
        });
        BrowserDetection.setup = function () {
            return new BrowserDetection(null);
        };
        Object.defineProperty(BrowserDetection.prototype, "isFirefox", {
            get: function () {
                return this._ua.indexOf('Firefox') > -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isAndroid", {
            get: function () {
                return this._ua.indexOf('Mozilla/5.0') > -1 && this._ua.indexOf('Android') > -1 &&
                    this._ua.indexOf('AppleWebKit') > -1 && this._ua.indexOf('Chrome') == -1 &&
                    this._ua.indexOf('IEMobile') == -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isEdge", {
            get: function () {
                return this._ua.indexOf('Edge') > -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isIE", {
            get: function () {
                return this._ua.indexOf('Trident') > -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isWebkit", {
            get: function () {
                return this._ua.indexOf('AppleWebKit') > -1 && this._ua.indexOf('Edge') == -1 &&
                    this._ua.indexOf('IEMobile') == -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isIOS7", {
            get: function () {
                return (this._ua.indexOf('iPhone OS 7') > -1 || this._ua.indexOf('iPad OS 7') > -1) &&
                    this._ua.indexOf('IEMobile') == -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isSlow", {
            get: function () {
                return this.isAndroid || this.isIE || this.isIOS7;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isChromeDesktop", {
            get: function () {
                return this._ua.indexOf('Chrome') > -1 && this._ua.indexOf('Mobile Safari') == -1 &&
                    this._ua.indexOf('Edge') == -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "isOldChrome", {
            // "Old Chrome" means Chrome 3X, where there are some discrepancies in the Intl API.
            // Android 4.4 and 5.X have such browsers by default (respectively 30 and 39).
            get: function () {
                return this._ua.indexOf('Chrome') > -1 && this._ua.indexOf('Chrome/3') > -1 &&
                    this._ua.indexOf('Edge') == -1;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "supportsCustomElements", {
            get: function () {
                return (typeof core.ɵglobal.customElements !== 'undefined');
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "supportsDeprecatedCustomCustomElementsV0", {
            get: function () {
                return (typeof document.registerElement !== 'undefined');
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "supportsRegExUnicodeFlag", {
            get: function () {
                return RegExp.prototype.hasOwnProperty('unicode');
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "supportsShadowDom", {
            get: function () {
                var testEl = document.createElement('div');
                return (typeof testEl.attachShadow !== 'undefined');
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(BrowserDetection.prototype, "supportsDeprecatedShadowDomV0", {
            get: function () {
                var testEl = document.createElement('div');
                return (typeof testEl.createShadowRoot !== 'undefined');
            },
            enumerable: false,
            configurable: true
        });
        return BrowserDetection;
    }());
    var browserDetection = BrowserDetection.setup();
    function dispatchEvent(element, eventType) {
        var evt = common.ɵgetDOM().getDefaultDocument().createEvent('Event');
        evt.initEvent(eventType, true, true);
        common.ɵgetDOM().dispatchEvent(element, evt);
    }
    function createMouseEvent(eventType) {
        var evt = common.ɵgetDOM().getDefaultDocument().createEvent('MouseEvent');
        evt.initEvent(eventType, true, true);
        return evt;
    }
    function el(html) {
        return getContent(createTemplate(html)).firstChild;
    }
    function normalizeCSS(css) {
        return css.replace(/\s+/g, ' ')
            .replace(/:\s/g, ':')
            .replace(/'/g, '"')
            .replace(/ }/g, '}')
            .replace(/url\((\"|\s)(.+)(\"|\s)\)(\s*)/g, function () {
            var match = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                match[_i] = arguments[_i];
            }
            return "url(\"" + match[2] + "\")";
        })
            .replace(/\[(.+)=([^"\]]+)\]/g, function () {
            var match = [];
            for (var _i = 0; _i < arguments.length; _i++) {
                match[_i] = arguments[_i];
            }
            return "[" + match[1] + "=\"" + match[2] + "\"]";
        });
    }
    function getAttributeMap(element) {
        var res = new Map();
        var elAttrs = element.attributes;
        for (var i = 0; i < elAttrs.length; i++) {
            var attrib = elAttrs.item(i);
            res.set(attrib.name, attrib.value);
        }
        return res;
    }
    var _selfClosingTags = ['br', 'hr', 'input'];
    function stringifyElement(el /** TODO #9100 */) {
        var e_1, _a;
        var result = '';
        if (common.ɵgetDOM().isElementNode(el)) {
            var tagName = el.tagName.toLowerCase();
            // Opening tag
            result += "<" + tagName;
            // Attributes in an ordered way
            var attributeMap = getAttributeMap(el);
            var sortedKeys = Array.from(attributeMap.keys()).sort();
            try {
                for (var sortedKeys_1 = __values(sortedKeys), sortedKeys_1_1 = sortedKeys_1.next(); !sortedKeys_1_1.done; sortedKeys_1_1 = sortedKeys_1.next()) {
                    var key = sortedKeys_1_1.value;
                    var lowerCaseKey = key.toLowerCase();
                    var attValue = attributeMap.get(key);
                    if (typeof attValue !== 'string') {
                        result += " " + lowerCaseKey;
                    }
                    else {
                        // Browsers order style rules differently. Order them alphabetically for consistency.
                        if (lowerCaseKey === 'style') {
                            attValue = attValue.split(/; ?/).filter(function (s) { return !!s; }).sort().map(function (s) { return s + ";"; }).join(' ');
                        }
                        result += " " + lowerCaseKey + "=\"" + attValue + "\"";
                    }
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (sortedKeys_1_1 && !sortedKeys_1_1.done && (_a = sortedKeys_1.return)) _a.call(sortedKeys_1);
                }
                finally { if (e_1) throw e_1.error; }
            }
            result += '>';
            // Children
            var childrenRoot = templateAwareRoot(el);
            var children = childrenRoot ? childrenRoot.childNodes : [];
            for (var j = 0; j < children.length; j++) {
                result += stringifyElement(children[j]);
            }
            // Closing tag
            if (_selfClosingTags.indexOf(tagName) == -1) {
                result += "</" + tagName + ">";
            }
        }
        else if (isCommentNode(el)) {
            result += "<!--" + el.nodeValue + "-->";
        }
        else {
            result += el.textContent;
        }
        return result;
    }
    function createNgZone() {
        return new core.NgZone({ enableLongStackTrace: true, shouldCoalesceEventChangeDetection: false });
    }
    function isCommentNode(node) {
        return node.nodeType === Node.COMMENT_NODE;
    }
    function isTextNode(node) {
        return node.nodeType === Node.TEXT_NODE;
    }
    function getContent(node) {
        if ('content' in node) {
            return node.content;
        }
        else {
            return node;
        }
    }
    function templateAwareRoot(el) {
        return common.ɵgetDOM().isElementNode(el) && el.nodeName === 'TEMPLATE' ? getContent(el) : el;
    }
    function setCookie(name, value) {
        // document.cookie is magical, assigning into it assigns/overrides one cookie value, but does
        // not clear other cookies.
        document.cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value);
    }
    function supportsWebAnimation() {
        return typeof Element.prototype['animate'] === 'function';
    }
    function hasStyle(element, styleName, styleValue) {
        var value = element.style[styleName] || '';
        return styleValue ? value == styleValue : value.length > 0;
    }
    function hasClass(element, className) {
        return element.classList.contains(className);
    }
    function sortedClassList(element) {
        return Array.prototype.slice.call(element.classList, 0).sort();
    }
    function createTemplate(html) {
        var t = common.ɵgetDOM().getDefaultDocument().createElement('template');
        t.innerHTML = html;
        return t;
    }
    function childNodesAsList(el) {
        var childNodes = el.childNodes;
        var res = [];
        for (var i = 0; i < childNodes.length; i++) {
            res[i] = childNodes[i];
        }
        return res;
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    function initBrowserTests() {
        platformBrowser.ɵBrowserDomAdapter.makeCurrent();
        BrowserDetection.setup();
    }
    var _TEST_BROWSER_PLATFORM_PROVIDERS = [{ provide: core.PLATFORM_INITIALIZER, useValue: initBrowserTests, multi: true }];
    /**
     * Platform for testing
     *
     * @publicApi
     */
    var platformBrowserTesting = core.createPlatformFactory(core.platformCore, 'browserTesting', _TEST_BROWSER_PLATFORM_PROVIDERS);
    var ɵ0 = createNgZone;
    /**
     * NgModule for testing.
     *
     * @publicApi
     */
    var BrowserTestingModule = /** @class */ (function () {
        function BrowserTestingModule() {
        }
        return BrowserTestingModule;
    }());
    BrowserTestingModule.decorators = [
        { type: core.NgModule, args: [{
                    exports: [platformBrowser.BrowserModule],
                    providers: [
                        { provide: core.APP_ID, useValue: 'a' },
                        platformBrowser.ɵELEMENT_PROBE_PROVIDERS,
                        { provide: core.NgZone, useFactory: ɵ0 },
                    ]
                },] }
    ];

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

    /**
     * Generated bundle index. Do not edit.
     */

    exports.BrowserTestingModule = BrowserTestingModule;
    exports.platformBrowserTesting = platformBrowserTesting;
    exports.ɵ0 = ɵ0;
    exports.ɵangular_packages_platform_browser_testing_testing_a = createNgZone;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=platform-browser-testing.umd.js.map
