'use strict';
/**
 * @license Angular v12.0.0-next.0
 * (c) 2010-2020 Google LLC. https://angular.io/
 * License: MIT
 */
(function (factory) {
    typeof define === 'function' && define.amd ? define(factory) :
        factory();
}((function () {
    'use strict';
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('jasmine', function (global, Zone, api) {
        var __extends = function (d, b) {
            for (var p in b)
                if (b.hasOwnProperty(p))
                    d[p] = b[p];
            function __() {
                this.constructor = d;
            }
            d.prototype = b === null ? Object.create(b) : ((__.prototype = b.prototype), new __());
        };
        // Patch jasmine's describe/it/beforeEach/afterEach functions so test code always runs
        // in a testZone (ProxyZone). (See: angular/zone.js#91 & angular/angular#10503)
        if (!Zone)
            throw new Error('Missing: zone.js');
        if (typeof jest !== 'undefined') {
            // return if jasmine is a light implementation inside jest
            // in this case, we are running inside jest not jasmine
            return;
        }
        if (typeof jasmine == 'undefined' || jasmine['__zone_patch__']) {
            return;
        }
        jasmine['__zone_patch__'] = true;
        var SyncTestZoneSpec = Zone['SyncTestZoneSpec'];
        var ProxyZoneSpec = Zone['ProxyZoneSpec'];
        if (!SyncTestZoneSpec)
            throw new Error('Missing: SyncTestZoneSpec');
        if (!ProxyZoneSpec)
            throw new Error('Missing: ProxyZoneSpec');
        var ambientZone = Zone.current;
        // Create a synchronous-only zone in which to run `describe` blocks in order to raise an
        // error if any asynchronous operations are attempted inside of a `describe` but outside of
        // a `beforeEach` or `it`.
        var syncZone = ambientZone.fork(new SyncTestZoneSpec('jasmine.describe'));
        var symbol = Zone.__symbol__;
        // whether patch jasmine clock when in fakeAsync
        var disablePatchingJasmineClock = global[symbol('fakeAsyncDisablePatchingClock')] === true;
        // the original variable name fakeAsyncPatchLock is not accurate, so the name will be
        // fakeAsyncAutoFakeAsyncWhenClockPatched and if this enablePatchingJasmineClock is false, we also
        // automatically disable the auto jump into fakeAsync feature
        var enableAutoFakeAsyncWhenClockPatched = !disablePatchingJasmineClock &&
            ((global[symbol('fakeAsyncPatchLock')] === true) ||
                (global[symbol('fakeAsyncAutoFakeAsyncWhenClockPatched')] === true));
        var ignoreUnhandledRejection = global[symbol('ignoreUnhandledRejection')] === true;
        if (!ignoreUnhandledRejection) {
            var globalErrors_1 = jasmine.GlobalErrors;
            if (globalErrors_1 && !jasmine[symbol('GlobalErrors')]) {
                jasmine[symbol('GlobalErrors')] = globalErrors_1;
                jasmine.GlobalErrors = function () {
                    var instance = new globalErrors_1();
                    var originalInstall = instance.install;
                    if (originalInstall && !instance[symbol('install')]) {
                        instance[symbol('install')] = originalInstall;
                        instance.install = function () {
                            var originalHandlers = process.listeners('unhandledRejection');
                            var r = originalInstall.apply(this, arguments);
                            process.removeAllListeners('unhandledRejection');
                            if (originalHandlers) {
                                originalHandlers.forEach(function (h) { return process.on('unhandledRejection', h); });
                            }
                            return r;
                        };
                    }
                    return instance;
                };
            }
        }
        // Monkey patch all of the jasmine DSL so that each function runs in appropriate zone.
        var jasmineEnv = jasmine.getEnv();
        ['describe', 'xdescribe', 'fdescribe'].forEach(function (methodName) {
            var originalJasmineFn = jasmineEnv[methodName];
            jasmineEnv[methodName] = function (description, specDefinitions) {
                return originalJasmineFn.call(this, description, wrapDescribeInZone(specDefinitions));
            };
        });
        ['it', 'xit', 'fit'].forEach(function (methodName) {
            var originalJasmineFn = jasmineEnv[methodName];
            jasmineEnv[symbol(methodName)] = originalJasmineFn;
            jasmineEnv[methodName] = function (description, specDefinitions, timeout) {
                arguments[1] = wrapTestInZone(specDefinitions);
                return originalJasmineFn.apply(this, arguments);
            };
        });
        ['beforeEach', 'afterEach', 'beforeAll', 'afterAll'].forEach(function (methodName) {
            var originalJasmineFn = jasmineEnv[methodName];
            jasmineEnv[symbol(methodName)] = originalJasmineFn;
            jasmineEnv[methodName] = function (specDefinitions, timeout) {
                arguments[0] = wrapTestInZone(specDefinitions);
                return originalJasmineFn.apply(this, arguments);
            };
        });
        if (!disablePatchingJasmineClock) {
            // need to patch jasmine.clock().mockDate and jasmine.clock().tick() so
            // they can work properly in FakeAsyncTest
            var originalClockFn_1 = (jasmine[symbol('clock')] = jasmine['clock']);
            jasmine['clock'] = function () {
                var clock = originalClockFn_1.apply(this, arguments);
                if (!clock[symbol('patched')]) {
                    clock[symbol('patched')] = symbol('patched');
                    var originalTick_1 = (clock[symbol('tick')] = clock.tick);
                    clock.tick = function () {
                        var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                        if (fakeAsyncZoneSpec) {
                            return fakeAsyncZoneSpec.tick.apply(fakeAsyncZoneSpec, arguments);
                        }
                        return originalTick_1.apply(this, arguments);
                    };
                    var originalMockDate_1 = (clock[symbol('mockDate')] = clock.mockDate);
                    clock.mockDate = function () {
                        var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                        if (fakeAsyncZoneSpec) {
                            var dateTime = arguments.length > 0 ? arguments[0] : new Date();
                            return fakeAsyncZoneSpec.setFakeBaseSystemTime.apply(fakeAsyncZoneSpec, dateTime && typeof dateTime.getTime === 'function' ? [dateTime.getTime()] :
                                arguments);
                        }
                        return originalMockDate_1.apply(this, arguments);
                    };
                    // for auto go into fakeAsync feature, we need the flag to enable it
                    if (enableAutoFakeAsyncWhenClockPatched) {
                        ['install', 'uninstall'].forEach(function (methodName) {
                            var originalClockFn = (clock[symbol(methodName)] = clock[methodName]);
                            clock[methodName] = function () {
                                var FakeAsyncTestZoneSpec = Zone['FakeAsyncTestZoneSpec'];
                                if (FakeAsyncTestZoneSpec) {
                                    jasmine[symbol('clockInstalled')] = 'install' === methodName;
                                    return;
                                }
                                return originalClockFn.apply(this, arguments);
                            };
                        });
                    }
                }
                return clock;
            };
        }
        // monkey patch createSpyObj to make properties enumerable to true
        if (!jasmine[Zone.__symbol__('createSpyObj')]) {
            var originalCreateSpyObj_1 = jasmine.createSpyObj;
            jasmine[Zone.__symbol__('createSpyObj')] = originalCreateSpyObj_1;
            jasmine.createSpyObj = function () {
                var args = Array.prototype.slice.call(arguments);
                var propertyNames = args.length >= 3 ? args[2] : null;
                var spyObj;
                if (propertyNames) {
                    var defineProperty_1 = Object.defineProperty;
                    Object.defineProperty = function (obj, p, attributes) {
                        return defineProperty_1.call(this, obj, p, Object.assign(Object.assign({}, attributes), { configurable: true, enumerable: true }));
                    };
                    try {
                        spyObj = originalCreateSpyObj_1.apply(this, args);
                    }
                    finally {
                        Object.defineProperty = defineProperty_1;
                    }
                }
                else {
                    spyObj = originalCreateSpyObj_1.apply(this, args);
                }
                return spyObj;
            };
        }
        /**
         * Gets a function wrapping the body of a Jasmine `describe` block to execute in a
         * synchronous-only zone.
         */
        function wrapDescribeInZone(describeBody) {
            return function () {
                return syncZone.run(describeBody, this, arguments);
            };
        }
        function runInTestZone(testBody, applyThis, queueRunner, done) {
            var isClockInstalled = !!jasmine[symbol('clockInstalled')];
            var testProxyZoneSpec = queueRunner.testProxyZoneSpec;
            var testProxyZone = queueRunner.testProxyZone;
            if (isClockInstalled && enableAutoFakeAsyncWhenClockPatched) {
                // auto run a fakeAsync
                var fakeAsyncModule = Zone[Zone.__symbol__('fakeAsyncTest')];
                if (fakeAsyncModule && typeof fakeAsyncModule.fakeAsync === 'function') {
                    testBody = fakeAsyncModule.fakeAsync(testBody);
                }
            }
            if (done) {
                return testProxyZone.run(testBody, applyThis, [done]);
            }
            else {
                return testProxyZone.run(testBody, applyThis);
            }
        }
        /**
         * Gets a function wrapping the body of a Jasmine `it/beforeEach/afterEach` block to
         * execute in a ProxyZone zone.
         * This will run in `testProxyZone`. The `testProxyZone` will be reset by the `ZoneQueueRunner`
         */
        function wrapTestInZone(testBody) {
            // The `done` callback is only passed through if the function expects at least one argument.
            // Note we have to make a function with correct number of arguments, otherwise jasmine will
            // think that all functions are sync or async.
            return (testBody && (testBody.length ? function (done) {
                return runInTestZone(testBody, this, this.queueRunner, done);
            } : function () {
                return runInTestZone(testBody, this, this.queueRunner);
            }));
        }
        var QueueRunner = jasmine.QueueRunner;
        jasmine.QueueRunner = (function (_super) {
            __extends(ZoneQueueRunner, _super);
            function ZoneQueueRunner(attrs) {
                var _this = this;
                if (attrs.onComplete) {
                    attrs.onComplete = (function (fn) { return function () {
                        // All functions are done, clear the test zone.
                        _this.testProxyZone = null;
                        _this.testProxyZoneSpec = null;
                        ambientZone.scheduleMicroTask('jasmine.onComplete', fn);
                    }; })(attrs.onComplete);
                }
                var nativeSetTimeout = global[Zone.__symbol__('setTimeout')];
                var nativeClearTimeout = global[Zone.__symbol__('clearTimeout')];
                if (nativeSetTimeout) {
                    // should run setTimeout inside jasmine outside of zone
                    attrs.timeout = {
                        setTimeout: nativeSetTimeout ? nativeSetTimeout : global.setTimeout,
                        clearTimeout: nativeClearTimeout ? nativeClearTimeout : global.clearTimeout
                    };
                }
                // create a userContext to hold the queueRunner itself
                // so we can access the testProxy in it/xit/beforeEach ...
                if (jasmine.UserContext) {
                    if (!attrs.userContext) {
                        attrs.userContext = new jasmine.UserContext();
                    }
                    attrs.userContext.queueRunner = this;
                }
                else {
                    if (!attrs.userContext) {
                        attrs.userContext = {};
                    }
                    attrs.userContext.queueRunner = this;
                }
                // patch attrs.onException
                var onException = attrs.onException;
                attrs.onException = function (error) {
                    if (error &&
                        error.message ===
                            'Timeout - Async callback was not invoked within timeout specified by jasmine.DEFAULT_TIMEOUT_INTERVAL.') {
                        // jasmine timeout, we can make the error message more
                        // reasonable to tell what tasks are pending
                        var proxyZoneSpec = this && this.testProxyZoneSpec;
                        if (proxyZoneSpec) {
                            var pendingTasksInfo = proxyZoneSpec.getAndClearPendingTasksInfo();
                            try {
                                // try catch here in case error.message is not writable
                                error.message += pendingTasksInfo;
                            }
                            catch (err) {
                            }
                        }
                    }
                    if (onException) {
                        onException.call(this, error);
                    }
                };
                _super.call(this, attrs);
            }
            ZoneQueueRunner.prototype.execute = function () {
                var _this = this;
                var zone = Zone.current;
                var isChildOfAmbientZone = false;
                while (zone) {
                    if (zone === ambientZone) {
                        isChildOfAmbientZone = true;
                        break;
                    }
                    zone = zone.parent;
                }
                if (!isChildOfAmbientZone)
                    throw new Error('Unexpected Zone: ' + Zone.current.name);
                // This is the zone which will be used for running individual tests.
                // It will be a proxy zone, so that the tests function can retroactively install
                // different zones.
                // Example:
                //   - In beforeEach() do childZone = Zone.current.fork(...);
                //   - In it() try to do fakeAsync(). The issue is that because the beforeEach forked the
                //     zone outside of fakeAsync it will be able to escape the fakeAsync rules.
                //   - Because ProxyZone is parent fo `childZone` fakeAsync can retroactively add
                //     fakeAsync behavior to the childZone.
                this.testProxyZoneSpec = new ProxyZoneSpec();
                this.testProxyZone = ambientZone.fork(this.testProxyZoneSpec);
                if (!Zone.currentTask) {
                    // if we are not running in a task then if someone would register a
                    // element.addEventListener and then calling element.click() the
                    // addEventListener callback would think that it is the top most task and would
                    // drain the microtask queue on element.click() which would be incorrect.
                    // For this reason we always force a task when running jasmine tests.
                    Zone.current.scheduleMicroTask('jasmine.execute().forceTask', function () { return QueueRunner.prototype.execute.call(_this); });
                }
                else {
                    _super.prototype.execute.call(this);
                }
            };
            return ZoneQueueRunner;
        })(QueueRunner);
    });
})));
