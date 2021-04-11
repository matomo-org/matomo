/**
 * @license Angular v11.2.7
 * (c) 2010-2021 Google LLC. https://angular.io/
 * License: MIT
 */

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@angular/core'), require('@angular/compiler')) :
    typeof define === 'function' && define.amd ? define('@angular/core/testing', ['exports', '@angular/core', '@angular/compiler'], factory) :
    (global = global || self, factory((global.ng = global.ng || {}, global.ng.core = global.ng.core || {}, global.ng.core.testing = {}), global.ng.core, global.ng.compiler));
}(this, (function (exports, core, compiler) { 'use strict';

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Wraps a test function in an asynchronous test zone. The test will automatically
     * complete when all asynchronous calls within this zone are done. Can be used
     * to wrap an {@link inject} call.
     *
     * Example:
     *
     * ```
     * it('...', waitForAsync(inject([AClass], (object) => {
     *   object.doSomething.then(() => {
     *     expect(...);
     *   })
     * });
     * ```
     *
     * @publicApi
     */
    function waitForAsync(fn) {
        var _Zone = typeof Zone !== 'undefined' ? Zone : null;
        if (!_Zone) {
            return function () {
                return Promise.reject('Zone is needed for the waitForAsync() test helper but could not be found. ' +
                    'Please make sure that your environment includes zone.js/dist/zone.js');
            };
        }
        var asyncTest = _Zone && _Zone[_Zone.__symbol__('asyncTest')];
        if (typeof asyncTest === 'function') {
            return asyncTest(fn);
        }
        return function () {
            return Promise.reject('zone-testing.js is needed for the async() test helper but could not be found. ' +
                'Please make sure that your environment includes zone.js/dist/zone-testing.js');
        };
    }
    /**
     * @deprecated use `waitForAsync()`, (expected removal in v12)
     * @see {@link waitForAsync}
     * @publicApi
     * */
    function async(fn) {
        return waitForAsync(fn);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Fixture for debugging and testing a component.
     *
     * @publicApi
     */
    var ComponentFixture = /** @class */ (function () {
        function ComponentFixture(componentRef, ngZone, _autoDetect) {
            var _this = this;
            this.componentRef = componentRef;
            this.ngZone = ngZone;
            this._autoDetect = _autoDetect;
            this._isStable = true;
            this._isDestroyed = false;
            this._resolve = null;
            this._promise = null;
            this._onUnstableSubscription = null;
            this._onStableSubscription = null;
            this._onMicrotaskEmptySubscription = null;
            this._onErrorSubscription = null;
            this.changeDetectorRef = componentRef.changeDetectorRef;
            this.elementRef = componentRef.location;
            this.debugElement = core.getDebugNode(this.elementRef.nativeElement);
            this.componentInstance = componentRef.instance;
            this.nativeElement = this.elementRef.nativeElement;
            this.componentRef = componentRef;
            this.ngZone = ngZone;
            if (ngZone) {
                // Create subscriptions outside the NgZone so that the callbacks run oustide
                // of NgZone.
                ngZone.runOutsideAngular(function () {
                    _this._onUnstableSubscription = ngZone.onUnstable.subscribe({
                        next: function () {
                            _this._isStable = false;
                        }
                    });
                    _this._onMicrotaskEmptySubscription = ngZone.onMicrotaskEmpty.subscribe({
                        next: function () {
                            if (_this._autoDetect) {
                                // Do a change detection run with checkNoChanges set to true to check
                                // there are no changes on the second run.
                                _this.detectChanges(true);
                            }
                        }
                    });
                    _this._onStableSubscription = ngZone.onStable.subscribe({
                        next: function () {
                            _this._isStable = true;
                            // Check whether there is a pending whenStable() completer to resolve.
                            if (_this._promise !== null) {
                                // If so check whether there are no pending macrotasks before resolving.
                                // Do this check in the next tick so that ngZone gets a chance to update the state of
                                // pending macrotasks.
                                scheduleMicroTask(function () {
                                    if (!ngZone.hasPendingMacrotasks) {
                                        if (_this._promise !== null) {
                                            _this._resolve(true);
                                            _this._resolve = null;
                                            _this._promise = null;
                                        }
                                    }
                                });
                            }
                        }
                    });
                    _this._onErrorSubscription = ngZone.onError.subscribe({
                        next: function (error) {
                            throw error;
                        }
                    });
                });
            }
        }
        ComponentFixture.prototype._tick = function (checkNoChanges) {
            this.changeDetectorRef.detectChanges();
            if (checkNoChanges) {
                this.checkNoChanges();
            }
        };
        /**
         * Trigger a change detection cycle for the component.
         */
        ComponentFixture.prototype.detectChanges = function (checkNoChanges) {
            var _this = this;
            if (checkNoChanges === void 0) { checkNoChanges = true; }
            if (this.ngZone != null) {
                // Run the change detection inside the NgZone so that any async tasks as part of the change
                // detection are captured by the zone and can be waited for in isStable.
                this.ngZone.run(function () {
                    _this._tick(checkNoChanges);
                });
            }
            else {
                // Running without zone. Just do the change detection.
                this._tick(checkNoChanges);
            }
        };
        /**
         * Do a change detection run to make sure there were no changes.
         */
        ComponentFixture.prototype.checkNoChanges = function () {
            this.changeDetectorRef.checkNoChanges();
        };
        /**
         * Set whether the fixture should autodetect changes.
         *
         * Also runs detectChanges once so that any existing change is detected.
         */
        ComponentFixture.prototype.autoDetectChanges = function (autoDetect) {
            if (autoDetect === void 0) { autoDetect = true; }
            if (this.ngZone == null) {
                throw new Error('Cannot call autoDetectChanges when ComponentFixtureNoNgZone is set');
            }
            this._autoDetect = autoDetect;
            this.detectChanges();
        };
        /**
         * Return whether the fixture is currently stable or has async tasks that have not been completed
         * yet.
         */
        ComponentFixture.prototype.isStable = function () {
            return this._isStable && !this.ngZone.hasPendingMacrotasks;
        };
        /**
         * Get a promise that resolves when the fixture is stable.
         *
         * This can be used to resume testing after events have triggered asynchronous activity or
         * asynchronous change detection.
         */
        ComponentFixture.prototype.whenStable = function () {
            var _this = this;
            if (this.isStable()) {
                return Promise.resolve(false);
            }
            else if (this._promise !== null) {
                return this._promise;
            }
            else {
                this._promise = new Promise(function (res) {
                    _this._resolve = res;
                });
                return this._promise;
            }
        };
        ComponentFixture.prototype._getRenderer = function () {
            if (this._renderer === undefined) {
                this._renderer = this.componentRef.injector.get(core.RendererFactory2, null);
            }
            return this._renderer;
        };
        /**
         * Get a promise that resolves when the ui state is stable following animations.
         */
        ComponentFixture.prototype.whenRenderingDone = function () {
            var renderer = this._getRenderer();
            if (renderer && renderer.whenRenderingDone) {
                return renderer.whenRenderingDone();
            }
            return this.whenStable();
        };
        /**
         * Trigger component destruction.
         */
        ComponentFixture.prototype.destroy = function () {
            if (!this._isDestroyed) {
                this.componentRef.destroy();
                if (this._onUnstableSubscription != null) {
                    this._onUnstableSubscription.unsubscribe();
                    this._onUnstableSubscription = null;
                }
                if (this._onStableSubscription != null) {
                    this._onStableSubscription.unsubscribe();
                    this._onStableSubscription = null;
                }
                if (this._onMicrotaskEmptySubscription != null) {
                    this._onMicrotaskEmptySubscription.unsubscribe();
                    this._onMicrotaskEmptySubscription = null;
                }
                if (this._onErrorSubscription != null) {
                    this._onErrorSubscription.unsubscribe();
                    this._onErrorSubscription = null;
                }
                this._isDestroyed = true;
            }
        };
        return ComponentFixture;
    }());
    function scheduleMicroTask(fn) {
        Zone.current.scheduleMicroTask('scheduleMicrotask', fn);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var _Zone = typeof Zone !== 'undefined' ? Zone : null;
    var fakeAsyncTestModule = _Zone && _Zone[_Zone.__symbol__('fakeAsyncTest')];
    var fakeAsyncTestModuleNotLoadedErrorMessage = "zone-testing.js is needed for the fakeAsync() test helper but could not be found.\n        Please make sure that your environment includes zone.js/dist/zone-testing.js";
    /**
     * Clears out the shared fake async zone for a test.
     * To be called in a global `beforeEach`.
     *
     * @publicApi
     */
    function resetFakeAsyncZone() {
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.resetFakeAsyncZone();
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
    }
    /**
     * Wraps a function to be executed in the fakeAsync zone:
     * - microtasks are manually executed by calling `flushMicrotasks()`,
     * - timers are synchronous, `tick()` simulates the asynchronous passage of time.
     *
     * If there are any pending timers at the end of the function, an exception will be thrown.
     *
     * Can be used to wrap inject() calls.
     *
     * @usageNotes
     * ### Example
     *
     * {@example core/testing/ts/fake_async.ts region='basic'}
     *
     * @param fn
     * @returns The function wrapped to be executed in the fakeAsync zone
     *
     * @publicApi
     */
    function fakeAsync(fn) {
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.fakeAsync(fn);
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
    }
    /**
     * Simulates the asynchronous passage of time for the timers in the fakeAsync zone.
     *
     * The microtasks queue is drained at the very start of this function and after any timer callback
     * has been executed.
     *
     * @usageNotes
     * ### Example
     *
     * {@example core/testing/ts/fake_async.ts region='basic'}
     *
     * @param millis, the number of millisecond to advance the virtual timer
     * @param tickOptions, the options of tick with a flag called
     * processNewMacroTasksSynchronously, whether to invoke the new macroTasks, by default is
     * false, means the new macroTasks will be invoked
     *
     * For example,
     *
     * it ('test with nested setTimeout', fakeAsync(() => {
     *   let nestedTimeoutInvoked = false;
     *   function funcWithNestedTimeout() {
     *     setTimeout(() => {
     *       nestedTimeoutInvoked = true;
     *     });
     *   };
     *   setTimeout(funcWithNestedTimeout);
     *   tick();
     *   expect(nestedTimeoutInvoked).toBe(true);
     * }));
     *
     * in this case, we have a nested timeout (new macroTask), when we tick, both the
     * funcWithNestedTimeout and the nested timeout both will be invoked.
     *
     * it ('test with nested setTimeout', fakeAsync(() => {
     *   let nestedTimeoutInvoked = false;
     *   function funcWithNestedTimeout() {
     *     setTimeout(() => {
     *       nestedTimeoutInvoked = true;
     *     });
     *   };
     *   setTimeout(funcWithNestedTimeout);
     *   tick(0, {processNewMacroTasksSynchronously: false});
     *   expect(nestedTimeoutInvoked).toBe(false);
     * }));
     *
     * if we pass the tickOptions with processNewMacroTasksSynchronously to be false, the nested timeout
     * will not be invoked.
     *
     *
     * @publicApi
     */
    function tick(millis, tickOptions) {
        if (millis === void 0) { millis = 0; }
        if (tickOptions === void 0) { tickOptions = {
            processNewMacroTasksSynchronously: true
        }; }
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.tick(millis, tickOptions);
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
    }
    /**
     * Simulates the asynchronous passage of time for the timers in the fakeAsync zone by
     * draining the macrotask queue until it is empty. The returned value is the milliseconds
     * of time that would have been elapsed.
     *
     * @param maxTurns
     * @returns The simulated time elapsed, in millis.
     *
     * @publicApi
     */
    function flush(maxTurns) {
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.flush(maxTurns);
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
    }
    /**
     * Discard all remaining periodic tasks.
     *
     * @publicApi
     */
    function discardPeriodicTasks() {
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.discardPeriodicTasks();
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
    }
    /**
     * Flush any pending microtasks.
     *
     * @publicApi
     */
    function flushMicrotasks() {
        if (fakeAsyncTestModule) {
            return fakeAsyncTestModule.flushMicrotasks();
        }
        throw new Error(fakeAsyncTestModuleNotLoadedErrorMessage);
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
     * Injectable completer that allows signaling completion of an asynchronous test. Used internally.
     */
    var AsyncTestCompleter = /** @class */ (function () {
        function AsyncTestCompleter() {
            var _this = this;
            this._promise = new Promise(function (res, rej) {
                _this._resolve = res;
                _this._reject = rej;
            });
        }
        AsyncTestCompleter.prototype.done = function (value) {
            this._resolve(value);
        };
        AsyncTestCompleter.prototype.fail = function (error, stackTrace) {
            this._reject(error);
        };
        Object.defineProperty(AsyncTestCompleter.prototype, "promise", {
            get: function () {
                return this._promise;
            },
            enumerable: false,
            configurable: true
        });
        return AsyncTestCompleter;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Used to resolve resource URLs on `@Component` when used with JIT compilation.
     *
     * Example:
     * ```
     * @Component({
     *   selector: 'my-comp',
     *   templateUrl: 'my-comp.html', // This requires asynchronous resolution
     * })
     * class MyComponent{
     * }
     *
     * // Calling `renderComponent` will fail because `renderComponent` is a synchronous process
     * // and `MyComponent`'s `@Component.templateUrl` needs to be resolved asynchronously.
     *
     * // Calling `resolveComponentResources()` will resolve `@Component.templateUrl` into
     * // `@Component.template`, which allows `renderComponent` to proceed in a synchronous manner.
     *
     * // Use browser's `fetch()` function as the default resource resolution strategy.
     * resolveComponentResources(fetch).then(() => {
     *   // After resolution all URLs have been converted into `template` strings.
     *   renderComponent(MyComponent);
     * });
     *
     * ```
     *
     * NOTE: In AOT the resolution happens during compilation, and so there should be no need
     * to call this method outside JIT mode.
     *
     * @param resourceResolver a function which is responsible for returning a `Promise` to the
     * contents of the resolved URL. Browser's `fetch()` method is a good default implementation.
     */
    function resolveComponentResources(resourceResolver) {
        // Store all promises which are fetching the resources.
        var componentResolved = [];
        // Cache so that we don't fetch the same resource more than once.
        var urlMap = new Map();
        function cachedResourceResolve(url) {
            var promise = urlMap.get(url);
            if (!promise) {
                var resp = resourceResolver(url);
                urlMap.set(url, promise = resp.then(unwrapResponse));
            }
            return promise;
        }
        componentResourceResolutionQueue.forEach(function (component, type) {
            var promises = [];
            if (component.templateUrl) {
                promises.push(cachedResourceResolve(component.templateUrl).then(function (template) {
                    component.template = template;
                }));
            }
            var styleUrls = component.styleUrls;
            var styles = component.styles || (component.styles = []);
            var styleOffset = component.styles.length;
            styleUrls && styleUrls.forEach(function (styleUrl, index) {
                styles.push(''); // pre-allocate array.
                promises.push(cachedResourceResolve(styleUrl).then(function (style) {
                    styles[styleOffset + index] = style;
                    styleUrls.splice(styleUrls.indexOf(styleUrl), 1);
                    if (styleUrls.length == 0) {
                        component.styleUrls = undefined;
                    }
                }));
            });
            var fullyResolved = Promise.all(promises).then(function () { return componentDefResolved(type); });
            componentResolved.push(fullyResolved);
        });
        clearResolutionOfComponentResourcesQueue();
        return Promise.all(componentResolved).then(function () { return undefined; });
    }
    var componentResourceResolutionQueue = new Map();
    // Track when existing ɵcmp for a Type is waiting on resources.
    var componentDefPendingResolution = new Set();
    function maybeQueueResolutionOfComponentResources(type, metadata) {
        if (componentNeedsResolution(metadata)) {
            componentResourceResolutionQueue.set(type, metadata);
            componentDefPendingResolution.add(type);
        }
    }
    function isComponentDefPendingResolution(type) {
        return componentDefPendingResolution.has(type);
    }
    function componentNeedsResolution(component) {
        return !!((component.templateUrl && !component.hasOwnProperty('template')) ||
            component.styleUrls && component.styleUrls.length);
    }
    function clearResolutionOfComponentResourcesQueue() {
        var old = componentResourceResolutionQueue;
        componentResourceResolutionQueue = new Map();
        return old;
    }
    function restoreComponentResolutionQueue(queue) {
        componentDefPendingResolution.clear();
        queue.forEach(function (_, type) { return componentDefPendingResolution.add(type); });
        componentResourceResolutionQueue = queue;
    }
    function isComponentResourceResolutionQueueEmpty() {
        return componentResourceResolutionQueue.size === 0;
    }
    function unwrapResponse(response) {
        return typeof response == 'string' ? response : response.text();
    }
    function componentDefResolved(type) {
        componentDefPendingResolution.delete(type);
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var _nextReferenceId = 0;
    var MetadataOverrider = /** @class */ (function () {
        function MetadataOverrider() {
            this._references = new Map();
        }
        /**
         * Creates a new instance for the given metadata class
         * based on an old instance and overrides.
         */
        MetadataOverrider.prototype.overrideMetadata = function (metadataClass, oldMetadata, override) {
            var props = {};
            if (oldMetadata) {
                _valueProps(oldMetadata).forEach(function (prop) { return props[prop] = oldMetadata[prop]; });
            }
            if (override.set) {
                if (override.remove || override.add) {
                    throw new Error("Cannot set and add/remove " + core.ɵstringify(metadataClass) + " at the same time!");
                }
                setMetadata(props, override.set);
            }
            if (override.remove) {
                removeMetadata(props, override.remove, this._references);
            }
            if (override.add) {
                addMetadata(props, override.add);
            }
            return new metadataClass(props);
        };
        return MetadataOverrider;
    }());
    function removeMetadata(metadata, remove, references) {
        var removeObjects = new Set();
        var _loop_1 = function (prop) {
            var removeValue = remove[prop];
            if (Array.isArray(removeValue)) {
                removeValue.forEach(function (value) {
                    removeObjects.add(_propHashKey(prop, value, references));
                });
            }
            else {
                removeObjects.add(_propHashKey(prop, removeValue, references));
            }
        };
        for (var prop in remove) {
            _loop_1(prop);
        }
        var _loop_2 = function (prop) {
            var propValue = metadata[prop];
            if (Array.isArray(propValue)) {
                metadata[prop] = propValue.filter(function (value) { return !removeObjects.has(_propHashKey(prop, value, references)); });
            }
            else {
                if (removeObjects.has(_propHashKey(prop, propValue, references))) {
                    metadata[prop] = undefined;
                }
            }
        };
        for (var prop in metadata) {
            _loop_2(prop);
        }
    }
    function addMetadata(metadata, add) {
        for (var prop in add) {
            var addValue = add[prop];
            var propValue = metadata[prop];
            if (propValue != null && Array.isArray(propValue)) {
                metadata[prop] = propValue.concat(addValue);
            }
            else {
                metadata[prop] = addValue;
            }
        }
    }
    function setMetadata(metadata, set) {
        for (var prop in set) {
            metadata[prop] = set[prop];
        }
    }
    function _propHashKey(propName, propValue, references) {
        var replacer = function (key, value) {
            if (typeof value === 'function') {
                value = _serializeReference(value, references);
            }
            return value;
        };
        return propName + ":" + JSON.stringify(propValue, replacer);
    }
    function _serializeReference(ref, references) {
        var id = references.get(ref);
        if (!id) {
            id = "" + core.ɵstringify(ref) + _nextReferenceId++;
            references.set(ref, id);
        }
        return id;
    }
    function _valueProps(obj) {
        var props = [];
        // regular public props
        Object.keys(obj).forEach(function (prop) {
            if (!prop.startsWith('_')) {
                props.push(prop);
            }
        });
        // getters
        var proto = obj;
        while (proto = Object.getPrototypeOf(proto)) {
            Object.keys(proto).forEach(function (protoProp) {
                var desc = Object.getOwnPropertyDescriptor(proto, protoProp);
                if (!protoProp.startsWith('_') && desc && 'get' in desc) {
                    props.push(protoProp);
                }
            });
        }
        return props;
    }

    var reflection = new core.ɵReflectionCapabilities();
    /**
     * Allows to override ivy metadata for tests (via the `TestBed`).
     */
    var OverrideResolver = /** @class */ (function () {
        function OverrideResolver() {
            this.overrides = new Map();
            this.resolved = new Map();
        }
        OverrideResolver.prototype.addOverride = function (type, override) {
            var overrides = this.overrides.get(type) || [];
            overrides.push(override);
            this.overrides.set(type, overrides);
            this.resolved.delete(type);
        };
        OverrideResolver.prototype.setOverrides = function (overrides) {
            var _this = this;
            this.overrides.clear();
            overrides.forEach(function (_a) {
                var _b = __read(_a, 2), type = _b[0], override = _b[1];
                _this.addOverride(type, override);
            });
        };
        OverrideResolver.prototype.getAnnotation = function (type) {
            var annotations = reflection.annotations(type);
            // Try to find the nearest known Type annotation and make sure that this annotation is an
            // instance of the type we are looking for, so we can use it for resolution. Note: there might
            // be multiple known annotations found due to the fact that Components can extend Directives (so
            // both Directive and Component annotations would be present), so we always check if the known
            // annotation has the right type.
            for (var i = annotations.length - 1; i >= 0; i--) {
                var annotation = annotations[i];
                var isKnownType = annotation instanceof core.Directive || annotation instanceof core.Component ||
                    annotation instanceof core.Pipe || annotation instanceof core.NgModule;
                if (isKnownType) {
                    return annotation instanceof this.type ? annotation : null;
                }
            }
            return null;
        };
        OverrideResolver.prototype.resolve = function (type) {
            var _this = this;
            var resolved = this.resolved.get(type) || null;
            if (!resolved) {
                resolved = this.getAnnotation(type);
                if (resolved) {
                    var overrides = this.overrides.get(type);
                    if (overrides) {
                        var overrider_1 = new MetadataOverrider();
                        overrides.forEach(function (override) {
                            resolved = overrider_1.overrideMetadata(_this.type, resolved, override);
                        });
                    }
                }
                this.resolved.set(type, resolved);
            }
            return resolved;
        };
        return OverrideResolver;
    }());
    var DirectiveResolver = /** @class */ (function (_super) {
        __extends(DirectiveResolver, _super);
        function DirectiveResolver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(DirectiveResolver.prototype, "type", {
            get: function () {
                return core.Directive;
            },
            enumerable: false,
            configurable: true
        });
        return DirectiveResolver;
    }(OverrideResolver));
    var ComponentResolver = /** @class */ (function (_super) {
        __extends(ComponentResolver, _super);
        function ComponentResolver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(ComponentResolver.prototype, "type", {
            get: function () {
                return core.Component;
            },
            enumerable: false,
            configurable: true
        });
        return ComponentResolver;
    }(OverrideResolver));
    var PipeResolver = /** @class */ (function (_super) {
        __extends(PipeResolver, _super);
        function PipeResolver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(PipeResolver.prototype, "type", {
            get: function () {
                return core.Pipe;
            },
            enumerable: false,
            configurable: true
        });
        return PipeResolver;
    }(OverrideResolver));
    var NgModuleResolver = /** @class */ (function (_super) {
        __extends(NgModuleResolver, _super);
        function NgModuleResolver() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(NgModuleResolver.prototype, "type", {
            get: function () {
                return core.NgModule;
            },
            enumerable: false,
            configurable: true
        });
        return NgModuleResolver;
    }(OverrideResolver));

    var TestingModuleOverride;
    (function (TestingModuleOverride) {
        TestingModuleOverride[TestingModuleOverride["DECLARATION"] = 0] = "DECLARATION";
        TestingModuleOverride[TestingModuleOverride["OVERRIDE_TEMPLATE"] = 1] = "OVERRIDE_TEMPLATE";
    })(TestingModuleOverride || (TestingModuleOverride = {}));
    function isTestingModuleOverride(value) {
        return value === TestingModuleOverride.DECLARATION ||
            value === TestingModuleOverride.OVERRIDE_TEMPLATE;
    }
    var R3TestBedCompiler = /** @class */ (function () {
        function R3TestBedCompiler(platform, additionalModuleTypes) {
            this.platform = platform;
            this.additionalModuleTypes = additionalModuleTypes;
            this.originalComponentResolutionQueue = null;
            // Testing module configuration
            this.declarations = [];
            this.imports = [];
            this.providers = [];
            this.schemas = [];
            // Queues of components/directives/pipes that should be recompiled.
            this.pendingComponents = new Set();
            this.pendingDirectives = new Set();
            this.pendingPipes = new Set();
            // Keep track of all components and directives, so we can patch Providers onto defs later.
            this.seenComponents = new Set();
            this.seenDirectives = new Set();
            // Keep track of overridden modules, so that we can collect all affected ones in the module tree.
            this.overriddenModules = new Set();
            // Store resolved styles for Components that have template overrides present and `styleUrls`
            // defined at the same time.
            this.existingComponentStyles = new Map();
            this.resolvers = initResolvers();
            this.componentToModuleScope = new Map();
            // Map that keeps initial version of component/directive/pipe defs in case
            // we compile a Type again, thus overriding respective static fields. This is
            // required to make sure we restore defs to their initial states between test runs
            // TODO: we should support the case with multiple defs on a type
            this.initialNgDefs = new Map();
            // Array that keeps cleanup operations for initial versions of component/directive/pipe/module
            // defs in case TestBed makes changes to the originals.
            this.defCleanupOps = [];
            this._injector = null;
            this.compilerProviders = null;
            this.providerOverrides = [];
            this.rootProviderOverrides = [];
            // Overrides for injectables with `{providedIn: SomeModule}` need to be tracked and added to that
            // module's provider list.
            this.providerOverridesByModule = new Map();
            this.providerOverridesByToken = new Map();
            this.moduleProvidersOverridden = new Set();
            this.testModuleRef = null;
            var DynamicTestModule = /** @class */ (function () {
                function DynamicTestModule() {
                }
                return DynamicTestModule;
            }());
            this.testModuleType = DynamicTestModule;
        }
        R3TestBedCompiler.prototype.setCompilerProviders = function (providers) {
            this.compilerProviders = providers;
            this._injector = null;
        };
        R3TestBedCompiler.prototype.configureTestingModule = function (moduleDef) {
            var _a, _b, _c, _d;
            // Enqueue any compilation tasks for the directly declared component.
            if (moduleDef.declarations !== undefined) {
                this.queueTypeArray(moduleDef.declarations, TestingModuleOverride.DECLARATION);
                (_a = this.declarations).push.apply(_a, __spread(moduleDef.declarations));
            }
            // Enqueue any compilation tasks for imported modules.
            if (moduleDef.imports !== undefined) {
                this.queueTypesFromModulesArray(moduleDef.imports);
                (_b = this.imports).push.apply(_b, __spread(moduleDef.imports));
            }
            if (moduleDef.providers !== undefined) {
                (_c = this.providers).push.apply(_c, __spread(moduleDef.providers));
            }
            if (moduleDef.schemas !== undefined) {
                (_d = this.schemas).push.apply(_d, __spread(moduleDef.schemas));
            }
        };
        R3TestBedCompiler.prototype.overrideModule = function (ngModule, override) {
            this.overriddenModules.add(ngModule);
            // Compile the module right away.
            this.resolvers.module.addOverride(ngModule, override);
            var metadata = this.resolvers.module.resolve(ngModule);
            if (metadata === null) {
                throw invalidTypeError(ngModule.name, 'NgModule');
            }
            this.recompileNgModule(ngModule, metadata);
            // At this point, the module has a valid module def (ɵmod), but the override may have introduced
            // new declarations or imported modules. Ingest any possible new types and add them to the
            // current queue.
            this.queueTypesFromModulesArray([ngModule]);
        };
        R3TestBedCompiler.prototype.overrideComponent = function (component, override) {
            this.resolvers.component.addOverride(component, override);
            this.pendingComponents.add(component);
        };
        R3TestBedCompiler.prototype.overrideDirective = function (directive, override) {
            this.resolvers.directive.addOverride(directive, override);
            this.pendingDirectives.add(directive);
        };
        R3TestBedCompiler.prototype.overridePipe = function (pipe, override) {
            this.resolvers.pipe.addOverride(pipe, override);
            this.pendingPipes.add(pipe);
        };
        R3TestBedCompiler.prototype.overrideProvider = function (token, provider) {
            var providerDef;
            if (provider.useFactory !== undefined) {
                providerDef = {
                    provide: token,
                    useFactory: provider.useFactory,
                    deps: provider.deps || [],
                    multi: provider.multi
                };
            }
            else if (provider.useValue !== undefined) {
                providerDef = { provide: token, useValue: provider.useValue, multi: provider.multi };
            }
            else {
                providerDef = { provide: token };
            }
            var injectableDef = typeof token !== 'string' ? core.ɵgetInjectableDef(token) : null;
            var isRoot = injectableDef !== null && injectableDef.providedIn === 'root';
            var overridesBucket = isRoot ? this.rootProviderOverrides : this.providerOverrides;
            overridesBucket.push(providerDef);
            // Keep overrides grouped by token as well for fast lookups using token
            this.providerOverridesByToken.set(token, providerDef);
            if (injectableDef !== null && injectableDef.providedIn !== null &&
                typeof injectableDef.providedIn !== 'string') {
                var existingOverrides = this.providerOverridesByModule.get(injectableDef.providedIn);
                if (existingOverrides !== undefined) {
                    existingOverrides.push(providerDef);
                }
                else {
                    this.providerOverridesByModule.set(injectableDef.providedIn, [providerDef]);
                }
            }
        };
        R3TestBedCompiler.prototype.overrideTemplateUsingTestingModule = function (type, template) {
            var _this = this;
            var def = type[core.ɵNG_COMP_DEF];
            var hasStyleUrls = function () {
                var metadata = _this.resolvers.component.resolve(type);
                return !!metadata.styleUrls && metadata.styleUrls.length > 0;
            };
            var overrideStyleUrls = !!def && !isComponentDefPendingResolution(type) && hasStyleUrls();
            // In Ivy, compiling a component does not require knowing the module providing the
            // component's scope, so overrideTemplateUsingTestingModule can be implemented purely via
            // overrideComponent. Important: overriding template requires full Component re-compilation,
            // which may fail in case styleUrls are also present (thus Component is considered as required
            // resolution). In order to avoid this, we preemptively set styleUrls to an empty array,
            // preserve current styles available on Component def and restore styles back once compilation
            // is complete.
            var override = overrideStyleUrls ? { template: template, styles: [], styleUrls: [] } : { template: template };
            this.overrideComponent(type, { set: override });
            if (overrideStyleUrls && def.styles && def.styles.length > 0) {
                this.existingComponentStyles.set(type, def.styles);
            }
            // Set the component's scope to be the testing module.
            this.componentToModuleScope.set(type, TestingModuleOverride.OVERRIDE_TEMPLATE);
        };
        R3TestBedCompiler.prototype.compileComponents = function () {
            return __awaiter(this, void 0, void 0, function () {
                var needsAsyncResources, resourceLoader_1, resolver;
                var _this = this;
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0:
                            this.clearComponentResolutionQueue();
                            needsAsyncResources = this.compileTypesSync();
                            if (!needsAsyncResources) return [3 /*break*/, 2];
                            resolver = function (url) {
                                if (!resourceLoader_1) {
                                    resourceLoader_1 = _this.injector.get(compiler.ResourceLoader);
                                }
                                return Promise.resolve(resourceLoader_1.get(url));
                            };
                            return [4 /*yield*/, resolveComponentResources(resolver)];
                        case 1:
                            _a.sent();
                            _a.label = 2;
                        case 2: return [2 /*return*/];
                    }
                });
            });
        };
        R3TestBedCompiler.prototype.finalize = function () {
            // One last compile
            this.compileTypesSync();
            // Create the testing module itself.
            this.compileTestModule();
            this.applyTransitiveScopes();
            this.applyProviderOverrides();
            // Patch previously stored `styles` Component values (taken from ɵcmp), in case these
            // Components have `styleUrls` fields defined and template override was requested.
            this.patchComponentsWithExistingStyles();
            // Clear the componentToModuleScope map, so that future compilations don't reset the scope of
            // every component.
            this.componentToModuleScope.clear();
            var parentInjector = this.platform.injector;
            this.testModuleRef = new core.ɵRender3NgModuleRef(this.testModuleType, parentInjector);
            // ApplicationInitStatus.runInitializers() is marked @internal to core.
            // Cast it to any before accessing it.
            this.testModuleRef.injector.get(core.ApplicationInitStatus).runInitializers();
            // Set locale ID after running app initializers, since locale information might be updated while
            // running initializers. This is also consistent with the execution order while bootstrapping an
            // app (see `packages/core/src/application_ref.ts` file).
            var localeId = this.testModuleRef.injector.get(core.LOCALE_ID, core.ɵDEFAULT_LOCALE_ID);
            core.ɵsetLocaleId(localeId);
            return this.testModuleRef;
        };
        /**
         * @internal
         */
        R3TestBedCompiler.prototype._compileNgModuleSync = function (moduleType) {
            this.queueTypesFromModulesArray([moduleType]);
            this.compileTypesSync();
            this.applyProviderOverrides();
            this.applyProviderOverridesToModule(moduleType);
            this.applyTransitiveScopes();
        };
        /**
         * @internal
         */
        R3TestBedCompiler.prototype._compileNgModuleAsync = function (moduleType) {
            return __awaiter(this, void 0, void 0, function () {
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0:
                            this.queueTypesFromModulesArray([moduleType]);
                            return [4 /*yield*/, this.compileComponents()];
                        case 1:
                            _a.sent();
                            this.applyProviderOverrides();
                            this.applyProviderOverridesToModule(moduleType);
                            this.applyTransitiveScopes();
                            return [2 /*return*/];
                    }
                });
            });
        };
        /**
         * @internal
         */
        R3TestBedCompiler.prototype._getModuleResolver = function () {
            return this.resolvers.module;
        };
        /**
         * @internal
         */
        R3TestBedCompiler.prototype._getComponentFactories = function (moduleType) {
            var _this = this;
            return maybeUnwrapFn(moduleType.ɵmod.declarations).reduce(function (factories, declaration) {
                var componentDef = declaration.ɵcmp;
                componentDef && factories.push(new core.ɵRender3ComponentFactory(componentDef, _this.testModuleRef));
                return factories;
            }, []);
        };
        R3TestBedCompiler.prototype.compileTypesSync = function () {
            var _this = this;
            // Compile all queued components, directives, pipes.
            var needsAsyncResources = false;
            this.pendingComponents.forEach(function (declaration) {
                needsAsyncResources = needsAsyncResources || isComponentDefPendingResolution(declaration);
                var metadata = _this.resolvers.component.resolve(declaration);
                if (metadata === null) {
                    throw invalidTypeError(declaration.name, 'Component');
                }
                _this.maybeStoreNgDef(core.ɵNG_COMP_DEF, declaration);
                core.ɵcompileComponent(declaration, metadata);
            });
            this.pendingComponents.clear();
            this.pendingDirectives.forEach(function (declaration) {
                var metadata = _this.resolvers.directive.resolve(declaration);
                if (metadata === null) {
                    throw invalidTypeError(declaration.name, 'Directive');
                }
                _this.maybeStoreNgDef(core.ɵNG_DIR_DEF, declaration);
                core.ɵcompileDirective(declaration, metadata);
            });
            this.pendingDirectives.clear();
            this.pendingPipes.forEach(function (declaration) {
                var metadata = _this.resolvers.pipe.resolve(declaration);
                if (metadata === null) {
                    throw invalidTypeError(declaration.name, 'Pipe');
                }
                _this.maybeStoreNgDef(core.ɵNG_PIPE_DEF, declaration);
                core.ɵcompilePipe(declaration, metadata);
            });
            this.pendingPipes.clear();
            return needsAsyncResources;
        };
        R3TestBedCompiler.prototype.applyTransitiveScopes = function () {
            var _this = this;
            if (this.overriddenModules.size > 0) {
                // Module overrides (via `TestBed.overrideModule`) might affect scopes that were previously
                // calculated and stored in `transitiveCompileScopes`. If module overrides are present,
                // collect all affected modules and reset scopes to force their re-calculatation.
                var testingModuleDef = this.testModuleType[core.ɵNG_MOD_DEF];
                var affectedModules = this.collectModulesAffectedByOverrides(testingModuleDef.imports);
                if (affectedModules.size > 0) {
                    affectedModules.forEach(function (moduleType) {
                        _this.storeFieldOfDefOnType(moduleType, core.ɵNG_MOD_DEF, 'transitiveCompileScopes');
                        moduleType[core.ɵNG_MOD_DEF].transitiveCompileScopes = null;
                    });
                }
            }
            var moduleToScope = new Map();
            var getScopeOfModule = function (moduleType) {
                if (!moduleToScope.has(moduleType)) {
                    var isTestingModule = isTestingModuleOverride(moduleType);
                    var realType = isTestingModule ? _this.testModuleType : moduleType;
                    moduleToScope.set(moduleType, core.ɵtransitiveScopesFor(realType));
                }
                return moduleToScope.get(moduleType);
            };
            this.componentToModuleScope.forEach(function (moduleType, componentType) {
                var moduleScope = getScopeOfModule(moduleType);
                _this.storeFieldOfDefOnType(componentType, core.ɵNG_COMP_DEF, 'directiveDefs');
                _this.storeFieldOfDefOnType(componentType, core.ɵNG_COMP_DEF, 'pipeDefs');
                // `tView` that is stored on component def contains information about directives and pipes
                // that are in the scope of this component. Patching component scope will cause `tView` to be
                // changed. Store original `tView` before patching scope, so the `tView` (including scope
                // information) is restored back to its previous/original state before running next test.
                _this.storeFieldOfDefOnType(componentType, core.ɵNG_COMP_DEF, 'tView');
                core.ɵpatchComponentDefWithScope(componentType.ɵcmp, moduleScope);
            });
            this.componentToModuleScope.clear();
        };
        R3TestBedCompiler.prototype.applyProviderOverrides = function () {
            var _this = this;
            var maybeApplyOverrides = function (field) { return function (type) {
                var resolver = field === core.ɵNG_COMP_DEF ? _this.resolvers.component : _this.resolvers.directive;
                var metadata = resolver.resolve(type);
                if (_this.hasProviderOverrides(metadata.providers)) {
                    _this.patchDefWithProviderOverrides(type, field);
                }
            }; };
            this.seenComponents.forEach(maybeApplyOverrides(core.ɵNG_COMP_DEF));
            this.seenDirectives.forEach(maybeApplyOverrides(core.ɵNG_DIR_DEF));
            this.seenComponents.clear();
            this.seenDirectives.clear();
        };
        R3TestBedCompiler.prototype.applyProviderOverridesToModule = function (moduleType) {
            var e_1, _a, e_2, _b;
            if (this.moduleProvidersOverridden.has(moduleType)) {
                return;
            }
            this.moduleProvidersOverridden.add(moduleType);
            var injectorDef = moduleType[core.ɵNG_INJ_DEF];
            if (this.providerOverridesByToken.size > 0) {
                var providers = __spread(injectorDef.providers, (this.providerOverridesByModule.get(moduleType) || []));
                if (this.hasProviderOverrides(providers)) {
                    this.maybeStoreNgDef(core.ɵNG_INJ_DEF, moduleType);
                    this.storeFieldOfDefOnType(moduleType, core.ɵNG_INJ_DEF, 'providers');
                    injectorDef.providers = this.getOverriddenProviders(providers);
                }
                // Apply provider overrides to imported modules recursively
                var moduleDef = moduleType[core.ɵNG_MOD_DEF];
                var imports = maybeUnwrapFn(moduleDef.imports);
                try {
                    for (var imports_1 = __values(imports), imports_1_1 = imports_1.next(); !imports_1_1.done; imports_1_1 = imports_1.next()) {
                        var importedModule = imports_1_1.value;
                        this.applyProviderOverridesToModule(importedModule);
                    }
                }
                catch (e_1_1) { e_1 = { error: e_1_1 }; }
                finally {
                    try {
                        if (imports_1_1 && !imports_1_1.done && (_a = imports_1.return)) _a.call(imports_1);
                    }
                    finally { if (e_1) throw e_1.error; }
                }
                try {
                    // Also override the providers on any ModuleWithProviders imports since those don't appear in
                    // the moduleDef.
                    for (var _c = __values(flatten(injectorDef.imports)), _d = _c.next(); !_d.done; _d = _c.next()) {
                        var importedModule = _d.value;
                        if (isModuleWithProviders(importedModule)) {
                            this.defCleanupOps.push({
                                object: importedModule,
                                fieldName: 'providers',
                                originalValue: importedModule.providers
                            });
                            importedModule.providers = this.getOverriddenProviders(importedModule.providers);
                        }
                    }
                }
                catch (e_2_1) { e_2 = { error: e_2_1 }; }
                finally {
                    try {
                        if (_d && !_d.done && (_b = _c.return)) _b.call(_c);
                    }
                    finally { if (e_2) throw e_2.error; }
                }
            }
        };
        R3TestBedCompiler.prototype.patchComponentsWithExistingStyles = function () {
            this.existingComponentStyles.forEach(function (styles, type) { return type[core.ɵNG_COMP_DEF].styles = styles; });
            this.existingComponentStyles.clear();
        };
        R3TestBedCompiler.prototype.queueTypeArray = function (arr, moduleType) {
            var e_3, _a;
            try {
                for (var arr_1 = __values(arr), arr_1_1 = arr_1.next(); !arr_1_1.done; arr_1_1 = arr_1.next()) {
                    var value = arr_1_1.value;
                    if (Array.isArray(value)) {
                        this.queueTypeArray(value, moduleType);
                    }
                    else {
                        this.queueType(value, moduleType);
                    }
                }
            }
            catch (e_3_1) { e_3 = { error: e_3_1 }; }
            finally {
                try {
                    if (arr_1_1 && !arr_1_1.done && (_a = arr_1.return)) _a.call(arr_1);
                }
                finally { if (e_3) throw e_3.error; }
            }
        };
        R3TestBedCompiler.prototype.recompileNgModule = function (ngModule, metadata) {
            // Cache the initial ngModuleDef as it will be overwritten.
            this.maybeStoreNgDef(core.ɵNG_MOD_DEF, ngModule);
            this.maybeStoreNgDef(core.ɵNG_INJ_DEF, ngModule);
            core.ɵcompileNgModuleDefs(ngModule, metadata);
        };
        R3TestBedCompiler.prototype.queueType = function (type, moduleType) {
            var component = this.resolvers.component.resolve(type);
            if (component) {
                // Check whether a give Type has respective NG def (ɵcmp) and compile if def is
                // missing. That might happen in case a class without any Angular decorators extends another
                // class where Component/Directive/Pipe decorator is defined.
                if (isComponentDefPendingResolution(type) || !type.hasOwnProperty(core.ɵNG_COMP_DEF)) {
                    this.pendingComponents.add(type);
                }
                this.seenComponents.add(type);
                // Keep track of the module which declares this component, so later the component's scope
                // can be set correctly. If the component has already been recorded here, then one of several
                // cases is true:
                // * the module containing the component was imported multiple times (common).
                // * the component is declared in multiple modules (which is an error).
                // * the component was in 'declarations' of the testing module, and also in an imported module
                //   in which case the module scope will be TestingModuleOverride.DECLARATION.
                // * overrideTemplateUsingTestingModule was called for the component in which case the module
                //   scope will be TestingModuleOverride.OVERRIDE_TEMPLATE.
                //
                // If the component was previously in the testing module's 'declarations' (meaning the
                // current value is TestingModuleOverride.DECLARATION), then `moduleType` is the component's
                // real module, which was imported. This pattern is understood to mean that the component
                // should use its original scope, but that the testing module should also contain the
                // component in its scope.
                if (!this.componentToModuleScope.has(type) ||
                    this.componentToModuleScope.get(type) === TestingModuleOverride.DECLARATION) {
                    this.componentToModuleScope.set(type, moduleType);
                }
                return;
            }
            var directive = this.resolvers.directive.resolve(type);
            if (directive) {
                if (!type.hasOwnProperty(core.ɵNG_DIR_DEF)) {
                    this.pendingDirectives.add(type);
                }
                this.seenDirectives.add(type);
                return;
            }
            var pipe = this.resolvers.pipe.resolve(type);
            if (pipe && !type.hasOwnProperty(core.ɵNG_PIPE_DEF)) {
                this.pendingPipes.add(type);
                return;
            }
        };
        R3TestBedCompiler.prototype.queueTypesFromModulesArray = function (arr) {
            var _this = this;
            // Because we may encounter the same NgModule while processing the imports and exports of an
            // NgModule tree, we cache them in this set so we can skip ones that have already been seen
            // encountered. In some test setups, this caching resulted in 10X runtime improvement.
            var processedNgModuleDefs = new Set();
            var queueTypesFromModulesArrayRecur = function (arr) {
                var e_4, _a;
                try {
                    for (var arr_2 = __values(arr), arr_2_1 = arr_2.next(); !arr_2_1.done; arr_2_1 = arr_2.next()) {
                        var value = arr_2_1.value;
                        if (Array.isArray(value)) {
                            queueTypesFromModulesArrayRecur(value);
                        }
                        else if (hasNgModuleDef(value)) {
                            var def = value.ɵmod;
                            if (processedNgModuleDefs.has(def)) {
                                continue;
                            }
                            processedNgModuleDefs.add(def);
                            // Look through declarations, imports, and exports, and queue
                            // everything found there.
                            _this.queueTypeArray(maybeUnwrapFn(def.declarations), value);
                            queueTypesFromModulesArrayRecur(maybeUnwrapFn(def.imports));
                            queueTypesFromModulesArrayRecur(maybeUnwrapFn(def.exports));
                        }
                    }
                }
                catch (e_4_1) { e_4 = { error: e_4_1 }; }
                finally {
                    try {
                        if (arr_2_1 && !arr_2_1.done && (_a = arr_2.return)) _a.call(arr_2);
                    }
                    finally { if (e_4) throw e_4.error; }
                }
            };
            queueTypesFromModulesArrayRecur(arr);
        };
        // When module overrides (via `TestBed.overrideModule`) are present, it might affect all modules
        // that import (even transitively) an overridden one. For all affected modules we need to
        // recalculate their scopes for a given test run and restore original scopes at the end. The goal
        // of this function is to collect all affected modules in a set for further processing. Example:
        // if we have the following module hierarchy: A -> B -> C (where `->` means `imports`) and module
        // `C` is overridden, we consider `A` and `B` as affected, since their scopes might become
        // invalidated with the override.
        R3TestBedCompiler.prototype.collectModulesAffectedByOverrides = function (arr) {
            var _this = this;
            var seenModules = new Set();
            var affectedModules = new Set();
            var calcAffectedModulesRecur = function (arr, path) {
                var e_5, _a;
                try {
                    for (var arr_3 = __values(arr), arr_3_1 = arr_3.next(); !arr_3_1.done; arr_3_1 = arr_3.next()) {
                        var value = arr_3_1.value;
                        if (Array.isArray(value)) {
                            // If the value is an array, just flatten it (by invoking this function recursively),
                            // keeping "path" the same.
                            calcAffectedModulesRecur(value, path);
                        }
                        else if (hasNgModuleDef(value)) {
                            if (seenModules.has(value)) {
                                // If we've seen this module before and it's included into "affected modules" list, mark
                                // the whole path that leads to that module as affected, but do not descend into its
                                // imports, since we already examined them before.
                                if (affectedModules.has(value)) {
                                    path.forEach(function (item) { return affectedModules.add(item); });
                                }
                                continue;
                            }
                            seenModules.add(value);
                            if (_this.overriddenModules.has(value)) {
                                path.forEach(function (item) { return affectedModules.add(item); });
                            }
                            // Examine module imports recursively to look for overridden modules.
                            var moduleDef = value[core.ɵNG_MOD_DEF];
                            calcAffectedModulesRecur(maybeUnwrapFn(moduleDef.imports), path.concat(value));
                        }
                    }
                }
                catch (e_5_1) { e_5 = { error: e_5_1 }; }
                finally {
                    try {
                        if (arr_3_1 && !arr_3_1.done && (_a = arr_3.return)) _a.call(arr_3);
                    }
                    finally { if (e_5) throw e_5.error; }
                }
            };
            calcAffectedModulesRecur(arr, []);
            return affectedModules;
        };
        R3TestBedCompiler.prototype.maybeStoreNgDef = function (prop, type) {
            if (!this.initialNgDefs.has(type)) {
                var currentDef = Object.getOwnPropertyDescriptor(type, prop);
                this.initialNgDefs.set(type, [prop, currentDef]);
            }
        };
        R3TestBedCompiler.prototype.storeFieldOfDefOnType = function (type, defField, fieldName) {
            var def = type[defField];
            var originalValue = def[fieldName];
            this.defCleanupOps.push({ object: def, fieldName: fieldName, originalValue: originalValue });
        };
        /**
         * Clears current components resolution queue, but stores the state of the queue, so we can
         * restore it later. Clearing the queue is required before we try to compile components (via
         * `TestBed.compileComponents`), so that component defs are in sync with the resolution queue.
         */
        R3TestBedCompiler.prototype.clearComponentResolutionQueue = function () {
            var _this = this;
            if (this.originalComponentResolutionQueue === null) {
                this.originalComponentResolutionQueue = new Map();
            }
            clearResolutionOfComponentResourcesQueue().forEach(function (value, key) { return _this.originalComponentResolutionQueue.set(key, value); });
        };
        /*
         * Restores component resolution queue to the previously saved state. This operation is performed
         * as a part of restoring the state after completion of the current set of tests (that might
         * potentially mutate the state).
         */
        R3TestBedCompiler.prototype.restoreComponentResolutionQueue = function () {
            if (this.originalComponentResolutionQueue !== null) {
                restoreComponentResolutionQueue(this.originalComponentResolutionQueue);
                this.originalComponentResolutionQueue = null;
            }
        };
        R3TestBedCompiler.prototype.restoreOriginalState = function () {
            // Process cleanup ops in reverse order so the field's original value is restored correctly (in
            // case there were multiple overrides for the same field).
            forEachRight(this.defCleanupOps, function (op) {
                op.object[op.fieldName] = op.originalValue;
            });
            // Restore initial component/directive/pipe defs
            this.initialNgDefs.forEach(function (value, type) {
                var _a = __read(value, 2), prop = _a[0], descriptor = _a[1];
                if (!descriptor) {
                    // Delete operations are generally undesirable since they have performance implications
                    // on objects they were applied to. In this particular case, situations where this code
                    // is invoked should be quite rare to cause any noticeable impact, since it's applied
                    // only to some test cases (for example when class with no annotations extends some
                    // @Component) when we need to clear 'ɵcmp' field on a given class to restore
                    // its original state (before applying overrides and running tests).
                    delete type[prop];
                }
                else {
                    Object.defineProperty(type, prop, descriptor);
                }
            });
            this.initialNgDefs.clear();
            this.moduleProvidersOverridden.clear();
            this.restoreComponentResolutionQueue();
            // Restore the locale ID to the default value, this shouldn't be necessary but we never know
            core.ɵsetLocaleId(core.ɵDEFAULT_LOCALE_ID);
        };
        R3TestBedCompiler.prototype.compileTestModule = function () {
            var _this = this;
            var RootScopeModule = /** @class */ (function () {
                function RootScopeModule() {
                }
                return RootScopeModule;
            }());
            core.ɵcompileNgModuleDefs(RootScopeModule, {
                providers: __spread(this.rootProviderOverrides),
            });
            var ngZone = new core.NgZone({ enableLongStackTrace: true });
            var providers = __spread([
                { provide: core.NgZone, useValue: ngZone },
                { provide: core.Compiler, useFactory: function () { return new R3TestCompiler(_this); } }
            ], this.providers, this.providerOverrides);
            var imports = [RootScopeModule, this.additionalModuleTypes, this.imports || []];
            // clang-format off
            core.ɵcompileNgModuleDefs(this.testModuleType, {
                declarations: this.declarations,
                imports: imports,
                schemas: this.schemas,
                providers: providers,
            }, /* allowDuplicateDeclarationsInRoot */ true);
            // clang-format on
            this.applyProviderOverridesToModule(this.testModuleType);
        };
        Object.defineProperty(R3TestBedCompiler.prototype, "injector", {
            get: function () {
                if (this._injector !== null) {
                    return this._injector;
                }
                var providers = [];
                var compilerOptions = this.platform.injector.get(core.COMPILER_OPTIONS);
                compilerOptions.forEach(function (opts) {
                    if (opts.providers) {
                        providers.push(opts.providers);
                    }
                });
                if (this.compilerProviders !== null) {
                    providers.push.apply(providers, __spread(this.compilerProviders));
                }
                // TODO(ocombe): make this work with an Injector directly instead of creating a module for it
                var CompilerModule = /** @class */ (function () {
                    function CompilerModule() {
                    }
                    return CompilerModule;
                }());
                core.ɵcompileNgModuleDefs(CompilerModule, { providers: providers });
                var CompilerModuleFactory = new core.ɵNgModuleFactory(CompilerModule);
                this._injector = CompilerModuleFactory.create(this.platform.injector).injector;
                return this._injector;
            },
            enumerable: false,
            configurable: true
        });
        // get overrides for a specific provider (if any)
        R3TestBedCompiler.prototype.getSingleProviderOverrides = function (provider) {
            var token = getProviderToken(provider);
            return this.providerOverridesByToken.get(token) || null;
        };
        R3TestBedCompiler.prototype.getProviderOverrides = function (providers) {
            var _this = this;
            if (!providers || !providers.length || this.providerOverridesByToken.size === 0)
                return [];
            // There are two flattening operations here. The inner flatten() operates on the metadata's
            // providers and applies a mapping function which retrieves overrides for each incoming
            // provider. The outer flatten() then flattens the produced overrides array. If this is not
            // done, the array can contain other empty arrays (e.g. `[[], []]`) which leak into the
            // providers array and contaminate any error messages that might be generated.
            return flatten(flatten(providers, function (provider) { return _this.getSingleProviderOverrides(provider) || []; }));
        };
        R3TestBedCompiler.prototype.getOverriddenProviders = function (providers) {
            var _this = this;
            if (!providers || !providers.length || this.providerOverridesByToken.size === 0)
                return [];
            var flattenedProviders = flatten(providers);
            var overrides = this.getProviderOverrides(flattenedProviders);
            var overriddenProviders = __spread(flattenedProviders, overrides);
            var final = [];
            var seenOverriddenProviders = new Set();
            // We iterate through the list of providers in reverse order to make sure provider overrides
            // take precedence over the values defined in provider list. We also filter out all providers
            // that have overrides, keeping overridden values only. This is needed, since presence of a
            // provider with `ngOnDestroy` hook will cause this hook to be registered and invoked later.
            forEachRight(overriddenProviders, function (provider) {
                var token = getProviderToken(provider);
                if (_this.providerOverridesByToken.has(token)) {
                    if (!seenOverriddenProviders.has(token)) {
                        seenOverriddenProviders.add(token);
                        // Treat all overridden providers as `{multi: false}` (even if it's a multi-provider) to
                        // make sure that provided override takes highest precedence and is not combined with
                        // other instances of the same multi provider.
                        final.unshift(Object.assign(Object.assign({}, provider), { multi: false }));
                    }
                }
                else {
                    final.unshift(provider);
                }
            });
            return final;
        };
        R3TestBedCompiler.prototype.hasProviderOverrides = function (providers) {
            return this.getProviderOverrides(providers).length > 0;
        };
        R3TestBedCompiler.prototype.patchDefWithProviderOverrides = function (declaration, field) {
            var _this = this;
            var def = declaration[field];
            if (def && def.providersResolver) {
                this.maybeStoreNgDef(field, declaration);
                var resolver_1 = def.providersResolver;
                var processProvidersFn_1 = function (providers) { return _this.getOverriddenProviders(providers); };
                this.storeFieldOfDefOnType(declaration, field, 'providersResolver');
                def.providersResolver = function (ngDef) { return resolver_1(ngDef, processProvidersFn_1); };
            }
        };
        return R3TestBedCompiler;
    }());
    function initResolvers() {
        return {
            module: new NgModuleResolver(),
            component: new ComponentResolver(),
            directive: new DirectiveResolver(),
            pipe: new PipeResolver()
        };
    }
    function hasNgModuleDef(value) {
        return value.hasOwnProperty('ɵmod');
    }
    function maybeUnwrapFn(maybeFn) {
        return maybeFn instanceof Function ? maybeFn() : maybeFn;
    }
    function flatten(values, mapFn) {
        var out = [];
        values.forEach(function (value) {
            if (Array.isArray(value)) {
                out.push.apply(out, __spread(flatten(value, mapFn)));
            }
            else {
                out.push(mapFn ? mapFn(value) : value);
            }
        });
        return out;
    }
    function getProviderField(provider, field) {
        return provider && typeof provider === 'object' && provider[field];
    }
    function getProviderToken(provider) {
        return getProviderField(provider, 'provide') || provider;
    }
    function isModuleWithProviders(value) {
        return value.hasOwnProperty('ngModule');
    }
    function forEachRight(values, fn) {
        for (var idx = values.length - 1; idx >= 0; idx--) {
            fn(values[idx], idx);
        }
    }
    function invalidTypeError(name, expectedType) {
        return new Error(name + " class doesn't have @" + expectedType + " decorator or is missing metadata.");
    }
    var R3TestCompiler = /** @class */ (function () {
        function R3TestCompiler(testBed) {
            this.testBed = testBed;
        }
        R3TestCompiler.prototype.compileModuleSync = function (moduleType) {
            this.testBed._compileNgModuleSync(moduleType);
            return new core.ɵNgModuleFactory(moduleType);
        };
        R3TestCompiler.prototype.compileModuleAsync = function (moduleType) {
            return __awaiter(this, void 0, void 0, function () {
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4 /*yield*/, this.testBed._compileNgModuleAsync(moduleType)];
                        case 1:
                            _a.sent();
                            return [2 /*return*/, new core.ɵNgModuleFactory(moduleType)];
                    }
                });
            });
        };
        R3TestCompiler.prototype.compileModuleAndAllComponentsSync = function (moduleType) {
            var ngModuleFactory = this.compileModuleSync(moduleType);
            var componentFactories = this.testBed._getComponentFactories(moduleType);
            return new core.ModuleWithComponentFactories(ngModuleFactory, componentFactories);
        };
        R3TestCompiler.prototype.compileModuleAndAllComponentsAsync = function (moduleType) {
            return __awaiter(this, void 0, void 0, function () {
                var ngModuleFactory, componentFactories;
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4 /*yield*/, this.compileModuleAsync(moduleType)];
                        case 1:
                            ngModuleFactory = _a.sent();
                            componentFactories = this.testBed._getComponentFactories(moduleType);
                            return [2 /*return*/, new core.ModuleWithComponentFactories(ngModuleFactory, componentFactories)];
                    }
                });
            });
        };
        R3TestCompiler.prototype.clearCache = function () { };
        R3TestCompiler.prototype.clearCacheFor = function (type) { };
        R3TestCompiler.prototype.getModuleId = function (moduleType) {
            var meta = this.testBed._getModuleResolver().resolve(moduleType);
            return meta && meta.id || undefined;
        };
        return R3TestCompiler;
    }());

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * An abstract class for inserting the root test component element in a platform independent way.
     *
     * @publicApi
     */
    var TestComponentRenderer = /** @class */ (function () {
        function TestComponentRenderer() {
        }
        TestComponentRenderer.prototype.insertRootElement = function (rootElementId) { };
        return TestComponentRenderer;
    }());
    /**
     * @publicApi
     */
    var ComponentFixtureAutoDetect = new core.InjectionToken('ComponentFixtureAutoDetect');
    /**
     * @publicApi
     */
    var ComponentFixtureNoNgZone = new core.InjectionToken('ComponentFixtureNoNgZone');

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var _nextRootElementId = 0;
    /**
     * @description
     * Configures and initializes environment for unit testing and provides methods for
     * creating components and services in unit tests.
     *
     * TestBed is the primary api for writing unit tests for Angular applications and libraries.
     *
     * Note: Use `TestBed` in tests. It will be set to either `TestBedViewEngine` or `TestBedRender3`
     * according to the compiler used.
     */
    var TestBedRender3 = /** @class */ (function () {
        function TestBedRender3() {
            // Properties
            this.platform = null;
            this.ngModule = null;
            this._compiler = null;
            this._testModuleRef = null;
            this._activeFixtures = [];
            this._globalCompilationChecked = false;
        }
        /**
         * Initialize the environment for testing with a compiler factory, a PlatformRef, and an
         * angular module. These are common to every test in the suite.
         *
         * This may only be called once, to set up the common providers for the current test
         * suite on the current platform. If you absolutely need to change the providers,
         * first use `resetTestEnvironment`.
         *
         * Test modules and platforms for individual platforms are available from
         * '@angular/<platform_name>/testing'.
         *
         * @publicApi
         */
        TestBedRender3.initTestEnvironment = function (ngModule, platform, aotSummaries) {
            var testBed = _getTestBedRender3();
            testBed.initTestEnvironment(ngModule, platform, aotSummaries);
            return testBed;
        };
        /**
         * Reset the providers for the test injector.
         *
         * @publicApi
         */
        TestBedRender3.resetTestEnvironment = function () {
            _getTestBedRender3().resetTestEnvironment();
        };
        TestBedRender3.configureCompiler = function (config) {
            _getTestBedRender3().configureCompiler(config);
            return TestBedRender3;
        };
        /**
         * Allows overriding default providers, directives, pipes, modules of the test injector,
         * which are defined in test_injector.js
         */
        TestBedRender3.configureTestingModule = function (moduleDef) {
            _getTestBedRender3().configureTestingModule(moduleDef);
            return TestBedRender3;
        };
        /**
         * Compile components with a `templateUrl` for the test's NgModule.
         * It is necessary to call this function
         * as fetching urls is asynchronous.
         */
        TestBedRender3.compileComponents = function () {
            return _getTestBedRender3().compileComponents();
        };
        TestBedRender3.overrideModule = function (ngModule, override) {
            _getTestBedRender3().overrideModule(ngModule, override);
            return TestBedRender3;
        };
        TestBedRender3.overrideComponent = function (component, override) {
            _getTestBedRender3().overrideComponent(component, override);
            return TestBedRender3;
        };
        TestBedRender3.overrideDirective = function (directive, override) {
            _getTestBedRender3().overrideDirective(directive, override);
            return TestBedRender3;
        };
        TestBedRender3.overridePipe = function (pipe, override) {
            _getTestBedRender3().overridePipe(pipe, override);
            return TestBedRender3;
        };
        TestBedRender3.overrideTemplate = function (component, template) {
            _getTestBedRender3().overrideComponent(component, { set: { template: template, templateUrl: null } });
            return TestBedRender3;
        };
        /**
         * Overrides the template of the given component, compiling the template
         * in the context of the TestingModule.
         *
         * Note: This works for JIT and AOTed components as well.
         */
        TestBedRender3.overrideTemplateUsingTestingModule = function (component, template) {
            _getTestBedRender3().overrideTemplateUsingTestingModule(component, template);
            return TestBedRender3;
        };
        TestBedRender3.overrideProvider = function (token, provider) {
            _getTestBedRender3().overrideProvider(token, provider);
            return TestBedRender3;
        };
        TestBedRender3.inject = function (token, notFoundValue, flags) {
            return _getTestBedRender3().inject(token, notFoundValue, flags);
        };
        /** @deprecated from v9.0.0 use TestBed.inject */
        TestBedRender3.get = function (token, notFoundValue, flags) {
            if (notFoundValue === void 0) { notFoundValue = core.Injector.THROW_IF_NOT_FOUND; }
            if (flags === void 0) { flags = core.InjectFlags.Default; }
            return _getTestBedRender3().inject(token, notFoundValue, flags);
        };
        TestBedRender3.createComponent = function (component) {
            return _getTestBedRender3().createComponent(component);
        };
        TestBedRender3.resetTestingModule = function () {
            _getTestBedRender3().resetTestingModule();
            return TestBedRender3;
        };
        /**
         * Initialize the environment for testing with a compiler factory, a PlatformRef, and an
         * angular module. These are common to every test in the suite.
         *
         * This may only be called once, to set up the common providers for the current test
         * suite on the current platform. If you absolutely need to change the providers,
         * first use `resetTestEnvironment`.
         *
         * Test modules and platforms for individual platforms are available from
         * '@angular/<platform_name>/testing'.
         *
         * @publicApi
         */
        TestBedRender3.prototype.initTestEnvironment = function (ngModule, platform, aotSummaries) {
            if (this.platform || this.ngModule) {
                throw new Error('Cannot set base providers because it has already been called');
            }
            this.platform = platform;
            this.ngModule = ngModule;
            this._compiler = new R3TestBedCompiler(this.platform, this.ngModule);
        };
        /**
         * Reset the providers for the test injector.
         *
         * @publicApi
         */
        TestBedRender3.prototype.resetTestEnvironment = function () {
            this.resetTestingModule();
            this._compiler = null;
            this.platform = null;
            this.ngModule = null;
        };
        TestBedRender3.prototype.resetTestingModule = function () {
            this.checkGlobalCompilationFinished();
            core.ɵresetCompiledComponents();
            if (this._compiler !== null) {
                this.compiler.restoreOriginalState();
            }
            this._compiler = new R3TestBedCompiler(this.platform, this.ngModule);
            this._testModuleRef = null;
            this.destroyActiveFixtures();
        };
        TestBedRender3.prototype.configureCompiler = function (config) {
            if (config.useJit != null) {
                throw new Error('the Render3 compiler JiT mode is not configurable !');
            }
            if (config.providers !== undefined) {
                this.compiler.setCompilerProviders(config.providers);
            }
        };
        TestBedRender3.prototype.configureTestingModule = function (moduleDef) {
            this.assertNotInstantiated('R3TestBed.configureTestingModule', 'configure the test module');
            this.compiler.configureTestingModule(moduleDef);
        };
        TestBedRender3.prototype.compileComponents = function () {
            return this.compiler.compileComponents();
        };
        TestBedRender3.prototype.inject = function (token, notFoundValue, flags) {
            if (token === TestBedRender3) {
                return this;
            }
            var UNDEFINED = {};
            var result = this.testModuleRef.injector.get(token, UNDEFINED, flags);
            return result === UNDEFINED ? this.compiler.injector.get(token, notFoundValue, flags) :
                result;
        };
        /** @deprecated from v9.0.0 use TestBed.inject */
        TestBedRender3.prototype.get = function (token, notFoundValue, flags) {
            if (notFoundValue === void 0) { notFoundValue = core.Injector.THROW_IF_NOT_FOUND; }
            if (flags === void 0) { flags = core.InjectFlags.Default; }
            return this.inject(token, notFoundValue, flags);
        };
        TestBedRender3.prototype.execute = function (tokens, fn, context) {
            var _this = this;
            var params = tokens.map(function (t) { return _this.inject(t); });
            return fn.apply(context, params);
        };
        TestBedRender3.prototype.overrideModule = function (ngModule, override) {
            this.assertNotInstantiated('overrideModule', 'override module metadata');
            this.compiler.overrideModule(ngModule, override);
        };
        TestBedRender3.prototype.overrideComponent = function (component, override) {
            this.assertNotInstantiated('overrideComponent', 'override component metadata');
            this.compiler.overrideComponent(component, override);
        };
        TestBedRender3.prototype.overrideTemplateUsingTestingModule = function (component, template) {
            this.assertNotInstantiated('R3TestBed.overrideTemplateUsingTestingModule', 'Cannot override template when the test module has already been instantiated');
            this.compiler.overrideTemplateUsingTestingModule(component, template);
        };
        TestBedRender3.prototype.overrideDirective = function (directive, override) {
            this.assertNotInstantiated('overrideDirective', 'override directive metadata');
            this.compiler.overrideDirective(directive, override);
        };
        TestBedRender3.prototype.overridePipe = function (pipe, override) {
            this.assertNotInstantiated('overridePipe', 'override pipe metadata');
            this.compiler.overridePipe(pipe, override);
        };
        /**
         * Overwrites all providers for the given token with the given provider definition.
         */
        TestBedRender3.prototype.overrideProvider = function (token, provider) {
            this.assertNotInstantiated('overrideProvider', 'override provider');
            this.compiler.overrideProvider(token, provider);
        };
        TestBedRender3.prototype.createComponent = function (type) {
            var _this = this;
            var testComponentRenderer = this.inject(TestComponentRenderer);
            var rootElId = "root" + _nextRootElementId++;
            testComponentRenderer.insertRootElement(rootElId);
            var componentDef = type.ɵcmp;
            if (!componentDef) {
                throw new Error("It looks like '" + core.ɵstringify(type) + "' has not been IVY compiled - it has no '\u0275cmp' field");
            }
            // TODO: Don't cast as `InjectionToken<boolean>`, proper type is boolean[]
            var noNgZone = this.inject(ComponentFixtureNoNgZone, false);
            // TODO: Don't cast as `InjectionToken<boolean>`, proper type is boolean[]
            var autoDetect = this.inject(ComponentFixtureAutoDetect, false);
            var ngZone = noNgZone ? null : this.inject(core.NgZone, null);
            var componentFactory = new core.ɵRender3ComponentFactory(componentDef);
            var initComponent = function () {
                var componentRef = componentFactory.create(core.Injector.NULL, [], "#" + rootElId, _this.testModuleRef);
                return new ComponentFixture(componentRef, ngZone, autoDetect);
            };
            var fixture = ngZone ? ngZone.run(initComponent) : initComponent();
            this._activeFixtures.push(fixture);
            return fixture;
        };
        Object.defineProperty(TestBedRender3.prototype, "compiler", {
            /**
             * @internal strip this from published d.ts files due to
             * https://github.com/microsoft/TypeScript/issues/36216
             */
            get: function () {
                if (this._compiler === null) {
                    throw new Error("Need to call TestBed.initTestEnvironment() first");
                }
                return this._compiler;
            },
            enumerable: false,
            configurable: true
        });
        Object.defineProperty(TestBedRender3.prototype, "testModuleRef", {
            /**
             * @internal strip this from published d.ts files due to
             * https://github.com/microsoft/TypeScript/issues/36216
             */
            get: function () {
                if (this._testModuleRef === null) {
                    this._testModuleRef = this.compiler.finalize();
                }
                return this._testModuleRef;
            },
            enumerable: false,
            configurable: true
        });
        TestBedRender3.prototype.assertNotInstantiated = function (methodName, methodDescription) {
            if (this._testModuleRef !== null) {
                throw new Error("Cannot " + methodDescription + " when the test module has already been instantiated. " +
                    ("Make sure you are not using `inject` before `" + methodName + "`."));
            }
        };
        /**
         * Check whether the module scoping queue should be flushed, and flush it if needed.
         *
         * When the TestBed is reset, it clears the JIT module compilation queue, cancelling any
         * in-progress module compilation. This creates a potential hazard - the very first time the
         * TestBed is initialized (or if it's reset without being initialized), there may be pending
         * compilations of modules declared in global scope. These compilations should be finished.
         *
         * To ensure that globally declared modules have their components scoped properly, this function
         * is called whenever TestBed is initialized or reset. The _first_ time that this happens, prior
         * to any other operations, the scoping queue is flushed.
         */
        TestBedRender3.prototype.checkGlobalCompilationFinished = function () {
            // Checking _testNgModuleRef is null should not be necessary, but is left in as an additional
            // guard that compilations queued in tests (after instantiation) are never flushed accidentally.
            if (!this._globalCompilationChecked && this._testModuleRef === null) {
                core.ɵflushModuleScopingQueueAsMuchAsPossible();
            }
            this._globalCompilationChecked = true;
        };
        TestBedRender3.prototype.destroyActiveFixtures = function () {
            this._activeFixtures.forEach(function (fixture) {
                try {
                    fixture.destroy();
                }
                catch (e) {
                    console.error('Error during cleanup of component', {
                        component: fixture.componentInstance,
                        stacktrace: e,
                    });
                }
            });
            this._activeFixtures = [];
        };
        return TestBedRender3;
    }());
    var testBed;
    function _getTestBedRender3() {
        return testBed = testBed || new TestBedRender3();
    }

    function unimplemented() {
        throw Error('unimplemented');
    }
    /**
     * Special interface to the compiler only used by testing
     *
     * @publicApi
     */
    var TestingCompiler = /** @class */ (function (_super) {
        __extends(TestingCompiler, _super);
        function TestingCompiler() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        Object.defineProperty(TestingCompiler.prototype, "injector", {
            get: function () {
                throw unimplemented();
            },
            enumerable: false,
            configurable: true
        });
        TestingCompiler.prototype.overrideModule = function (module, overrides) {
            throw unimplemented();
        };
        TestingCompiler.prototype.overrideDirective = function (directive, overrides) {
            throw unimplemented();
        };
        TestingCompiler.prototype.overrideComponent = function (component, overrides) {
            throw unimplemented();
        };
        TestingCompiler.prototype.overridePipe = function (directive, overrides) {
            throw unimplemented();
        };
        /**
         * Allows to pass the compile summary from AOT compilation to the JIT compiler,
         * so that it can use the code generated by AOT.
         */
        TestingCompiler.prototype.loadAotSummaries = function (summaries) {
            throw unimplemented();
        };
        /**
         * Gets the component factory for the given component.
         * This assumes that the component has been compiled before calling this call using
         * `compileModuleAndAllComponents*`.
         */
        TestingCompiler.prototype.getComponentFactory = function (component) {
            throw unimplemented();
        };
        /**
         * Returns the component type that is stored in the given error.
         * This can be used for errors created by compileModule...
         */
        TestingCompiler.prototype.getComponentFromError = function (error) {
            throw unimplemented();
        };
        return TestingCompiler;
    }(core.Compiler));
    TestingCompiler.decorators = [
        { type: core.Injectable }
    ];
    /**
     * A factory for creating a Compiler
     *
     * @publicApi
     */
    var TestingCompilerFactory = /** @class */ (function () {
        function TestingCompilerFactory() {
        }
        return TestingCompilerFactory;
    }());

    var _nextRootElementId$1 = 0;
    /**
     * @description
     * Configures and initializes environment for unit testing and provides methods for
     * creating components and services in unit tests.
     *
     * `TestBed` is the primary api for writing unit tests for Angular applications and libraries.
     *
     * Note: Use `TestBed` in tests. It will be set to either `TestBedViewEngine` or `TestBedRender3`
     * according to the compiler used.
     */
    var TestBedViewEngine = /** @class */ (function () {
        function TestBedViewEngine() {
            this._instantiated = false;
            this._compiler = null;
            this._moduleRef = null;
            this._moduleFactory = null;
            this._compilerOptions = [];
            this._moduleOverrides = [];
            this._componentOverrides = [];
            this._directiveOverrides = [];
            this._pipeOverrides = [];
            this._providers = [];
            this._declarations = [];
            this._imports = [];
            this._schemas = [];
            this._activeFixtures = [];
            this._testEnvAotSummaries = function () { return []; };
            this._aotSummaries = [];
            this._templateOverrides = [];
            this._isRoot = true;
            this._rootProviderOverrides = [];
            this.platform = null;
            this.ngModule = null;
        }
        /**
         * Initialize the environment for testing with a compiler factory, a PlatformRef, and an
         * angular module. These are common to every test in the suite.
         *
         * This may only be called once, to set up the common providers for the current test
         * suite on the current platform. If you absolutely need to change the providers,
         * first use `resetTestEnvironment`.
         *
         * Test modules and platforms for individual platforms are available from
         * '@angular/<platform_name>/testing'.
         */
        TestBedViewEngine.initTestEnvironment = function (ngModule, platform, aotSummaries) {
            var testBed = _getTestBedViewEngine();
            testBed.initTestEnvironment(ngModule, platform, aotSummaries);
            return testBed;
        };
        /**
         * Reset the providers for the test injector.
         */
        TestBedViewEngine.resetTestEnvironment = function () {
            _getTestBedViewEngine().resetTestEnvironment();
        };
        TestBedViewEngine.resetTestingModule = function () {
            _getTestBedViewEngine().resetTestingModule();
            return TestBedViewEngine;
        };
        /**
         * Allows overriding default compiler providers and settings
         * which are defined in test_injector.js
         */
        TestBedViewEngine.configureCompiler = function (config) {
            _getTestBedViewEngine().configureCompiler(config);
            return TestBedViewEngine;
        };
        /**
         * Allows overriding default providers, directives, pipes, modules of the test injector,
         * which are defined in test_injector.js
         */
        TestBedViewEngine.configureTestingModule = function (moduleDef) {
            _getTestBedViewEngine().configureTestingModule(moduleDef);
            return TestBedViewEngine;
        };
        /**
         * Compile components with a `templateUrl` for the test's NgModule.
         * It is necessary to call this function
         * as fetching urls is asynchronous.
         */
        TestBedViewEngine.compileComponents = function () {
            return getTestBed().compileComponents();
        };
        TestBedViewEngine.overrideModule = function (ngModule, override) {
            _getTestBedViewEngine().overrideModule(ngModule, override);
            return TestBedViewEngine;
        };
        TestBedViewEngine.overrideComponent = function (component, override) {
            _getTestBedViewEngine().overrideComponent(component, override);
            return TestBedViewEngine;
        };
        TestBedViewEngine.overrideDirective = function (directive, override) {
            _getTestBedViewEngine().overrideDirective(directive, override);
            return TestBedViewEngine;
        };
        TestBedViewEngine.overridePipe = function (pipe, override) {
            _getTestBedViewEngine().overridePipe(pipe, override);
            return TestBedViewEngine;
        };
        TestBedViewEngine.overrideTemplate = function (component, template) {
            _getTestBedViewEngine().overrideComponent(component, { set: { template: template, templateUrl: null } });
            return TestBedViewEngine;
        };
        /**
         * Overrides the template of the given component, compiling the template
         * in the context of the TestingModule.
         *
         * Note: This works for JIT and AOTed components as well.
         */
        TestBedViewEngine.overrideTemplateUsingTestingModule = function (component, template) {
            _getTestBedViewEngine().overrideTemplateUsingTestingModule(component, template);
            return TestBedViewEngine;
        };
        TestBedViewEngine.overrideProvider = function (token, provider) {
            _getTestBedViewEngine().overrideProvider(token, provider);
            return TestBedViewEngine;
        };
        TestBedViewEngine.inject = function (token, notFoundValue, flags) {
            return _getTestBedViewEngine().inject(token, notFoundValue, flags);
        };
        /** @deprecated from v9.0.0 use TestBed.inject */
        TestBedViewEngine.get = function (token, notFoundValue, flags) {
            if (notFoundValue === void 0) { notFoundValue = core.Injector.THROW_IF_NOT_FOUND; }
            if (flags === void 0) { flags = core.InjectFlags.Default; }
            return _getTestBedViewEngine().inject(token, notFoundValue, flags);
        };
        TestBedViewEngine.createComponent = function (component) {
            return _getTestBedViewEngine().createComponent(component);
        };
        /**
         * Initialize the environment for testing with a compiler factory, a PlatformRef, and an
         * angular module. These are common to every test in the suite.
         *
         * This may only be called once, to set up the common providers for the current test
         * suite on the current platform. If you absolutely need to change the providers,
         * first use `resetTestEnvironment`.
         *
         * Test modules and platforms for individual platforms are available from
         * '@angular/<platform_name>/testing'.
         */
        TestBedViewEngine.prototype.initTestEnvironment = function (ngModule, platform, aotSummaries) {
            if (this.platform || this.ngModule) {
                throw new Error('Cannot set base providers because it has already been called');
            }
            this.platform = platform;
            this.ngModule = ngModule;
            if (aotSummaries) {
                this._testEnvAotSummaries = aotSummaries;
            }
        };
        /**
         * Reset the providers for the test injector.
         */
        TestBedViewEngine.prototype.resetTestEnvironment = function () {
            this.resetTestingModule();
            this.platform = null;
            this.ngModule = null;
            this._testEnvAotSummaries = function () { return []; };
        };
        TestBedViewEngine.prototype.resetTestingModule = function () {
            core.ɵclearOverrides();
            this._aotSummaries = [];
            this._templateOverrides = [];
            this._compiler = null;
            this._moduleOverrides = [];
            this._componentOverrides = [];
            this._directiveOverrides = [];
            this._pipeOverrides = [];
            this._isRoot = true;
            this._rootProviderOverrides = [];
            this._moduleRef = null;
            this._moduleFactory = null;
            this._compilerOptions = [];
            this._providers = [];
            this._declarations = [];
            this._imports = [];
            this._schemas = [];
            this._instantiated = false;
            this._activeFixtures.forEach(function (fixture) {
                try {
                    fixture.destroy();
                }
                catch (e) {
                    console.error('Error during cleanup of component', {
                        component: fixture.componentInstance,
                        stacktrace: e,
                    });
                }
            });
            this._activeFixtures = [];
        };
        TestBedViewEngine.prototype.configureCompiler = function (config) {
            this._assertNotInstantiated('TestBed.configureCompiler', 'configure the compiler');
            this._compilerOptions.push(config);
        };
        TestBedViewEngine.prototype.configureTestingModule = function (moduleDef) {
            var _a, _b, _c, _d;
            this._assertNotInstantiated('TestBed.configureTestingModule', 'configure the test module');
            if (moduleDef.providers) {
                (_a = this._providers).push.apply(_a, __spread(moduleDef.providers));
            }
            if (moduleDef.declarations) {
                (_b = this._declarations).push.apply(_b, __spread(moduleDef.declarations));
            }
            if (moduleDef.imports) {
                (_c = this._imports).push.apply(_c, __spread(moduleDef.imports));
            }
            if (moduleDef.schemas) {
                (_d = this._schemas).push.apply(_d, __spread(moduleDef.schemas));
            }
            if (moduleDef.aotSummaries) {
                this._aotSummaries.push(moduleDef.aotSummaries);
            }
        };
        TestBedViewEngine.prototype.compileComponents = function () {
            var _this = this;
            if (this._moduleFactory || this._instantiated) {
                return Promise.resolve(null);
            }
            var moduleType = this._createCompilerAndModule();
            return this._compiler.compileModuleAndAllComponentsAsync(moduleType)
                .then(function (moduleAndComponentFactories) {
                _this._moduleFactory = moduleAndComponentFactories.ngModuleFactory;
            });
        };
        TestBedViewEngine.prototype._initIfNeeded = function () {
            var e_1, _a;
            if (this._instantiated) {
                return;
            }
            if (!this._moduleFactory) {
                try {
                    var moduleType = this._createCompilerAndModule();
                    this._moduleFactory =
                        this._compiler.compileModuleAndAllComponentsSync(moduleType).ngModuleFactory;
                }
                catch (e) {
                    var errorCompType = this._compiler.getComponentFromError(e);
                    if (errorCompType) {
                        throw new Error("This test module uses the component " + core.ɵstringify(errorCompType) + " which is using a \"templateUrl\" or \"styleUrls\", but they were never compiled. " +
                            "Please call \"TestBed.compileComponents\" before your test.");
                    }
                    else {
                        throw e;
                    }
                }
            }
            try {
                for (var _b = __values(this._templateOverrides), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var _d = _c.value, component = _d.component, templateOf = _d.templateOf;
                    var compFactory = this._compiler.getComponentFactory(templateOf);
                    core.ɵoverrideComponentView(component, compFactory);
                }
            }
            catch (e_1_1) { e_1 = { error: e_1_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_1) throw e_1.error; }
            }
            var ngZone = new core.NgZone({ enableLongStackTrace: true, shouldCoalesceEventChangeDetection: false });
            var providers = [{ provide: core.NgZone, useValue: ngZone }];
            var ngZoneInjector = core.Injector.create({
                providers: providers,
                parent: this.platform.injector,
                name: this._moduleFactory.moduleType.name
            });
            this._moduleRef = this._moduleFactory.create(ngZoneInjector);
            // ApplicationInitStatus.runInitializers() is marked @internal to core. So casting to any
            // before accessing it.
            this._moduleRef.injector.get(core.ApplicationInitStatus).runInitializers();
            this._instantiated = true;
        };
        TestBedViewEngine.prototype._createCompilerAndModule = function () {
            var e_2, _a;
            var _this = this;
            var providers = this._providers.concat([{ provide: TestBed, useValue: this }]);
            var declarations = __spread(this._declarations, this._templateOverrides.map(function (entry) { return entry.templateOf; }));
            var rootScopeImports = [];
            var rootProviderOverrides = this._rootProviderOverrides;
            if (this._isRoot) {
                var RootScopeModule = /** @class */ (function () {
                    function RootScopeModule() {
                    }
                    return RootScopeModule;
                }());
                RootScopeModule.decorators = [
                    { type: core.NgModule, args: [{
                                providers: __spread(rootProviderOverrides),
                                jit: true,
                            },] }
                ];
                rootScopeImports.push(RootScopeModule);
            }
            providers.push({ provide: core.ɵINJECTOR_SCOPE, useValue: this._isRoot ? 'root' : null });
            var imports = [rootScopeImports, this.ngModule, this._imports];
            var schemas = this._schemas;
            var DynamicTestModule = /** @class */ (function () {
                function DynamicTestModule() {
                }
                return DynamicTestModule;
            }());
            DynamicTestModule.decorators = [
                { type: core.NgModule, args: [{ providers: providers, declarations: declarations, imports: imports, schemas: schemas, jit: true },] }
            ];
            var compilerFactory = this.platform.injector.get(TestingCompilerFactory);
            this._compiler = compilerFactory.createTestingCompiler(this._compilerOptions);
            try {
                for (var _b = __values(__spread([this._testEnvAotSummaries], this._aotSummaries)), _c = _b.next(); !_c.done; _c = _b.next()) {
                    var summary = _c.value;
                    this._compiler.loadAotSummaries(summary);
                }
            }
            catch (e_2_1) { e_2 = { error: e_2_1 }; }
            finally {
                try {
                    if (_c && !_c.done && (_a = _b.return)) _a.call(_b);
                }
                finally { if (e_2) throw e_2.error; }
            }
            this._moduleOverrides.forEach(function (entry) { return _this._compiler.overrideModule(entry[0], entry[1]); });
            this._componentOverrides.forEach(function (entry) { return _this._compiler.overrideComponent(entry[0], entry[1]); });
            this._directiveOverrides.forEach(function (entry) { return _this._compiler.overrideDirective(entry[0], entry[1]); });
            this._pipeOverrides.forEach(function (entry) { return _this._compiler.overridePipe(entry[0], entry[1]); });
            return DynamicTestModule;
        };
        TestBedViewEngine.prototype._assertNotInstantiated = function (methodName, methodDescription) {
            if (this._instantiated) {
                throw new Error("Cannot " + methodDescription + " when the test module has already been instantiated. " +
                    ("Make sure you are not using `inject` before `" + methodName + "`."));
            }
        };
        TestBedViewEngine.prototype.inject = function (token, notFoundValue, flags) {
            this._initIfNeeded();
            if (token === TestBed) {
                return this;
            }
            // Tests can inject things from the ng module and from the compiler,
            // but the ng module can't inject things from the compiler and vice versa.
            var UNDEFINED = {};
            var result = this._moduleRef.injector.get(token, UNDEFINED, flags);
            return result === UNDEFINED ? this._compiler.injector.get(token, notFoundValue, flags) :
                result;
        };
        /** @deprecated from v9.0.0 use TestBed.inject */
        TestBedViewEngine.prototype.get = function (token, notFoundValue, flags) {
            if (notFoundValue === void 0) { notFoundValue = core.Injector.THROW_IF_NOT_FOUND; }
            if (flags === void 0) { flags = core.InjectFlags.Default; }
            return this.inject(token, notFoundValue, flags);
        };
        TestBedViewEngine.prototype.execute = function (tokens, fn, context) {
            var _this = this;
            this._initIfNeeded();
            var params = tokens.map(function (t) { return _this.inject(t); });
            return fn.apply(context, params);
        };
        TestBedViewEngine.prototype.overrideModule = function (ngModule, override) {
            this._assertNotInstantiated('overrideModule', 'override module metadata');
            this._moduleOverrides.push([ngModule, override]);
        };
        TestBedViewEngine.prototype.overrideComponent = function (component, override) {
            this._assertNotInstantiated('overrideComponent', 'override component metadata');
            this._componentOverrides.push([component, override]);
        };
        TestBedViewEngine.prototype.overrideDirective = function (directive, override) {
            this._assertNotInstantiated('overrideDirective', 'override directive metadata');
            this._directiveOverrides.push([directive, override]);
        };
        TestBedViewEngine.prototype.overridePipe = function (pipe, override) {
            this._assertNotInstantiated('overridePipe', 'override pipe metadata');
            this._pipeOverrides.push([pipe, override]);
        };
        TestBedViewEngine.prototype.overrideProvider = function (token, provider) {
            this._assertNotInstantiated('overrideProvider', 'override provider');
            this.overrideProviderImpl(token, provider);
        };
        TestBedViewEngine.prototype.overrideProviderImpl = function (token, provider, deprecated) {
            if (deprecated === void 0) { deprecated = false; }
            var def = null;
            if (typeof token !== 'string' && (def = core.ɵgetInjectableDef(token)) && def.providedIn === 'root') {
                if (provider.useFactory) {
                    this._rootProviderOverrides.push({ provide: token, useFactory: provider.useFactory, deps: provider.deps || [] });
                }
                else {
                    this._rootProviderOverrides.push({ provide: token, useValue: provider.useValue });
                }
            }
            var flags = 0;
            var value;
            if (provider.useFactory) {
                flags |= 1024 /* TypeFactoryProvider */;
                value = provider.useFactory;
            }
            else {
                flags |= 256 /* TypeValueProvider */;
                value = provider.useValue;
            }
            var deps = (provider.deps || []).map(function (dep) {
                var depFlags = 0 /* None */;
                var depToken;
                if (Array.isArray(dep)) {
                    dep.forEach(function (entry) {
                        if (entry instanceof core.Optional) {
                            depFlags |= 2 /* Optional */;
                        }
                        else if (entry instanceof core.SkipSelf) {
                            depFlags |= 1 /* SkipSelf */;
                        }
                        else {
                            depToken = entry;
                        }
                    });
                }
                else {
                    depToken = dep;
                }
                return [depFlags, depToken];
            });
            core.ɵoverrideProvider({ token: token, flags: flags, deps: deps, value: value, deprecatedBehavior: deprecated });
        };
        TestBedViewEngine.prototype.overrideTemplateUsingTestingModule = function (component, template) {
            this._assertNotInstantiated('overrideTemplateUsingTestingModule', 'override template');
            var OverrideComponent = /** @class */ (function () {
                function OverrideComponent() {
                }
                return OverrideComponent;
            }());
            OverrideComponent.decorators = [
                { type: core.Component, args: [{ selector: 'empty', template: template, jit: true },] }
            ];
            this._templateOverrides.push({ component: component, templateOf: OverrideComponent });
        };
        TestBedViewEngine.prototype.createComponent = function (component) {
            var _this = this;
            this._initIfNeeded();
            var componentFactory = this._compiler.getComponentFactory(component);
            if (!componentFactory) {
                throw new Error("Cannot create the component " + core.ɵstringify(component) + " as it was not imported into the testing module!");
            }
            // TODO: Don't cast as `InjectionToken<boolean>`, declared type is boolean[]
            var noNgZone = this.inject(ComponentFixtureNoNgZone, false);
            // TODO: Don't cast as `InjectionToken<boolean>`, declared type is boolean[]
            var autoDetect = this.inject(ComponentFixtureAutoDetect, false);
            var ngZone = noNgZone ? null : this.inject(core.NgZone, null);
            var testComponentRenderer = this.inject(TestComponentRenderer);
            var rootElId = "root" + _nextRootElementId$1++;
            testComponentRenderer.insertRootElement(rootElId);
            var initComponent = function () {
                var componentRef = componentFactory.create(core.Injector.NULL, [], "#" + rootElId, _this._moduleRef);
                return new ComponentFixture(componentRef, ngZone, autoDetect);
            };
            var fixture = !ngZone ? initComponent() : ngZone.run(initComponent);
            this._activeFixtures.push(fixture);
            return fixture;
        };
        return TestBedViewEngine;
    }());
    /**
     * @description
     * Configures and initializes environment for unit testing and provides methods for
     * creating components and services in unit tests.
     *
     * `TestBed` is the primary api for writing unit tests for Angular applications and libraries.
     *
     * Note: Use `TestBed` in tests. It will be set to either `TestBedViewEngine` or `TestBedRender3`
     * according to the compiler used.
     *
     * @publicApi
     */
    var TestBed = core.ɵivyEnabled ? TestBedRender3 : TestBedViewEngine;
    /**
     * Returns a singleton of the applicable `TestBed`.
     *
     * It will be either an instance of `TestBedViewEngine` or `TestBedRender3`.
     *
     * @publicApi
     */
    var getTestBed = core.ɵivyEnabled ? _getTestBedRender3 : _getTestBedViewEngine;
    var testBed$1;
    function _getTestBedViewEngine() {
        return testBed$1 = testBed$1 || new TestBedViewEngine();
    }
    /**
     * Allows injecting dependencies in `beforeEach()` and `it()`.
     *
     * Example:
     *
     * ```
     * beforeEach(inject([Dependency, AClass], (dep, object) => {
     *   // some code that uses `dep` and `object`
     *   // ...
     * }));
     *
     * it('...', inject([AClass], (object) => {
     *   object.doSomething();
     *   expect(...);
     * })
     * ```
     *
     * Notes:
     * - inject is currently a function because of some Traceur limitation the syntax should
     * eventually
     *   becomes `it('...', @Inject (object: AClass, async: AsyncTestCompleter) => { ... });`
     *
     * @publicApi
     */
    function inject(tokens, fn) {
        var testBed = getTestBed();
        if (tokens.indexOf(AsyncTestCompleter) >= 0) {
            // Not using an arrow function to preserve context passed from call site
            return function () {
                var _this = this;
                // Return an async test method that returns a Promise if AsyncTestCompleter is one of
                // the injected tokens.
                return testBed.compileComponents().then(function () {
                    var completer = testBed.inject(AsyncTestCompleter);
                    testBed.execute(tokens, fn, _this);
                    return completer.promise;
                });
            };
        }
        else {
            // Not using an arrow function to preserve context passed from call site
            return function () {
                return testBed.execute(tokens, fn, this);
            };
        }
    }
    /**
     * @publicApi
     */
    var InjectSetupWrapper = /** @class */ (function () {
        function InjectSetupWrapper(_moduleDef) {
            this._moduleDef = _moduleDef;
        }
        InjectSetupWrapper.prototype._addModule = function () {
            var moduleDef = this._moduleDef();
            if (moduleDef) {
                getTestBed().configureTestingModule(moduleDef);
            }
        };
        InjectSetupWrapper.prototype.inject = function (tokens, fn) {
            var self = this;
            // Not using an arrow function to preserve context passed from call site
            return function () {
                self._addModule();
                return inject(tokens, fn).call(this);
            };
        };
        return InjectSetupWrapper;
    }());
    function withModule(moduleDef, fn) {
        if (fn) {
            // Not using an arrow function to preserve context passed from call site
            return function () {
                var testBed = getTestBed();
                if (moduleDef) {
                    testBed.configureTestingModule(moduleDef);
                }
                return fn.apply(this);
            };
        }
        return new InjectSetupWrapper(function () { return moduleDef; });
    }

    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var _global = (typeof window === 'undefined' ? global : window);
    // Reset the test providers and the fake async zone before each test.
    if (_global.beforeEach) {
        _global.beforeEach(function () {
            TestBed.resetTestingModule();
            resetFakeAsyncZone();
        });
    }
    /**
     * This API should be removed. But doing so seems to break `google3` and so it requires a bit of
     * investigation.
     *
     * A work around is to mark it as `@codeGenApi` for now and investigate later.
     *
     * @codeGenApi
     */
    // TODO(iminar): Remove this code in a safe way.
    var __core_private_testing_placeholder__ = '';

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

    exports.ComponentFixture = ComponentFixture;
    exports.ComponentFixtureAutoDetect = ComponentFixtureAutoDetect;
    exports.ComponentFixtureNoNgZone = ComponentFixtureNoNgZone;
    exports.InjectSetupWrapper = InjectSetupWrapper;
    exports.TestBed = TestBed;
    exports.TestComponentRenderer = TestComponentRenderer;
    exports.__core_private_testing_placeholder__ = __core_private_testing_placeholder__;
    exports.async = async;
    exports.discardPeriodicTasks = discardPeriodicTasks;
    exports.fakeAsync = fakeAsync;
    exports.flush = flush;
    exports.flushMicrotasks = flushMicrotasks;
    exports.getTestBed = getTestBed;
    exports.inject = inject;
    exports.resetFakeAsyncZone = resetFakeAsyncZone;
    exports.tick = tick;
    exports.waitForAsync = waitForAsync;
    exports.withModule = withModule;
    exports.ɵMetadataOverrider = MetadataOverrider;
    exports.ɵTestingCompiler = TestingCompiler;
    exports.ɵTestingCompilerFactory = TestingCompilerFactory;
    exports.ɵangular_packages_core_testing_testing_a = TestBedViewEngine;
    exports.ɵangular_packages_core_testing_testing_b = TestBedRender3;
    exports.ɵangular_packages_core_testing_testing_c = _getTestBedRender3;

    Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=core-testing.umd.js.map
