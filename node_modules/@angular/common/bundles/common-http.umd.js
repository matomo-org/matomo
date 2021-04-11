/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('rxjs'), require('rxjs/operators'), require('@angular/common')) :
    typeof define === 'function' && define.amd ? define('@angular/common/http', ['exports', '@angular/core', 'rxjs', 'rxjs/operators', '@angular/common'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.common = global.ng.common || {}, global.ng.common.http = {}), global.ng.core, global.rxjs, global.rxjs.operators, global.ng.common));
}(this, (function (exports, core, rxjs, operators, common) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Transforms an `HttpRequest` into a stream of `HttpEvent`s, one of which will likely be a
     * `HttpResponse`.
     *
     * `HttpHandler` is injectable. When injected, the handler instance dispatches requests to the
     * first interceptor in the chain, which dispatches to the second, etc, eventually reaching the
     * `HttpBackend`.
     *
     * In an `HttpInterceptor`, the `HttpHandler` parameter is the next interceptor in the chain.
     *
     * @publicApi
     */
    var HttpHandler = /** @class */ (function () {
        function HttpHandler() {
        }
        return HttpHandler;
    }());
    /**
     * A final `HttpHandler` which will dispatch the request via browser HTTP APIs to a backend.
     *
     * Interceptors sit between the `HttpClient` interface and the `HttpBackend`.
     *
     * When injected, `HttpBackend` dispatches requests directly to the backend, without going
     * through the interceptor chain.
     *
     * @publicApi
     */
    var HttpBackend = /** @class */ (function () {
        function HttpBackend() {
        }
        return HttpBackend;
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
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Represents the header configuration options for an HTTP request.
     * Instances are immutable. Modifying methods return a cloned
     * instance with the change. The original object is never changed.
     *
     * @publicApi
     */
    var HttpHeaders = /** @class */ (function () {
        /**  Constructs a new HTTP header object with the given values.*/
        function HttpHeaders(headers) {
            var _this = this;
            /**
             * Internal map of lowercased header names to the normalized
             * form of the name (the form seen first).
             */
            this.normalizedNames = new Map();
            /**
             * Queued updates to be materialized the next initialization.
             */
            this.lazyUpdate = null;
            if (!headers) {
                this.headers = new Map();
            }
            else if (typeof headers === 'string') {
                this.lazyInit = function () {
                    _this.headers = new Map();
                    headers.split('\n').forEach(function (line) {
                        var index = line.indexOf(':');
                        if (index > 0) {
                            var name = line.slice(0, index);
                            var key = name.toLowerCase();
                            var value = line.slice(index + 1).trim();
                            _this.maybeSetNormalizedName(name, key);
                            if (_this.headers.has(key)) {
                                _this.headers.get(key).push(value);
                            }
                            else {
                                _this.headers.set(key, [value]);
                            }
                        }
                    });
                };
            }
            else {
                this.lazyInit = function () {
                    _this.headers = new Map();
                    Object.keys(headers).forEach(function (name) {
                        var values = headers[name];
                        var key = name.toLowerCase();
                        if (typeof values === 'string') {
                            values = [values];
                        }
                        if (values.length > 0) {
                            _this.headers.set(key, values);
                            _this.maybeSetNormalizedName(name, key);
                        }
                    });
                };
            }
        }
        /**
         * Checks for existence of a given header.
         *
         * @param name The header name to check for existence.
         *
         * @returns True if the header exists, false otherwise.
         */
        HttpHeaders.prototype.has = function (name) {
            this.init();
            return this.headers.has(name.toLowerCase());
        };
        /**
         * Retrieves the first value of a given header.
         *
         * @param name The header name.
         *
         * @returns The value string if the header exists, null otherwise
         */
        HttpHeaders.prototype.get = function (name) {
            this.init();
            var values = this.headers.get(name.toLowerCase());
            return values && values.length > 0 ? values[0] : null;
        };
        /**
         * Retrieves the names of the headers.
         *
         * @returns A list of header names.
         */
        HttpHeaders.prototype.keys = function () {
            this.init();
            return Array.from(this.normalizedNames.values());
        };
        /**
         * Retrieves a list of values for a given header.
         *
         * @param name The header name from which to retrieve values.
         *
         * @returns A string of values if the header exists, null otherwise.
         */
        HttpHeaders.prototype.getAll = function (name) {
            this.init();
            return this.headers.get(name.toLowerCase()) || null;
        };
        /**
         * Appends a new value to the existing set of values for a header
         * and returns them in a clone of the original instance.
         *
         * @param name The header name for which to append the values.
         * @param value The value to append.
         *
         * @returns A clone of the HTTP headers object with the value appended to the given header.
         */
        HttpHeaders.prototype.append = function (name, value) {
            return this.clone({ name: name, value: value, op: 'a' });
        };
        /**
         * Sets or modifies a value for a given header in a clone of the original instance.
         * If the header already exists, its value is replaced with the given value
         * in the returned object.
         *
         * @param name The header name.
         * @param value The value or values to set or overide for the given header.
         *
         * @returns A clone of the HTTP headers object with the newly set header value.
         */
        HttpHeaders.prototype.set = function (name, value) {
            return this.clone({ name: name, value: value, op: 's' });
        };
        /**
         * Deletes values for a given header in a clone of the original instance.
         *
         * @param name The header name.
         * @param value The value or values to delete for the given header.
         *
         * @returns A clone of the HTTP headers object with the given value deleted.
         */
        HttpHeaders.prototype.delete = function (name, value) {
            return this.clone({ name: name, value: value, op: 'd' });
        };
        HttpHeaders.prototype.maybeSetNormalizedName = function (name, lcName) {
            if (!this.normalizedNames.has(lcName)) {
                this.normalizedNames.set(lcName, name);
            }
        };
        HttpHeaders.prototype.init = function () {
            var _this = this;
            if (!!this.lazyInit) {
                if (this.lazyInit instanceof HttpHeaders) {
                    this.copyFrom(this.lazyInit);
                }
                else {
                    this.lazyInit();
                }
                this.lazyInit = null;
                if (!!this.lazyUpdate) {
                    this.lazyUpdate.forEach(function (update) { return _this.applyUpdate(update); });
                    this.lazyUpdate = null;
                }
            }
        };
        HttpHeaders.prototype.copyFrom = function (other) {
            var _this = this;
            other.init();
            Array.from(other.headers.keys()).forEach(function (key) {
                _this.headers.set(key, other.headers.get(key));
                _this.normalizedNames.set(key, other.normalizedNames.get(key));
            });
        };
        HttpHeaders.prototype.clone = function (update) {
            var clone = new HttpHeaders();
            clone.lazyInit =
                (!!this.lazyInit && this.lazyInit instanceof HttpHeaders) ? this.lazyInit : this;
            clone.lazyUpdate = (this.lazyUpdate || []).concat([update]);
            return clone;
        };
        HttpHeaders.prototype.applyUpdate = function (update) {
            var key = update.name.toLowerCase();
            switch (update.op) {
                case 'a':
                case 's':
                    var value = update.value;
                    if (typeof value === 'string') {
                        value = [value];
                    }
                    if (value.length === 0) {
                        return;
                    }
                    this.maybeSetNormalizedName(update.name, key);
                    var base = (update.op === 'a' ? this.headers.get(key) : undefined) || [];
                    base.push.apply(base, __spread(value));
                    this.headers.set(key, base);
                    break;
                case 'd':
                    var toDelete_1 = update.value;
                    if (!toDelete_1) {
                        this.headers.delete(key);
                        this.normalizedNames.delete(key);
                    }
                    else {
                        var existing = this.headers.get(key);
                        if (!existing) {
                            return;
                        }
                        existing = existing.filter(function (value) { return toDelete_1.indexOf(value) === -1; });
                        if (existing.length === 0) {
                            this.headers.delete(key);
                            this.normalizedNames.delete(key);
                        }
                        else {
                            this.headers.set(key, existing);
                        }
                    }
                    break;
            }
        };
        /**
         * @internal
         */
        HttpHeaders.prototype.forEach = function (fn) {
            var _this = this;
            this.init();
            Array.from(this.normalizedNames.keys())
                .forEach(function (key) { return fn(_this.normalizedNames.get(key), _this.headers.get(key)); });
        };
        return HttpHeaders;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Provides encoding and decoding of URL parameter and query-string values.
     *
     * Serializes and parses URL parameter keys and values to encode and decode them.
     * If you pass URL query parameters without encoding,
     * the query parameters can be misinterpreted at the receiving end.
     *
     *
     * @publicApi
     */
    var HttpUrlEncodingCodec = /** @class */ (function () {
        function HttpUrlEncodingCodec() {
        }
        /**
         * Encodes a key name for a URL parameter or query-string.
         * @param key The key name.
         * @returns The encoded key name.
         */
        HttpUrlEncodingCodec.prototype.encodeKey = function (key) {
            return standardEncoding(key);
        };
        /**
         * Encodes the value of a URL parameter or query-string.
         * @param value The value.
         * @returns The encoded value.
         */
        HttpUrlEncodingCodec.prototype.encodeValue = function (value) {
            return standardEncoding(value);
        };
        /**
         * Decodes an encoded URL parameter or query-string key.
         * @param key The encoded key name.
         * @returns The decoded key name.
         */
        HttpUrlEncodingCodec.prototype.decodeKey = function (key) {
            return decodeURIComponent(key);
        };
        /**
         * Decodes an encoded URL parameter or query-string value.
         * @param value The encoded value.
         * @returns The decoded value.
         */
        HttpUrlEncodingCodec.prototype.decodeValue = function (value) {
            return decodeURIComponent(value);
        };
        return HttpUrlEncodingCodec;
    }());
    function paramParser(rawParams, codec) {
        var map = new Map();
        if (rawParams.length > 0) {
            // The `window.location.search` can be used while creating an instance of the `HttpParams` class
            // (e.g. `new HttpParams({ fromString: window.location.search })`). The `window.location.search`
            // may start with the `?` char, so we strip it if it's present.
            var params = rawParams.replace(/^\?/, '').split('&');
            params.forEach(function (param) {
                var eqIdx = param.indexOf('=');
                var _a = __read(eqIdx == -1 ?
                    [codec.decodeKey(param), ''] :
                    [codec.decodeKey(param.slice(0, eqIdx)), codec.decodeValue(param.slice(eqIdx + 1))], 2), key = _a[0], val = _a[1];
                var list = map.get(key) || [];
                list.push(val);
                map.set(key, list);
            });
        }
        return map;
    }
    function standardEncoding(v) {
        return encodeURIComponent(v)
            .replace(/%40/gi, '@')
            .replace(/%3A/gi, ':')
            .replace(/%24/gi, '$')
            .replace(/%2C/gi, ',')
            .replace(/%3B/gi, ';')
            .replace(/%2B/gi, '+')
            .replace(/%3D/gi, '=')
            .replace(/%3F/gi, '?')
            .replace(/%2F/gi, '/');
    }
    /**
     * An HTTP request/response body that represents serialized parameters,
     * per the MIME type `application/x-www-form-urlencoded`.
     *
     * This class is immutable; all mutation operations return a new instance.
     *
     * @publicApi
     */
    var HttpParams = /** @class */ (function () {
        function HttpParams(options) {
            var _this = this;
            if (options === void 0) { options = {}; }
            this.updates = null;
            this.cloneFrom = null;
            this.encoder = options.encoder || new HttpUrlEncodingCodec();
            if (!!options.fromString) {
                if (!!options.fromObject) {
                    throw new Error("Cannot specify both fromString and fromObject.");
                }
                this.map = paramParser(options.fromString, this.encoder);
            }
            else if (!!options.fromObject) {
                this.map = new Map();
                Object.keys(options.fromObject).forEach(function (key) {
                    var value = options.fromObject[key];
                    _this.map.set(key, Array.isArray(value) ? value : [value]);
                });
            }
            else {
                this.map = null;
            }
        }
        /**
         * Reports whether the body includes one or more values for a given parameter.
         * @param param The parameter name.
         * @returns True if the parameter has one or more values,
         * false if it has no value or is not present.
         */
        HttpParams.prototype.has = function (param) {
            this.init();
            return this.map.has(param);
        };
        /**
         * Retrieves the first value for a parameter.
         * @param param The parameter name.
         * @returns The first value of the given parameter,
         * or `null` if the parameter is not present.
         */
        HttpParams.prototype.get = function (param) {
            this.init();
            var res = this.map.get(param);
            return !!res ? res[0] : null;
        };
        /**
         * Retrieves all values for a  parameter.
         * @param param The parameter name.
         * @returns All values in a string array,
         * or `null` if the parameter not present.
         */
        HttpParams.prototype.getAll = function (param) {
            this.init();
            return this.map.get(param) || null;
        };
        /**
         * Retrieves all the parameters for this body.
         * @returns The parameter names in a string array.
         */
        HttpParams.prototype.keys = function () {
            this.init();
            return Array.from(this.map.keys());
        };
        /**
         * Appends a new value to existing values for a parameter.
         * @param param The parameter name.
         * @param value The new value to add.
         * @return A new body with the appended value.
         */
        HttpParams.prototype.append = function (param, value) {
            return this.clone({ param: param, value: value, op: 'a' });
        };
        /**
         * Constructs a new body with appended values for the given parameter name.
         * @param params parameters and values
         * @return A new body with the new value.
         */
        HttpParams.prototype.appendAll = function (params) {
            var updates = [];
            Object.keys(params).forEach(function (param) {
                var value = params[param];
                if (Array.isArray(value)) {
                    value.forEach(function (_value) {
                        updates.push({ param: param, value: _value, op: 'a' });
                    });
                }
                else {
                    updates.push({ param: param, value: value, op: 'a' });
                }
            });
            return this.clone(updates);
        };
        /**
         * Replaces the value for a parameter.
         * @param param The parameter name.
         * @param value The new value.
         * @return A new body with the new value.
         */
        HttpParams.prototype.set = function (param, value) {
            return this.clone({ param: param, value: value, op: 's' });
        };
        /**
         * Removes a given value or all values from a parameter.
         * @param param The parameter name.
         * @param value The value to remove, if provided.
         * @return A new body with the given value removed, or with all values
         * removed if no value is specified.
         */
        HttpParams.prototype.delete = function (param, value) {
            return this.clone({ param: param, value: value, op: 'd' });
        };
        /**
         * Serializes the body to an encoded string, where key-value pairs (separated by `=`) are
         * separated by `&`s.
         */
        HttpParams.prototype.toString = function () {
            var _this = this;
            this.init();
            return this.keys()
                .map(function (key) {
                var eKey = _this.encoder.encodeKey(key);
                // `a: ['1']` produces `'a=1'`
                // `b: []` produces `''`
                // `c: ['1', '2']` produces `'c=1&c=2'`
                return _this.map.get(key).map(function (value) { return eKey + '=' + _this.encoder.encodeValue(value); })
                    .join('&');
            })
                // filter out empty values because `b: []` produces `''`
                // which results in `a=1&&c=1&c=2` instead of `a=1&c=1&c=2` if we don't
                .filter(function (param) { return param !== ''; })
                .join('&');
        };
        HttpParams.prototype.clone = function (update) {
            var clone = new HttpParams({ encoder: this.encoder });
            clone.cloneFrom = this.cloneFrom || this;
            clone.updates = (this.updates || []).concat(update);
            return clone;
        };
        HttpParams.prototype.init = function () {
            var _this = this;
            if (this.map === null) {
                this.map = new Map();
            }
            if (this.cloneFrom !== null) {
                this.cloneFrom.init();
                this.cloneFrom.keys().forEach(function (key) { return _this.map.set(key, _this.cloneFrom.map.get(key)); });
                this.updates.forEach(function (update) {
                    switch (update.op) {
                        case 'a':
                        case 's':
                            var base = (update.op === 'a' ? _this.map.get(update.param) : undefined) || [];
                            base.push(update.value);
                            _this.map.set(update.param, base);
                            break;
                        case 'd':
                            if (update.value !== undefined) {
                                var base_1 = _this.map.get(update.param) || [];
                                var idx = base_1.indexOf(update.value);
                                if (idx !== -1) {
                                    base_1.splice(idx, 1);
                                }
                                if (base_1.length > 0) {
                                    _this.map.set(update.param, base_1);
                                }
                                else {
                                    _this.map.delete(update.param);
                                }
                            }
                            else {
                                _this.map.delete(update.param);
                                break;
                            }
                    }
                });
                this.cloneFrom = this.updates = null;
            }
        };
        return HttpParams;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Determine whether the given HTTP method may include a body.
     */
    function mightHaveBody(method) {
        switch (method) {
            case 'DELETE':
            case 'GET':
            case 'HEAD':
            case 'OPTIONS':
            case 'JSONP':
                return false;
            default:
                return true;
        }
    }
    /**
     * Safely assert whether the given value is an ArrayBuffer.
     *
     * In some execution environments ArrayBuffer is not defined.
     */
    function isArrayBuffer(value) {
        return typeof ArrayBuffer !== 'undefined' && value instanceof ArrayBuffer;
    }
    /**
     * Safely assert whether the given value is a Blob.
     *
     * In some execution environments Blob is not defined.
     */
    function isBlob(value) {
        return typeof Blob !== 'undefined' && value instanceof Blob;
    }
    /**
     * Safely assert whether the given value is a FormData instance.
     *
     * In some execution environments FormData is not defined.
     */
    function isFormData(value) {
        return typeof FormData !== 'undefined' && value instanceof FormData;
    }
    /**
     * An outgoing HTTP request with an optional typed body.
     *
     * `HttpRequest` represents an outgoing request, including URL, method,
     * headers, body, and other request configuration options. Instances should be
     * assumed to be immutable. To modify a `HttpRequest`, the `clone`
     * method should be used.
     *
     * @publicApi
     */
    var HttpRequest = /** @class */ (function () {
        function HttpRequest(method, url, third, fourth) {
            this.url = url;
            /**
             * The request body, or `null` if one isn't set.
             *
             * Bodies are not enforced to be immutable, as they can include a reference to any
             * user-defined data type. However, interceptors should take care to preserve
             * idempotence by treating them as such.
             */
            this.body = null;
            /**
             * Whether this request should be made in a way that exposes progress events.
             *
             * Progress events are expensive (change detection runs on each event) and so
             * they should only be requested if the consumer intends to monitor them.
             */
            this.reportProgress = false;
            /**
             * Whether this request should be sent with outgoing credentials (cookies).
             */
            this.withCredentials = false;
            /**
             * The expected response type of the server.
             *
             * This is used to parse the response appropriately before returning it to
             * the requestee.
             */
            this.responseType = 'json';
            this.method = method.toUpperCase();
            // Next, need to figure out which argument holds the HttpRequestInit
            // options, if any.
            var options;
            // Check whether a body argument is expected. The only valid way to omit
            // the body argument is to use a known no-body method like GET.
            if (mightHaveBody(this.method) || !!fourth) {
                // Body is the third argument, options are the fourth.
                this.body = (third !== undefined) ? third : null;
                options = fourth;
            }
            else {
                // No body required, options are the third argument. The body stays null.
                options = third;
            }
            // If options have been passed, interpret them.
            if (options) {
                // Normalize reportProgress and withCredentials.
                this.reportProgress = !!options.reportProgress;
                this.withCredentials = !!options.withCredentials;
                // Override default response type of 'json' if one is provided.
                if (!!options.responseType) {
                    this.responseType = options.responseType;
                }
                // Override headers if they're provided.
                if (!!options.headers) {
                    this.headers = options.headers;
                }
                if (!!options.params) {
                    this.params = options.params;
                }
            }
            // If no headers have been passed in, construct a new HttpHeaders instance.
            if (!this.headers) {
                this.headers = new HttpHeaders();
            }
            // If no parameters have been passed in, construct a new HttpUrlEncodedParams instance.
            if (!this.params) {
                this.params = new HttpParams();
                this.urlWithParams = url;
            }
            else {
                // Encode the parameters to a string in preparation for inclusion in the URL.
                var params = this.params.toString();
                if (params.length === 0) {
                    // No parameters, the visible URL is just the URL given at creation time.
                    this.urlWithParams = url;
                }
                else {
                    // Does the URL already have query parameters? Look for '?'.
                    var qIdx = url.indexOf('?');
                    // There are 3 cases to handle:
                    // 1) No existing parameters -> append '?' followed by params.
                    // 2) '?' exists and is followed by existing query string ->
                    //    append '&' followed by params.
                    // 3) '?' exists at the end of the url -> append params directly.
                    // This basically amounts to determining the character, if any, with
                    // which to join the URL and parameters.
                    var sep = qIdx === -1 ? '?' : (qIdx < url.length - 1 ? '&' : '');
                    this.urlWithParams = url + sep + params;
                }
            }
        }
        /**
         * Transform the free-form body into a serialized format suitable for
         * transmission to the server.
         */
        HttpRequest.prototype.serializeBody = function () {
            // If no body is present, no need to serialize it.
            if (this.body === null) {
                return null;
            }
            // Check whether the body is already in a serialized form. If so,
            // it can just be returned directly.
            if (isArrayBuffer(this.body) || isBlob(this.body) || isFormData(this.body) ||
                typeof this.body === 'string') {
                return this.body;
            }
            // Check whether the body is an instance of HttpUrlEncodedParams.
            if (this.body instanceof HttpParams) {
                return this.body.toString();
            }
            // Check whether the body is an object or array, and serialize with JSON if so.
            if (typeof this.body === 'object' || typeof this.body === 'boolean' ||
                Array.isArray(this.body)) {
                return JSON.stringify(this.body);
            }
            // Fall back on toString() for everything else.
            return this.body.toString();
        };
        /**
         * Examine the body and attempt to infer an appropriate MIME type
         * for it.
         *
         * If no such type can be inferred, this method will return `null`.
         */
        HttpRequest.prototype.detectContentTypeHeader = function () {
            // An empty body has no content type.
            if (this.body === null) {
                return null;
            }
            // FormData bodies rely on the browser's content type assignment.
            if (isFormData(this.body)) {
                return null;
            }
            // Blobs usually have their own content type. If it doesn't, then
            // no type can be inferred.
            if (isBlob(this.body)) {
                return this.body.type || null;
            }
            // Array buffers have unknown contents and thus no type can be inferred.
            if (isArrayBuffer(this.body)) {
                return null;
            }
            // Technically, strings could be a form of JSON data, but it's safe enough
            // to assume they're plain strings.
            if (typeof this.body === 'string') {
                return 'text/plain';
            }
            // `HttpUrlEncodedParams` has its own content-type.
            if (this.body instanceof HttpParams) {
                return 'application/x-www-form-urlencoded;charset=UTF-8';
            }
            // Arrays, objects, and numbers will be encoded as JSON.
            if (typeof this.body === 'object' || typeof this.body === 'number' ||
                Array.isArray(this.body)) {
                return 'application/json';
            }
            // No type could be inferred.
            return null;
        };
        HttpRequest.prototype.clone = function (update) {
            if (update === void 0) { update = {}; }
            // For method, url, and responseType, take the current value unless
            // it is overridden in the update hash.
            var method = update.method || this.method;
            var url = update.url || this.url;
            var responseType = update.responseType || this.responseType;
            // The body is somewhat special - a `null` value in update.body means
            // whatever current body is present is being overridden with an empty
            // body, whereas an `undefined` value in update.body implies no
            // override.
            var body = (update.body !== undefined) ? update.body : this.body;
            // Carefully handle the boolean options to differentiate between
            // `false` and `undefined` in the update args.
            var withCredentials = (update.withCredentials !== undefined) ? update.withCredentials : this.withCredentials;
            var reportProgress = (update.reportProgress !== undefined) ? update.reportProgress : this.reportProgress;
            // Headers and params may be appended to if `setHeaders` or
            // `setParams` are used.
            var headers = update.headers || this.headers;
            var params = update.params || this.params;
            // Check whether the caller has asked to add headers.
            if (update.setHeaders !== undefined) {
                // Set every requested header.
                headers =
                    Object.keys(update.setHeaders)
                        .reduce(function (headers, name) { return headers.set(name, update.setHeaders[name]); }, headers);
            }
            // Check whether the caller has asked to set params.
            if (update.setParams) {
                // Set every requested param.
                params = Object.keys(update.setParams)
                    .reduce(function (params, param) { return params.set(param, update.setParams[param]); }, params);
            }
            // Finally, construct the new HttpRequest using the pieces from above.
            return new HttpRequest(method, url, body, {
                params: params,
                headers: headers,
                reportProgress: reportProgress,
                responseType: responseType,
                withCredentials: withCredentials,
            });
        };
        return HttpRequest;
    }());

    (function (HttpEventType) {
        /**
         * The request was sent out over the wire.
         */
        HttpEventType[HttpEventType["Sent"] = 0] = "Sent";
        /**
         * An upload progress event was received.
         */
        HttpEventType[HttpEventType["UploadProgress"] = 1] = "UploadProgress";
        /**
         * The response status code and headers were received.
         */
        HttpEventType[HttpEventType["ResponseHeader"] = 2] = "ResponseHeader";
        /**
         * A download progress event was received.
         */
        HttpEventType[HttpEventType["DownloadProgress"] = 3] = "DownloadProgress";
        /**
         * The full response including the body was received.
         */
        HttpEventType[HttpEventType["Response"] = 4] = "Response";
        /**
         * A custom event from an interceptor or a backend.
         */
        HttpEventType[HttpEventType["User"] = 5] = "User";
    })(exports.HttpEventType || (exports.HttpEventType = {}));
    /**
     * Base class for both `HttpResponse` and `HttpHeaderResponse`.
     *
     * @publicApi
     */
    var HttpResponseBase = /** @class */ (function () {
        /**
         * Super-constructor for all responses.
         *
         * The single parameter accepted is an initialization hash. Any properties
         * of the response passed there will override the default values.
         */
        function HttpResponseBase(init, defaultStatus, defaultStatusText) {
            if (defaultStatus === void 0) { defaultStatus = 200; }
            if (defaultStatusText === void 0) { defaultStatusText = 'OK'; }
            // If the hash has values passed, use them to initialize the response.
            // Otherwise use the default values.
            this.headers = init.headers || new HttpHeaders();
            this.status = init.status !== undefined ? init.status : defaultStatus;
            this.statusText = init.statusText || defaultStatusText;
            this.url = init.url || null;
            // Cache the ok value to avoid defining a getter.
            this.ok = this.status >= 200 && this.status < 300;
        }
        return HttpResponseBase;
    }());
    /**
     * A partial HTTP response which only includes the status and header data,
     * but no response body.
     *
     * `HttpHeaderResponse` is a `HttpEvent` available on the response
     * event stream, only when progress events are requested.
     *
     * @publicApi
     */
    var HttpHeaderResponse = /** @class */ (function (_super) {
        __extends(HttpHeaderResponse, _super);
        /**
         * Create a new `HttpHeaderResponse` with the given parameters.
         */
        function HttpHeaderResponse(init) {
            if (init === void 0) { init = {}; }
            var _this = _super.call(this, init) || this;
            _this.type = exports.HttpEventType.ResponseHeader;
            return _this;
        }
        /**
         * Copy this `HttpHeaderResponse`, overriding its contents with the
         * given parameter hash.
         */
        HttpHeaderResponse.prototype.clone = function (update) {
            if (update === void 0) { update = {}; }
            // Perform a straightforward initialization of the new HttpHeaderResponse,
            // overriding the current parameters with new ones if given.
            return new HttpHeaderResponse({
                headers: update.headers || this.headers,
                status: update.status !== undefined ? update.status : this.status,
                statusText: update.statusText || this.statusText,
                url: update.url || this.url || undefined,
            });
        };
        return HttpHeaderResponse;
    }(HttpResponseBase));
    /**
     * A full HTTP response, including a typed response body (which may be `null`
     * if one was not returned).
     *
     * `HttpResponse` is a `HttpEvent` available on the response event
     * stream.
     *
     * @publicApi
     */
    var HttpResponse = /** @class */ (function (_super) {
        __extends(HttpResponse, _super);
        /**
         * Construct a new `HttpResponse`.
         */
        function HttpResponse(init) {
            if (init === void 0) { init = {}; }
            var _this = _super.call(this, init) || this;
            _this.type = exports.HttpEventType.Response;
            _this.body = init.body !== undefined ? init.body : null;
            return _this;
        }
        HttpResponse.prototype.clone = function (update) {
            if (update === void 0) { update = {}; }
            return new HttpResponse({
                body: (update.body !== undefined) ? update.body : this.body,
                headers: update.headers || this.headers,
                status: (update.status !== undefined) ? update.status : this.status,
                statusText: update.statusText || this.statusText,
                url: update.url || this.url || undefined,
            });
        };
        return HttpResponse;
    }(HttpResponseBase));
    /**
     * A response that represents an error or failure, either from a
     * non-successful HTTP status, an error while executing the request,
     * or some other failure which occurred during the parsing of the response.
     *
     * Any error returned on the `Observable` response stream will be
     * wrapped in an `HttpErrorResponse` to provide additional context about
     * the state of the HTTP layer when the error occurred. The error property
     * will contain either a wrapped Error object or the error response returned
     * from the server.
     *
     * @publicApi
     */
    var HttpErrorResponse = /** @class */ (function (_super) {
        __extends(HttpErrorResponse, _super);
        function HttpErrorResponse(init) {
            var _this = 
            // Initialize with a default status of 0 / Unknown Error.
            _super.call(this, init, 0, 'Unknown Error') || this;
            _this.name = 'HttpErrorResponse';
            /**
             * Errors are never okay, even when the status code is in the 2xx success range.
             */
            _this.ok = false;
            // If the response was successful, then this was a parse error. Otherwise, it was
            // a protocol-level failure of some sort. Either the request failed in transit
            // or the server returned an unsuccessful status code.
            if (_this.status >= 200 && _this.status < 300) {
                _this.message = "Http failure during parsing for " + (init.url || '(unknown url)');
            }
            else {
                _this.message = "Http failure response for " + (init.url || '(unknown url)') + ": " + init.status + " " + init.statusText;
            }
            _this.error = init.error || null;
            return _this;
        }
        return HttpErrorResponse;
    }(HttpResponseBase));

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Constructs an instance of `HttpRequestOptions<T>` from a source `HttpMethodOptions` and
     * the given `body`. This function clones the object and adds the body.
     *
     * Note that the `responseType` *options* value is a String that identifies the
     * single data type of the response.
     * A single overload version of the method handles each response type.
     * The value of `responseType` cannot be a union, as the combined signature could imply.
     *
     */
    function addBody(options, body) {
        return {
            body: body,
            headers: options.headers,
            observe: options.observe,
            params: options.params,
            reportProgress: options.reportProgress,
            responseType: options.responseType,
            withCredentials: options.withCredentials,
        };
    }
    /**
     * Performs HTTP requests.
     * This service is available as an injectable class, with methods to perform HTTP requests.
     * Each request method has multiple signatures, and the return type varies based on
     * the signature that is called (mainly the values of `observe` and `responseType`).
     *
     * Note that the `responseType` *options* value is a String that identifies the
     * single data type of the response.
     * A single overload version of the method handles each response type.
     * The value of `responseType` cannot be a union, as the combined signature could imply.

     *
     * @usageNotes
     * Sample HTTP requests for the [Tour of Heroes](/tutorial/toh-pt0) application.
     *
     * ### HTTP Request Example
     *
     * ```
     *  // GET heroes whose name contains search term
     * searchHeroes(term: string): observable<Hero[]>{
     *
     *  const params = new HttpParams({fromString: 'name=term'});
     *    return this.httpClient.request('GET', this.heroesUrl, {responseType:'json', params});
     * }
     * ```
     *
     * Alternatively, the parameter string can be used without invoking HttpParams
     * by directly joining to the URL.
     * ```
     * this.httpClient.request('GET', this.heroesUrl + '?' + 'name=term', {responseType:'json'});
     * ```
     *
     *
     * ### JSONP Example
     * ```
     * requestJsonp(url, callback = 'callback') {
     *  return this.httpClient.jsonp(this.heroesURL, callback);
     * }
     * ```
     *
     * ### PATCH Example
     * ```
     * // PATCH one of the heroes' name
     * patchHero (id: number, heroName: string): Observable<{}> {
     * const url = `${this.heroesUrl}/${id}`;   // PATCH api/heroes/42
     *  return this.httpClient.patch(url, {name: heroName}, httpOptions)
     *    .pipe(catchError(this.handleError('patchHero')));
     * }
     * ```
     *
     * @see [HTTP Guide](guide/http)
     * @see [HTTP Request](api/common/http/HttpRequest)
     *
     * @publicApi
     */
    var HttpClient = /** @class */ (function () {
        function HttpClient(handler) {
            this.handler = handler;
        }
        /**
         * Constructs an observable for a generic HTTP request that, when subscribed,
         * fires the request through the chain of registered interceptors and on to the
         * server.
         *
         * You can pass an `HttpRequest` directly as the only parameter. In this case,
         * the call returns an observable of the raw `HttpEvent` stream.
         *
         * Alternatively you can pass an HTTP method as the first parameter,
         * a URL string as the second, and an options hash containing the request body as the third.
         * See `addBody()`. In this case, the specified `responseType` and `observe` options determine the
         * type of returned observable.
         *   * The `responseType` value determines how a successful response body is parsed.
         *   * If `responseType` is the default `json`, you can pass a type interface for the resulting
         * object as a type parameter to the call.
         *
         * The `observe` value determines the return type, according to what you are interested in
         * observing.
         *   * An `observe` value of events returns an observable of the raw `HttpEvent` stream, including
         * progress events by default.
         *   * An `observe` value of response returns an observable of `HttpResponse<T>`,
         * where the `T` parameter depends on the `responseType` and any optionally provided type
         * parameter.
         *   * An `observe` value of body returns an observable of `<T>` with the same `T` body type.
         *
         */
        HttpClient.prototype.request = function (first, url, options) {
            var _this = this;
            if (options === void 0) { options = {}; }
            var req;
            // First, check whether the primary argument is an instance of `HttpRequest`.
            if (first instanceof HttpRequest) {
                // It is. The other arguments must be undefined (per the signatures) and can be
                // ignored.
                req = first;
            }
            else {
                // It's a string, so it represents a URL. Construct a request based on it,
                // and incorporate the remaining arguments (assuming `GET` unless a method is
                // provided.
                // Figure out the headers.
                var headers = undefined;
                if (options.headers instanceof HttpHeaders) {
                    headers = options.headers;
                }
                else {
                    headers = new HttpHeaders(options.headers);
                }
                // Sort out parameters.
                var params = undefined;
                if (!!options.params) {
                    if (options.params instanceof HttpParams) {
                        params = options.params;
                    }
                    else {
                        params = new HttpParams({ fromObject: options.params });
                    }
                }
                // Construct the request.
                req = new HttpRequest(first, url, (options.body !== undefined ? options.body : null), {
                    headers: headers,
                    params: params,
                    reportProgress: options.reportProgress,
                    // By default, JSON is assumed to be returned for all calls.
                    responseType: options.responseType || 'json',
                    withCredentials: options.withCredentials,
                });
            }
            // Start with an Observable.of() the initial request, and run the handler (which
            // includes all interceptors) inside a concatMap(). This way, the handler runs
            // inside an Observable chain, which causes interceptors to be re-run on every
            // subscription (this also makes retries re-run the handler, including interceptors).
            var events$ = rxjs.of(req).pipe(operators.concatMap(function (req) { return _this.handler.handle(req); }));
            // If coming via the API signature which accepts a previously constructed HttpRequest,
            // the only option is to get the event stream. Otherwise, return the event stream if
            // that is what was requested.
            if (first instanceof HttpRequest || options.observe === 'events') {
                return events$;
            }
            // The requested stream contains either the full response or the body. In either
            // case, the first step is to filter the event stream to extract a stream of
            // responses(s).
            var res$ = events$.pipe(operators.filter(function (event) { return event instanceof HttpResponse; }));
            // Decide which stream to return.
            switch (options.observe || 'body') {
                case 'body':
                    // The requested stream is the body. Map the response stream to the response
                    // body. This could be done more simply, but a misbehaving interceptor might
                    // transform the response body into a different format and ignore the requested
                    // responseType. Guard against this by validating that the response is of the
                    // requested type.
                    switch (req.responseType) {
                        case 'arraybuffer':
                            return res$.pipe(operators.map(function (res) {
                                // Validate that the body is an ArrayBuffer.
                                if (res.body !== null && !(res.body instanceof ArrayBuffer)) {
                                    throw new Error('Response is not an ArrayBuffer.');
                                }
                                return res.body;
                            }));
                        case 'blob':
                            return res$.pipe(operators.map(function (res) {
                                // Validate that the body is a Blob.
                                if (res.body !== null && !(res.body instanceof Blob)) {
                                    throw new Error('Response is not a Blob.');
                                }
                                return res.body;
                            }));
                        case 'text':
                            return res$.pipe(operators.map(function (res) {
                                // Validate that the body is a string.
                                if (res.body !== null && typeof res.body !== 'string') {
                                    throw new Error('Response is not a string.');
                                }
                                return res.body;
                            }));
                        case 'json':
                        default:
                            // No validation needed for JSON responses, as they can be of any type.
                            return res$.pipe(operators.map(function (res) { return res.body; }));
                    }
                case 'response':
                    // The response stream was requested directly, so return it.
                    return res$;
                default:
                    // Guard against new future observe types being added.
                    throw new Error("Unreachable: unhandled observe type " + options.observe + "}");
            }
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `DELETE` request to execute on the server. See the individual overloads for
         * details on the return type.
         *
         * @param url     The endpoint URL.
         * @param options The HTTP options to send with the request.
         *
         */
        HttpClient.prototype.delete = function (url, options) {
            if (options === void 0) { options = {}; }
            return this.request('DELETE', url, options);
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `GET` request to execute on the server. See the individual overloads for
         * details on the return type.
         */
        HttpClient.prototype.get = function (url, options) {
            if (options === void 0) { options = {}; }
            return this.request('GET', url, options);
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `HEAD` request to execute on the server. The `HEAD` method returns
         * meta information about the resource without transferring the
         * resource itself. See the individual overloads for
         * details on the return type.
         */
        HttpClient.prototype.head = function (url, options) {
            if (options === void 0) { options = {}; }
            return this.request('HEAD', url, options);
        };
        /**
         * Constructs an `Observable` that, when subscribed, causes a request with the special method
         * `JSONP` to be dispatched via the interceptor pipeline.
         * The [JSONP pattern](https://en.wikipedia.org/wiki/JSONP) works around limitations of certain
         * API endpoints that don't support newer,
         * and preferable [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS) protocol.
         * JSONP treats the endpoint API as a JavaScript file and tricks the browser to process the
         * requests even if the API endpoint is not located on the same domain (origin) as the client-side
         * application making the request.
         * The endpoint API must support JSONP callback for JSONP requests to work.
         * The resource API returns the JSON response wrapped in a callback function.
         * You can pass the callback function name as one of the query parameters.
         * Note that JSONP requests can only be used with `GET` requests.
         *
         * @param url The resource URL.
         * @param callbackParam The callback function name.
         *
         */
        HttpClient.prototype.jsonp = function (url, callbackParam) {
            return this.request('JSONP', url, {
                params: new HttpParams().append(callbackParam, 'JSONP_CALLBACK'),
                observe: 'body',
                responseType: 'json',
            });
        };
        /**
         * Constructs an `Observable` that, when subscribed, causes the configured
         * `OPTIONS` request to execute on the server. This method allows the client
         * to determine the supported HTTP methods and other capabilites of an endpoint,
         * without implying a resource action. See the individual overloads for
         * details on the return type.
         */
        HttpClient.prototype.options = function (url, options) {
            if (options === void 0) { options = {}; }
            return this.request('OPTIONS', url, options);
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `PATCH` request to execute on the server. See the individual overloads for
         * details on the return type.
         */
        HttpClient.prototype.patch = function (url, body, options) {
            if (options === void 0) { options = {}; }
            return this.request('PATCH', url, addBody(options, body));
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `POST` request to execute on the server. The server responds with the location of
         * the replaced resource. See the individual overloads for
         * details on the return type.
         */
        HttpClient.prototype.post = function (url, body, options) {
            if (options === void 0) { options = {}; }
            return this.request('POST', url, addBody(options, body));
        };
        /**
         * Constructs an observable that, when subscribed, causes the configured
         * `PUT` request to execute on the server. The `PUT` method replaces an existing resource
         * with a new set of values.
         * See the individual overloads for details on the return type.
         */
        HttpClient.prototype.put = function (url, body, options) {
            if (options === void 0) { options = {}; }
            return this.request('PUT', url, addBody(options, body));
        };
        return HttpClient;
    }());
    HttpClient.decorators = [
        { type: core.Injectable }
    ];
    HttpClient.ctorParameters = function () { return [
        { type: HttpHandler }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * `HttpHandler` which applies an `HttpInterceptor` to an `HttpRequest`.
     *
     *
     */
    var HttpInterceptorHandler = /** @class */ (function () {
        function HttpInterceptorHandler(next, interceptor) {
            this.next = next;
            this.interceptor = interceptor;
        }
        HttpInterceptorHandler.prototype.handle = function (req) {
            return this.interceptor.intercept(req, this.next);
        };
        return HttpInterceptorHandler;
    }());
    /**
     * A multi-provider token that represents the array of registered
     * `HttpInterceptor` objects.
     *
     * @publicApi
     */
    var HTTP_INTERCEPTORS = new core.InjectionToken('HTTP_INTERCEPTORS');
    var NoopInterceptor = /** @class */ (function () {
        function NoopInterceptor() {
        }
        NoopInterceptor.prototype.intercept = function (req, next) {
            return next.handle(req);
        };
        return NoopInterceptor;
    }());
    NoopInterceptor.decorators = [
        { type: core.Injectable }
    ];

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    // Every request made through JSONP needs a callback name that's unique across the
    // whole page. Each request is assigned an id and the callback name is constructed
    // from that. The next id to be assigned is tracked in a global variable here that
    // is shared among all applications on the page.
    var nextRequestId = 0;
    // Error text given when a JSONP script is injected, but doesn't invoke the callback
    // passed in its URL.
    var JSONP_ERR_NO_CALLBACK = 'JSONP injected script did not invoke callback.';
    // Error text given when a request is passed to the JsonpClientBackend that doesn't
    // have a request method JSONP.
    var JSONP_ERR_WRONG_METHOD = 'JSONP requests must use JSONP request method.';
    var JSONP_ERR_WRONG_RESPONSE_TYPE = 'JSONP requests must use Json response type.';
    /**
     * DI token/abstract type representing a map of JSONP callbacks.
     *
     * In the browser, this should always be the `window` object.
     *
     *
     */
    var JsonpCallbackContext = /** @class */ (function () {
        function JsonpCallbackContext() {
        }
        return JsonpCallbackContext;
    }());
    /**
     * Processes an `HttpRequest` with the JSONP method,
     * by performing JSONP style requests.
     * @see `HttpHandler`
     * @see `HttpXhrBackend`
     *
     * @publicApi
     */
    var JsonpClientBackend = /** @class */ (function () {
        function JsonpClientBackend(callbackMap, document) {
            this.callbackMap = callbackMap;
            this.document = document;
            /**
             * A resolved promise that can be used to schedule microtasks in the event handlers.
             */
            this.resolvedPromise = Promise.resolve();
        }
        /**
         * Get the name of the next callback method, by incrementing the global `nextRequestId`.
         */
        JsonpClientBackend.prototype.nextCallback = function () {
            return "ng_jsonp_callback_" + nextRequestId++;
        };
        /**
         * Processes a JSONP request and returns an event stream of the results.
         * @param req The request object.
         * @returns An observable of the response events.
         *
         */
        JsonpClientBackend.prototype.handle = function (req) {
            var _this = this;
            // Firstly, check both the method and response type. If either doesn't match
            // then the request was improperly routed here and cannot be handled.
            if (req.method !== 'JSONP') {
                throw new Error(JSONP_ERR_WRONG_METHOD);
            }
            else if (req.responseType !== 'json') {
                throw new Error(JSONP_ERR_WRONG_RESPONSE_TYPE);
            }
            // Everything else happens inside the Observable boundary.
            return new rxjs.Observable(function (observer) {
                // The first step to make a request is to generate the callback name, and replace the
                // callback placeholder in the URL with the name. Care has to be taken here to ensure
                // a trailing &, if matched, gets inserted back into the URL in the correct place.
                var callback = _this.nextCallback();
                var url = req.urlWithParams.replace(/=JSONP_CALLBACK(&|$)/, "=" + callback + "$1");
                // Construct the <script> tag and point it at the URL.
                var node = _this.document.createElement('script');
                node.src = url;
                // A JSONP request requires waiting for multiple callbacks. These variables
                // are closed over and track state across those callbacks.
                // The response object, if one has been received, or null otherwise.
                var body = null;
                // Whether the response callback has been called.
                var finished = false;
                // Whether the request has been cancelled (and thus any other callbacks)
                // should be ignored.
                var cancelled = false;
                // Set the response callback in this.callbackMap (which will be the window
                // object in the browser. The script being loaded via the <script> tag will
                // eventually call this callback.
                _this.callbackMap[callback] = function (data) {
                    // Data has been received from the JSONP script. Firstly, delete this callback.
                    delete _this.callbackMap[callback];
                    // Next, make sure the request wasn't cancelled in the meantime.
                    if (cancelled) {
                        return;
                    }
                    // Set state to indicate data was received.
                    body = data;
                    finished = true;
                };
                // cleanup() is a utility closure that removes the <script> from the page and
                // the response callback from the window. This logic is used in both the
                // success, error, and cancellation paths, so it's extracted out for convenience.
                var cleanup = function () {
                    // Remove the <script> tag if it's still on the page.
                    if (node.parentNode) {
                        node.parentNode.removeChild(node);
                    }
                    // Remove the response callback from the callbackMap (window object in the
                    // browser).
                    delete _this.callbackMap[callback];
                };
                // onLoad() is the success callback which runs after the response callback
                // if the JSONP script loads successfully. The event itself is unimportant.
                // If something went wrong, onLoad() may run without the response callback
                // having been invoked.
                var onLoad = function (event) {
                    // Do nothing if the request has been cancelled.
                    if (cancelled) {
                        return;
                    }
                    // We wrap it in an extra Promise, to ensure the microtask
                    // is scheduled after the loaded endpoint has executed any potential microtask itself,
                    // which is not guaranteed in Internet Explorer and EdgeHTML. See issue #39496
                    _this.resolvedPromise.then(function () {
                        // Cleanup the page.
                        cleanup();
                        // Check whether the response callback has run.
                        if (!finished) {
                            // It hasn't, something went wrong with the request. Return an error via
                            // the Observable error path. All JSONP errors have status 0.
                            observer.error(new HttpErrorResponse({
                                url: url,
                                status: 0,
                                statusText: 'JSONP Error',
                                error: new Error(JSONP_ERR_NO_CALLBACK),
                            }));
                            return;
                        }
                        // Success. body either contains the response body or null if none was
                        // returned.
                        observer.next(new HttpResponse({
                            body: body,
                            status: 200,
                            statusText: 'OK',
                            url: url,
                        }));
                        // Complete the stream, the response is over.
                        observer.complete();
                    });
                };
                // onError() is the error callback, which runs if the script returned generates
                // a Javascript error. It emits the error via the Observable error channel as
                // a HttpErrorResponse.
                var onError = function (error) {
                    // If the request was already cancelled, no need to emit anything.
                    if (cancelled) {
                        return;
                    }
                    cleanup();
                    // Wrap the error in a HttpErrorResponse.
                    observer.error(new HttpErrorResponse({
                        error: error,
                        status: 0,
                        statusText: 'JSONP Error',
                        url: url,
                    }));
                };
                // Subscribe to both the success (load) and error events on the <script> tag,
                // and add it to the page.
                node.addEventListener('load', onLoad);
                node.addEventListener('error', onError);
                _this.document.body.appendChild(node);
                // The request has now been successfully sent.
                observer.next({ type: exports.HttpEventType.Sent });
                // Cancellation handler.
                return function () {
                    // Track the cancellation so event listeners won't do anything even if already scheduled.
                    cancelled = true;
                    // Remove the event listeners so they won't run if the events later fire.
                    node.removeEventListener('load', onLoad);
                    node.removeEventListener('error', onError);
                    // And finally, clean up the page.
                    cleanup();
                };
            });
        };
        return JsonpClientBackend;
    }());
    JsonpClientBackend.decorators = [
        { type: core.Injectable }
    ];
    JsonpClientBackend.ctorParameters = function () { return [
        { type: JsonpCallbackContext },
        { type: undefined, decorators: [{ type: core.Inject, args: [common.DOCUMENT,] }] }
    ]; };
    /**
     * Identifies requests with the method JSONP and
     * shifts them to the `JsonpClientBackend`.
     *
     * @see `HttpInterceptor`
     *
     * @publicApi
     */
    var JsonpInterceptor = /** @class */ (function () {
        function JsonpInterceptor(jsonp) {
            this.jsonp = jsonp;
        }
        /**
         * Identifies and handles a given JSONP request.
         * @param req The outgoing request object to handle.
         * @param next The next interceptor in the chain, or the backend
         * if no interceptors remain in the chain.
         * @returns An observable of the event stream.
         */
        JsonpInterceptor.prototype.intercept = function (req, next) {
            if (req.method === 'JSONP') {
                return this.jsonp.handle(req);
            }
            // Fall through for normal HTTP requests.
            return next.handle(req);
        };
        return JsonpInterceptor;
    }());
    JsonpInterceptor.decorators = [
        { type: core.Injectable }
    ];
    JsonpInterceptor.ctorParameters = function () { return [
        { type: JsonpClientBackend }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var XSSI_PREFIX = /^\)\]\}',?\n/;
    /**
     * Determine an appropriate URL for the response, by checking either
     * XMLHttpRequest.responseURL or the X-Request-URL header.
     */
    function getResponseUrl(xhr) {
        if ('responseURL' in xhr && xhr.responseURL) {
            return xhr.responseURL;
        }
        if (/^X-Request-URL:/m.test(xhr.getAllResponseHeaders())) {
            return xhr.getResponseHeader('X-Request-URL');
        }
        return null;
    }
    /**
     * A wrapper around the `XMLHttpRequest` constructor.
     *
     * @publicApi
     */
    var XhrFactory = /** @class */ (function () {
        function XhrFactory() {
        }
        return XhrFactory;
    }());
    /**
     * A factory for `HttpXhrBackend` that uses the `XMLHttpRequest` browser API.
     *
     */
    var BrowserXhr = /** @class */ (function () {
        function BrowserXhr() {
        }
        BrowserXhr.prototype.build = function () {
            return (new XMLHttpRequest());
        };
        return BrowserXhr;
    }());
    BrowserXhr.decorators = [
        { type: core.Injectable }
    ];
    BrowserXhr.ctorParameters = function () { return []; };
    /**
     * Uses `XMLHttpRequest` to send requests to a backend server.
     * @see `HttpHandler`
     * @see `JsonpClientBackend`
     *
     * @publicApi
     */
    var HttpXhrBackend = /** @class */ (function () {
        function HttpXhrBackend(xhrFactory) {
            this.xhrFactory = xhrFactory;
        }
        /**
         * Processes a request and returns a stream of response events.
         * @param req The request object.
         * @returns An observable of the response events.
         */
        HttpXhrBackend.prototype.handle = function (req) {
            var _this = this;
            // Quick check to give a better error message when a user attempts to use
            // HttpClient.jsonp() without installing the HttpClientJsonpModule
            if (req.method === 'JSONP') {
                throw new Error("Attempted to construct Jsonp request without HttpClientJsonpModule installed.");
            }
            // Everything happens on Observable subscription.
            return new rxjs.Observable(function (observer) {
                // Start by setting up the XHR object with request method, URL, and withCredentials flag.
                var xhr = _this.xhrFactory.build();
                xhr.open(req.method, req.urlWithParams);
                if (!!req.withCredentials) {
                    xhr.withCredentials = true;
                }
                // Add all the requested headers.
                req.headers.forEach(function (name, values) { return xhr.setRequestHeader(name, values.join(',')); });
                // Add an Accept header if one isn't present already.
                if (!req.headers.has('Accept')) {
                    xhr.setRequestHeader('Accept', 'application/json, text/plain, */*');
                }
                // Auto-detect the Content-Type header if one isn't present already.
                if (!req.headers.has('Content-Type')) {
                    var detectedType = req.detectContentTypeHeader();
                    // Sometimes Content-Type detection fails.
                    if (detectedType !== null) {
                        xhr.setRequestHeader('Content-Type', detectedType);
                    }
                }
                // Set the responseType if one was requested.
                if (req.responseType) {
                    var responseType = req.responseType.toLowerCase();
                    // JSON responses need to be processed as text. This is because if the server
                    // returns an XSSI-prefixed JSON response, the browser will fail to parse it,
                    // xhr.response will be null, and xhr.responseText cannot be accessed to
                    // retrieve the prefixed JSON data in order to strip the prefix. Thus, all JSON
                    // is parsed by first requesting text and then applying JSON.parse.
                    xhr.responseType = ((responseType !== 'json') ? responseType : 'text');
                }
                // Serialize the request body if one is present. If not, this will be set to null.
                var reqBody = req.serializeBody();
                // If progress events are enabled, response headers will be delivered
                // in two events - the HttpHeaderResponse event and the full HttpResponse
                // event. However, since response headers don't change in between these
                // two events, it doesn't make sense to parse them twice. So headerResponse
                // caches the data extracted from the response whenever it's first parsed,
                // to ensure parsing isn't duplicated.
                var headerResponse = null;
                // partialFromXhr extracts the HttpHeaderResponse from the current XMLHttpRequest
                // state, and memoizes it into headerResponse.
                var partialFromXhr = function () {
                    if (headerResponse !== null) {
                        return headerResponse;
                    }
                    // Read status and normalize an IE9 bug (https://bugs.jquery.com/ticket/1450).
                    var status = xhr.status === 1223 ? 204 : xhr.status;
                    var statusText = xhr.statusText || 'OK';
                    // Parse headers from XMLHttpRequest - this step is lazy.
                    var headers = new HttpHeaders(xhr.getAllResponseHeaders());
                    // Read the response URL from the XMLHttpResponse instance and fall back on the
                    // request URL.
                    var url = getResponseUrl(xhr) || req.url;
                    // Construct the HttpHeaderResponse and memoize it.
                    headerResponse = new HttpHeaderResponse({ headers: headers, status: status, statusText: statusText, url: url });
                    return headerResponse;
                };
                // Next, a few closures are defined for the various events which XMLHttpRequest can
                // emit. This allows them to be unregistered as event listeners later.
                // First up is the load event, which represents a response being fully available.
                var onLoad = function () {
                    // Read response state from the memoized partial data.
                    var _a = partialFromXhr(), headers = _a.headers, status = _a.status, statusText = _a.statusText, url = _a.url;
                    // The body will be read out if present.
                    var body = null;
                    if (status !== 204) {
                        // Use XMLHttpRequest.response if set, responseText otherwise.
                        body = (typeof xhr.response === 'undefined') ? xhr.responseText : xhr.response;
                    }
                    // Normalize another potential bug (this one comes from CORS).
                    if (status === 0) {
                        status = !!body ? 200 : 0;
                    }
                    // ok determines whether the response will be transmitted on the event or
                    // error channel. Unsuccessful status codes (not 2xx) will always be errors,
                    // but a successful status code can still result in an error if the user
                    // asked for JSON data and the body cannot be parsed as such.
                    var ok = status >= 200 && status < 300;
                    // Check whether the body needs to be parsed as JSON (in many cases the browser
                    // will have done that already).
                    if (req.responseType === 'json' && typeof body === 'string') {
                        // Save the original body, before attempting XSSI prefix stripping.
                        var originalBody = body;
                        body = body.replace(XSSI_PREFIX, '');
                        try {
                            // Attempt the parse. If it fails, a parse error should be delivered to the user.
                            body = body !== '' ? JSON.parse(body) : null;
                        }
                        catch (error) {
                            // Since the JSON.parse failed, it's reasonable to assume this might not have been a
                            // JSON response. Restore the original body (including any XSSI prefix) to deliver
                            // a better error response.
                            body = originalBody;
                            // If this was an error request to begin with, leave it as a string, it probably
                            // just isn't JSON. Otherwise, deliver the parsing error to the user.
                            if (ok) {
                                // Even though the response status was 2xx, this is still an error.
                                ok = false;
                                // The parse error contains the text of the body that failed to parse.
                                body = { error: error, text: body };
                            }
                        }
                    }
                    if (ok) {
                        // A successful response is delivered on the event stream.
                        observer.next(new HttpResponse({
                            body: body,
                            headers: headers,
                            status: status,
                            statusText: statusText,
                            url: url || undefined,
                        }));
                        // The full body has been received and delivered, no further events
                        // are possible. This request is complete.
                        observer.complete();
                    }
                    else {
                        // An unsuccessful request is delivered on the error channel.
                        observer.error(new HttpErrorResponse({
                            // The error in this case is the response body (error from the server).
                            error: body,
                            headers: headers,
                            status: status,
                            statusText: statusText,
                            url: url || undefined,
                        }));
                    }
                };
                // The onError callback is called when something goes wrong at the network level.
                // Connection timeout, DNS error, offline, etc. These are actual errors, and are
                // transmitted on the error channel.
                var onError = function (error) {
                    var url = partialFromXhr().url;
                    var res = new HttpErrorResponse({
                        error: error,
                        status: xhr.status || 0,
                        statusText: xhr.statusText || 'Unknown Error',
                        url: url || undefined,
                    });
                    observer.error(res);
                };
                // The sentHeaders flag tracks whether the HttpResponseHeaders event
                // has been sent on the stream. This is necessary to track if progress
                // is enabled since the event will be sent on only the first download
                // progerss event.
                var sentHeaders = false;
                // The download progress event handler, which is only registered if
                // progress events are enabled.
                var onDownProgress = function (event) {
                    // Send the HttpResponseHeaders event if it hasn't been sent already.
                    if (!sentHeaders) {
                        observer.next(partialFromXhr());
                        sentHeaders = true;
                    }
                    // Start building the download progress event to deliver on the response
                    // event stream.
                    var progressEvent = {
                        type: exports.HttpEventType.DownloadProgress,
                        loaded: event.loaded,
                    };
                    // Set the total number of bytes in the event if it's available.
                    if (event.lengthComputable) {
                        progressEvent.total = event.total;
                    }
                    // If the request was for text content and a partial response is
                    // available on XMLHttpRequest, include it in the progress event
                    // to allow for streaming reads.
                    if (req.responseType === 'text' && !!xhr.responseText) {
                        progressEvent.partialText = xhr.responseText;
                    }
                    // Finally, fire the event.
                    observer.next(progressEvent);
                };
                // The upload progress event handler, which is only registered if
                // progress events are enabled.
                var onUpProgress = function (event) {
                    // Upload progress events are simpler. Begin building the progress
                    // event.
                    var progress = {
                        type: exports.HttpEventType.UploadProgress,
                        loaded: event.loaded,
                    };
                    // If the total number of bytes being uploaded is available, include
                    // it.
                    if (event.lengthComputable) {
                        progress.total = event.total;
                    }
                    // Send the event.
                    observer.next(progress);
                };
                // By default, register for load and error events.
                xhr.addEventListener('load', onLoad);
                xhr.addEventListener('error', onError);
                xhr.addEventListener('timeout', onError);
                xhr.addEventListener('abort', onError);
                // Progress events are only enabled if requested.
                if (req.reportProgress) {
                    // Download progress is always enabled if requested.
                    xhr.addEventListener('progress', onDownProgress);
                    // Upload progress depends on whether there is a body to upload.
                    if (reqBody !== null && xhr.upload) {
                        xhr.upload.addEventListener('progress', onUpProgress);
                    }
                }
                // Fire the request, and notify the event stream that it was fired.
                xhr.send(reqBody);
                observer.next({ type: exports.HttpEventType.Sent });
                // This is the return from the Observable function, which is the
                // request cancellation handler.
                return function () {
                    // On a cancellation, remove all registered event listeners.
                    xhr.removeEventListener('error', onError);
                    xhr.removeEventListener('abort', onError);
                    xhr.removeEventListener('load', onLoad);
                    xhr.removeEventListener('timeout', onError);
                    if (req.reportProgress) {
                        xhr.removeEventListener('progress', onDownProgress);
                        if (reqBody !== null && xhr.upload) {
                            xhr.upload.removeEventListener('progress', onUpProgress);
                        }
                    }
                    // Finally, abort the in-flight request.
                    if (xhr.readyState !== xhr.DONE) {
                        xhr.abort();
                    }
                };
            });
        };
        return HttpXhrBackend;
    }());
    HttpXhrBackend.decorators = [
        { type: core.Injectable }
    ];
    HttpXhrBackend.ctorParameters = function () { return [
        { type: XhrFactory }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var XSRF_COOKIE_NAME = new core.InjectionToken('XSRF_COOKIE_NAME');
    var XSRF_HEADER_NAME = new core.InjectionToken('XSRF_HEADER_NAME');
    /**
     * Retrieves the current XSRF token to use with the next outgoing request.
     *
     * @publicApi
     */
    var HttpXsrfTokenExtractor = /** @class */ (function () {
        function HttpXsrfTokenExtractor() {
        }
        return HttpXsrfTokenExtractor;
    }());
    /**
     * `HttpXsrfTokenExtractor` which retrieves the token from a cookie.
     */
    var HttpXsrfCookieExtractor = /** @class */ (function () {
        function HttpXsrfCookieExtractor(doc, platform, cookieName) {
            this.doc = doc;
            this.platform = platform;
            this.cookieName = cookieName;
            this.lastCookieString = '';
            this.lastToken = null;
            /**
             * @internal for testing
             */
            this.parseCount = 0;
        }
        HttpXsrfCookieExtractor.prototype.getToken = function () {
            if (this.platform === 'server') {
                return null;
            }
            var cookieString = this.doc.cookie || '';
            if (cookieString !== this.lastCookieString) {
                this.parseCount++;
                this.lastToken = common.ɵparseCookieValue(cookieString, this.cookieName);
                this.lastCookieString = cookieString;
            }
            return this.lastToken;
        };
        return HttpXsrfCookieExtractor;
    }());
    HttpXsrfCookieExtractor.decorators = [
        { type: core.Injectable }
    ];
    HttpXsrfCookieExtractor.ctorParameters = function () { return [
        { type: undefined, decorators: [{ type: core.Inject, args: [common.DOCUMENT,] }] },
        { type: String, decorators: [{ type: core.Inject, args: [core.PLATFORM_ID,] }] },
        { type: String, decorators: [{ type: core.Inject, args: [XSRF_COOKIE_NAME,] }] }
    ]; };
    /**
     * `HttpInterceptor` which adds an XSRF token to eligible outgoing requests.
     */
    var HttpXsrfInterceptor = /** @class */ (function () {
        function HttpXsrfInterceptor(tokenService, headerName) {
            this.tokenService = tokenService;
            this.headerName = headerName;
        }
        HttpXsrfInterceptor.prototype.intercept = function (req, next) {
            var lcUrl = req.url.toLowerCase();
            // Skip both non-mutating requests and absolute URLs.
            // Non-mutating requests don't require a token, and absolute URLs require special handling
            // anyway as the cookie set
            // on our origin is not the same as the token expected by another origin.
            if (req.method === 'GET' || req.method === 'HEAD' || lcUrl.startsWith('http://') ||
                lcUrl.startsWith('https://')) {
                return next.handle(req);
            }
            var token = this.tokenService.getToken();
            // Be careful not to overwrite an existing header of the same name.
            if (token !== null && !req.headers.has(this.headerName)) {
                req = req.clone({ headers: req.headers.set(this.headerName, token) });
            }
            return next.handle(req);
        };
        return HttpXsrfInterceptor;
    }());
    HttpXsrfInterceptor.decorators = [
        { type: core.Injectable }
    ];
    HttpXsrfInterceptor.ctorParameters = function () { return [
        { type: HttpXsrfTokenExtractor },
        { type: String, decorators: [{ type: core.Inject, args: [XSRF_HEADER_NAME,] }] }
    ]; };

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * An injectable `HttpHandler` that applies multiple interceptors
     * to a request before passing it to the given `HttpBackend`.
     *
     * The interceptors are loaded lazily from the injector, to allow
     * interceptors to themselves inject classes depending indirectly
     * on `HttpInterceptingHandler` itself.
     * @see `HttpInterceptor`
     */
    var HttpInterceptingHandler = /** @class */ (function () {
        function HttpInterceptingHandler(backend, injector) {
            this.backend = backend;
            this.injector = injector;
            this.chain = null;
        }
        HttpInterceptingHandler.prototype.handle = function (req) {
            if (this.chain === null) {
                var interceptors = this.injector.get(HTTP_INTERCEPTORS, []);
                this.chain = interceptors.reduceRight(function (next, interceptor) { return new HttpInterceptorHandler(next, interceptor); }, this.backend);
            }
            return this.chain.handle(req);
        };
        return HttpInterceptingHandler;
    }());
    HttpInterceptingHandler.decorators = [
        { type: core.Injectable }
    ];
    HttpInterceptingHandler.ctorParameters = function () { return [
        { type: HttpBackend },
        { type: core.Injector }
    ]; };
    /**
     * Constructs an `HttpHandler` that applies interceptors
     * to a request before passing it to the given `HttpBackend`.
     *
     * Use as a factory function within `HttpClientModule`.
     *
     *
     */
    function interceptingHandler(backend, interceptors) {
        if (interceptors === void 0) { interceptors = []; }
        if (!interceptors) {
            return backend;
        }
        return interceptors.reduceRight(function (next, interceptor) { return new HttpInterceptorHandler(next, interceptor); }, backend);
    }
    /**
     * Factory function that determines where to store JSONP callbacks.
     *
     * Ordinarily JSONP callbacks are stored on the `window` object, but this may not exist
     * in test environments. In that case, callbacks are stored on an anonymous object instead.
     *
     *
     */
    function jsonpCallbackContext() {
        if (typeof window === 'object') {
            return window;
        }
        return {};
    }
    /**
     * Configures XSRF protection support for outgoing requests.
     *
     * For a server that supports a cookie-based XSRF protection system,
     * use directly to configure XSRF protection with the correct
     * cookie and header names.
     *
     * If no names are supplied, the default cookie name is `XSRF-TOKEN`
     * and the default header name is `X-XSRF-TOKEN`.
     *
     * @publicApi
     */
    var HttpClientXsrfModule = /** @class */ (function () {
        function HttpClientXsrfModule() {
        }
        /**
         * Disable the default XSRF protection.
         */
        HttpClientXsrfModule.disable = function () {
            return {
                ngModule: HttpClientXsrfModule,
                providers: [
                    { provide: HttpXsrfInterceptor, useClass: NoopInterceptor },
                ],
            };
        };
        /**
         * Configure XSRF protection.
         * @param options An object that can specify either or both
         * cookie name or header name.
         * - Cookie name default is `XSRF-TOKEN`.
         * - Header name default is `X-XSRF-TOKEN`.
         *
         */
        HttpClientXsrfModule.withOptions = function (options) {
            if (options === void 0) { options = {}; }
            return {
                ngModule: HttpClientXsrfModule,
                providers: [
                    options.cookieName ? { provide: XSRF_COOKIE_NAME, useValue: options.cookieName } : [],
                    options.headerName ? { provide: XSRF_HEADER_NAME, useValue: options.headerName } : [],
                ],
            };
        };
        return HttpClientXsrfModule;
    }());
    HttpClientXsrfModule.decorators = [
        { type: core.NgModule, args: [{
                    providers: [
                        HttpXsrfInterceptor,
                        { provide: HTTP_INTERCEPTORS, useExisting: HttpXsrfInterceptor, multi: true },
                        { provide: HttpXsrfTokenExtractor, useClass: HttpXsrfCookieExtractor },
                        { provide: XSRF_COOKIE_NAME, useValue: 'XSRF-TOKEN' },
                        { provide: XSRF_HEADER_NAME, useValue: 'X-XSRF-TOKEN' },
                    ],
                },] }
    ];
    /**
     * Configures the [dependency injector](guide/glossary#injector) for `HttpClient`
     * with supporting services for XSRF. Automatically imported by `HttpClientModule`.
     *
     * You can add interceptors to the chain behind `HttpClient` by binding them to the
     * multiprovider for built-in [DI token](guide/glossary#di-token) `HTTP_INTERCEPTORS`.
     *
     * @publicApi
     */
    var HttpClientModule = /** @class */ (function () {
        function HttpClientModule() {
        }
        return HttpClientModule;
    }());
    HttpClientModule.decorators = [
        { type: core.NgModule, args: [{
                    /**
                     * Optional configuration for XSRF protection.
                     */
                    imports: [
                        HttpClientXsrfModule.withOptions({
                            cookieName: 'XSRF-TOKEN',
                            headerName: 'X-XSRF-TOKEN',
                        }),
                    ],
                    /**
                     * Configures the [dependency injector](guide/glossary#injector) where it is imported
                     * with supporting services for HTTP communications.
                     */
                    providers: [
                        HttpClient,
                        { provide: HttpHandler, useClass: HttpInterceptingHandler },
                        HttpXhrBackend,
                        { provide: HttpBackend, useExisting: HttpXhrBackend },
                        BrowserXhr,
                        { provide: XhrFactory, useExisting: BrowserXhr },
                    ],
                },] }
    ];
    /**
     * Configures the [dependency injector](guide/glossary#injector) for `HttpClient`
     * with supporting services for JSONP.
     * Without this module, Jsonp requests reach the backend
     * with method JSONP, where they are rejected.
     *
     * You can add interceptors to the chain behind `HttpClient` by binding them to the
     * multiprovider for built-in [DI token](guide/glossary#di-token) `HTTP_INTERCEPTORS`.
     *
     * @publicApi
     */
    var HttpClientJsonpModule = /** @class */ (function () {
        function HttpClientJsonpModule() {
        }
        return HttpClientJsonpModule;
    }());
    HttpClientJsonpModule.decorators = [
        { type: core.NgModule, args: [{
                    providers: [
                        JsonpClientBackend,
                        { provide: JsonpCallbackContext, useFactory: jsonpCallbackContext },
                        { provide: HTTP_INTERCEPTORS, useClass: JsonpInterceptor, multi: true },
                    ],
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
     * Generated bundle index. Do not edit.
     */

    exports.HTTP_INTERCEPTORS = HTTP_INTERCEPTORS;
    exports.HttpBackend = HttpBackend;
    exports.HttpClient = HttpClient;
    exports.HttpClientJsonpModule = HttpClientJsonpModule;
    exports.HttpClientModule = HttpClientModule;
    exports.HttpClientXsrfModule = HttpClientXsrfModule;
    exports.HttpErrorResponse = HttpErrorResponse;
    exports.HttpHandler = HttpHandler;
    exports.HttpHeaderResponse = HttpHeaderResponse;
    exports.HttpHeaders = HttpHeaders;
    exports.HttpParams = HttpParams;
    exports.HttpRequest = HttpRequest;
    exports.HttpResponse = HttpResponse;
    exports.HttpResponseBase = HttpResponseBase;
    exports.HttpUrlEncodingCodec = HttpUrlEncodingCodec;
    exports.HttpXhrBackend = HttpXhrBackend;
    exports.HttpXsrfTokenExtractor = HttpXsrfTokenExtractor;
    exports.JsonpClientBackend = JsonpClientBackend;
    exports.JsonpInterceptor = JsonpInterceptor;
    exports.XhrFactory = XhrFactory;
    exports.ɵHttpInterceptingHandler = HttpInterceptingHandler;
    exports.ɵangular_packages_common_http_http_a = NoopInterceptor;
    exports.ɵangular_packages_common_http_http_b = JsonpCallbackContext;
    exports.ɵangular_packages_common_http_http_c = jsonpCallbackContext;
    exports.ɵangular_packages_common_http_http_d = BrowserXhr;
    exports.ɵangular_packages_common_http_http_e = XSRF_COOKIE_NAME;
    exports.ɵangular_packages_common_http_http_f = XSRF_HEADER_NAME;
    exports.ɵangular_packages_common_http_http_g = HttpXsrfCookieExtractor;
    exports.ɵangular_packages_common_http_http_h = HttpXsrfInterceptor;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=common-http.umd.js.map
