'use strict';
var __spreadArrays = (this && this.__spreadArrays) || function () {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++)
        for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
            r[k] = a[j];
    return r;
};
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
    /**
     * @fileoverview
     * @suppress {globalThis}
     */
    var NEWLINE = '\n';
    var IGNORE_FRAMES = {};
    var creationTrace = '__creationTrace__';
    var ERROR_TAG = 'STACKTRACE TRACKING';
    var SEP_TAG = '__SEP_TAG__';
    var sepTemplate = SEP_TAG + '@[native]';
    var LongStackTrace = /** @class */ (function () {
        function LongStackTrace() {
            this.error = getStacktrace();
            this.timestamp = new Date();
        }
        return LongStackTrace;
    }());
    function getStacktraceWithUncaughtError() {
        return new Error(ERROR_TAG);
    }
    function getStacktraceWithCaughtError() {
        try {
            throw getStacktraceWithUncaughtError();
        }
        catch (err) {
            return err;
        }
    }
    // Some implementations of exception handling don't create a stack trace if the exception
    // isn't thrown, however it's faster not to actually throw the exception.
    var error = getStacktraceWithUncaughtError();
    var caughtError = getStacktraceWithCaughtError();
    var getStacktrace = error.stack ?
        getStacktraceWithUncaughtError :
        (caughtError.stack ? getStacktraceWithCaughtError : getStacktraceWithUncaughtError);
    function getFrames(error) {
        return error.stack ? error.stack.split(NEWLINE) : [];
    }
    function addErrorStack(lines, error) {
        var trace = getFrames(error);
        for (var i = 0; i < trace.length; i++) {
            var frame = trace[i];
            // Filter out the Frames which are part of stack capturing.
            if (!IGNORE_FRAMES.hasOwnProperty(frame)) {
                lines.push(trace[i]);
            }
        }
    }
    function renderLongStackTrace(frames, stack) {
        var longTrace = [stack ? stack.trim() : ''];
        if (frames) {
            var timestamp = new Date().getTime();
            for (var i = 0; i < frames.length; i++) {
                var traceFrames = frames[i];
                var lastTime = traceFrames.timestamp;
                var separator = "____________________Elapsed " + (timestamp - lastTime.getTime()) + " ms; At: " + lastTime;
                separator = separator.replace(/[^\w\d]/g, '_');
                longTrace.push(sepTemplate.replace(SEP_TAG, separator));
                addErrorStack(longTrace, traceFrames.error);
                timestamp = lastTime.getTime();
            }
        }
        return longTrace.join(NEWLINE);
    }
    // if Error.stackTraceLimit is 0, means stack trace
    // is disabled, so we don't need to generate long stack trace
    // this will improve performance in some test(some test will
    // set stackTraceLimit to 0, https://github.com/angular/zone.js/issues/698
    function stackTracesEnabled() {
        // Cast through any since this property only exists on Error in the nodejs
        // typings.
        return Error.stackTraceLimit > 0;
    }
    Zone['longStackTraceZoneSpec'] = {
        name: 'long-stack-trace',
        longStackTraceLimit: 10,
        // add a getLongStackTrace method in spec to
        // handle handled reject promise error.
        getLongStackTrace: function (error) {
            if (!error) {
                return undefined;
            }
            var trace = error[Zone.__symbol__('currentTaskTrace')];
            if (!trace) {
                return error.stack;
            }
            return renderLongStackTrace(trace, error.stack);
        },
        onScheduleTask: function (parentZoneDelegate, currentZone, targetZone, task) {
            if (stackTracesEnabled()) {
                var currentTask = Zone.currentTask;
                var trace = currentTask && currentTask.data && currentTask.data[creationTrace] || [];
                trace = [new LongStackTrace()].concat(trace);
                if (trace.length > this.longStackTraceLimit) {
                    trace.length = this.longStackTraceLimit;
                }
                if (!task.data)
                    task.data = {};
                if (task.type === 'eventTask') {
                    // Fix issue https://github.com/angular/zone.js/issues/1195,
                    // For event task of browser, by default, all task will share a
                    // singleton instance of data object, we should create a new one here
                    // The cast to `any` is required to workaround a closure bug which wrongly applies
                    // URL sanitization rules to .data access.
                    task.data = Object.assign({}, task.data);
                }
                task.data[creationTrace] = trace;
            }
            return parentZoneDelegate.scheduleTask(targetZone, task);
        },
        onHandleError: function (parentZoneDelegate, currentZone, targetZone, error) {
            if (stackTracesEnabled()) {
                var parentTask = Zone.currentTask || error.task;
                if (error instanceof Error && parentTask) {
                    var longStack = renderLongStackTrace(parentTask.data && parentTask.data[creationTrace], error.stack);
                    try {
                        error.stack = error.longStack = longStack;
                    }
                    catch (err) {
                    }
                }
            }
            return parentZoneDelegate.handleError(targetZone, error);
        }
    };
    function captureStackTraces(stackTraces, count) {
        if (count > 0) {
            stackTraces.push(getFrames((new LongStackTrace()).error));
            captureStackTraces(stackTraces, count - 1);
        }
    }
    function computeIgnoreFrames() {
        if (!stackTracesEnabled()) {
            return;
        }
        var frames = [];
        captureStackTraces(frames, 2);
        var frames1 = frames[0];
        var frames2 = frames[1];
        for (var i = 0; i < frames1.length; i++) {
            var frame1 = frames1[i];
            if (frame1.indexOf(ERROR_TAG) == -1) {
                var match = frame1.match(/^\s*at\s+/);
                if (match) {
                    sepTemplate = match[0] + SEP_TAG + ' (http://localhost)';
                    break;
                }
            }
        }
        for (var i = 0; i < frames1.length; i++) {
            var frame1 = frames1[i];
            var frame2 = frames2[i];
            if (frame1 === frame2) {
                IGNORE_FRAMES[frame1] = true;
            }
            else {
                break;
            }
        }
    }
    computeIgnoreFrames();
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var ProxyZoneSpec = /** @class */ (function () {
        function ProxyZoneSpec(defaultSpecDelegate) {
            if (defaultSpecDelegate === void 0) { defaultSpecDelegate = null; }
            this.defaultSpecDelegate = defaultSpecDelegate;
            this.name = 'ProxyZone';
            this._delegateSpec = null;
            this.properties = { 'ProxyZoneSpec': this };
            this.propertyKeys = null;
            this.lastTaskState = null;
            this.isNeedToTriggerHasTask = false;
            this.tasks = [];
            this.setDelegate(defaultSpecDelegate);
        }
        ProxyZoneSpec.get = function () {
            return Zone.current.get('ProxyZoneSpec');
        };
        ProxyZoneSpec.isLoaded = function () {
            return ProxyZoneSpec.get() instanceof ProxyZoneSpec;
        };
        ProxyZoneSpec.assertPresent = function () {
            if (!ProxyZoneSpec.isLoaded()) {
                throw new Error("Expected to be running in 'ProxyZone', but it was not found.");
            }
            return ProxyZoneSpec.get();
        };
        ProxyZoneSpec.prototype.setDelegate = function (delegateSpec) {
            var _this = this;
            var isNewDelegate = this._delegateSpec !== delegateSpec;
            this._delegateSpec = delegateSpec;
            this.propertyKeys && this.propertyKeys.forEach(function (key) { return delete _this.properties[key]; });
            this.propertyKeys = null;
            if (delegateSpec && delegateSpec.properties) {
                this.propertyKeys = Object.keys(delegateSpec.properties);
                this.propertyKeys.forEach(function (k) { return _this.properties[k] = delegateSpec.properties[k]; });
            }
            // if a new delegateSpec was set, check if we need to trigger hasTask
            if (isNewDelegate && this.lastTaskState &&
                (this.lastTaskState.macroTask || this.lastTaskState.microTask)) {
                this.isNeedToTriggerHasTask = true;
            }
        };
        ProxyZoneSpec.prototype.getDelegate = function () {
            return this._delegateSpec;
        };
        ProxyZoneSpec.prototype.resetDelegate = function () {
            var delegateSpec = this.getDelegate();
            this.setDelegate(this.defaultSpecDelegate);
        };
        ProxyZoneSpec.prototype.tryTriggerHasTask = function (parentZoneDelegate, currentZone, targetZone) {
            if (this.isNeedToTriggerHasTask && this.lastTaskState) {
                // last delegateSpec has microTask or macroTask
                // should call onHasTask in current delegateSpec
                this.isNeedToTriggerHasTask = false;
                this.onHasTask(parentZoneDelegate, currentZone, targetZone, this.lastTaskState);
            }
        };
        ProxyZoneSpec.prototype.removeFromTasks = function (task) {
            if (!this.tasks) {
                return;
            }
            for (var i = 0; i < this.tasks.length; i++) {
                if (this.tasks[i] === task) {
                    this.tasks.splice(i, 1);
                    return;
                }
            }
        };
        ProxyZoneSpec.prototype.getAndClearPendingTasksInfo = function () {
            if (this.tasks.length === 0) {
                return '';
            }
            var taskInfo = this.tasks.map(function (task) {
                var dataInfo = task.data &&
                    Object.keys(task.data)
                        .map(function (key) {
                        return key + ':' + task.data[key];
                    })
                        .join(',');
                return "type: " + task.type + ", source: " + task.source + ", args: {" + dataInfo + "}";
            });
            var pendingTasksInfo = '--Pending async tasks are: [' + taskInfo + ']';
            // clear tasks
            this.tasks = [];
            return pendingTasksInfo;
        };
        ProxyZoneSpec.prototype.onFork = function (parentZoneDelegate, currentZone, targetZone, zoneSpec) {
            if (this._delegateSpec && this._delegateSpec.onFork) {
                return this._delegateSpec.onFork(parentZoneDelegate, currentZone, targetZone, zoneSpec);
            }
            else {
                return parentZoneDelegate.fork(targetZone, zoneSpec);
            }
        };
        ProxyZoneSpec.prototype.onIntercept = function (parentZoneDelegate, currentZone, targetZone, delegate, source) {
            if (this._delegateSpec && this._delegateSpec.onIntercept) {
                return this._delegateSpec.onIntercept(parentZoneDelegate, currentZone, targetZone, delegate, source);
            }
            else {
                return parentZoneDelegate.intercept(targetZone, delegate, source);
            }
        };
        ProxyZoneSpec.prototype.onInvoke = function (parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source) {
            this.tryTriggerHasTask(parentZoneDelegate, currentZone, targetZone);
            if (this._delegateSpec && this._delegateSpec.onInvoke) {
                return this._delegateSpec.onInvoke(parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source);
            }
            else {
                return parentZoneDelegate.invoke(targetZone, delegate, applyThis, applyArgs, source);
            }
        };
        ProxyZoneSpec.prototype.onHandleError = function (parentZoneDelegate, currentZone, targetZone, error) {
            if (this._delegateSpec && this._delegateSpec.onHandleError) {
                return this._delegateSpec.onHandleError(parentZoneDelegate, currentZone, targetZone, error);
            }
            else {
                return parentZoneDelegate.handleError(targetZone, error);
            }
        };
        ProxyZoneSpec.prototype.onScheduleTask = function (parentZoneDelegate, currentZone, targetZone, task) {
            if (task.type !== 'eventTask') {
                this.tasks.push(task);
            }
            if (this._delegateSpec && this._delegateSpec.onScheduleTask) {
                return this._delegateSpec.onScheduleTask(parentZoneDelegate, currentZone, targetZone, task);
            }
            else {
                return parentZoneDelegate.scheduleTask(targetZone, task);
            }
        };
        ProxyZoneSpec.prototype.onInvokeTask = function (parentZoneDelegate, currentZone, targetZone, task, applyThis, applyArgs) {
            if (task.type !== 'eventTask') {
                this.removeFromTasks(task);
            }
            this.tryTriggerHasTask(parentZoneDelegate, currentZone, targetZone);
            if (this._delegateSpec && this._delegateSpec.onInvokeTask) {
                return this._delegateSpec.onInvokeTask(parentZoneDelegate, currentZone, targetZone, task, applyThis, applyArgs);
            }
            else {
                return parentZoneDelegate.invokeTask(targetZone, task, applyThis, applyArgs);
            }
        };
        ProxyZoneSpec.prototype.onCancelTask = function (parentZoneDelegate, currentZone, targetZone, task) {
            if (task.type !== 'eventTask') {
                this.removeFromTasks(task);
            }
            this.tryTriggerHasTask(parentZoneDelegate, currentZone, targetZone);
            if (this._delegateSpec && this._delegateSpec.onCancelTask) {
                return this._delegateSpec.onCancelTask(parentZoneDelegate, currentZone, targetZone, task);
            }
            else {
                return parentZoneDelegate.cancelTask(targetZone, task);
            }
        };
        ProxyZoneSpec.prototype.onHasTask = function (delegate, current, target, hasTaskState) {
            this.lastTaskState = hasTaskState;
            if (this._delegateSpec && this._delegateSpec.onHasTask) {
                this._delegateSpec.onHasTask(delegate, current, target, hasTaskState);
            }
            else {
                delegate.hasTask(target, hasTaskState);
            }
        };
        return ProxyZoneSpec;
    }());
    // Export the class so that new instances can be created with proper
    // constructor params.
    Zone['ProxyZoneSpec'] = ProxyZoneSpec;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var SyncTestZoneSpec = /** @class */ (function () {
        function SyncTestZoneSpec(namePrefix) {
            this.runZone = Zone.current;
            this.name = 'syncTestZone for ' + namePrefix;
        }
        SyncTestZoneSpec.prototype.onScheduleTask = function (delegate, current, target, task) {
            switch (task.type) {
                case 'microTask':
                case 'macroTask':
                    throw new Error("Cannot call " + task.source + " from within a sync test.");
                case 'eventTask':
                    task = delegate.scheduleTask(target, task);
                    break;
            }
            return task;
        };
        return SyncTestZoneSpec;
    }());
    // Export the class so that new instances can be created with proper
    // constructor params.
    Zone['SyncTestZoneSpec'] = SyncTestZoneSpec;
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
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('jest', function (context, Zone, api) {
        if (typeof jest === 'undefined' || jest['__zone_patch__']) {
            return;
        }
        jest['__zone_patch__'] = true;
        var ProxyZoneSpec = Zone['ProxyZoneSpec'];
        var SyncTestZoneSpec = Zone['SyncTestZoneSpec'];
        if (!ProxyZoneSpec) {
            throw new Error('Missing ProxyZoneSpec');
        }
        var rootZone = Zone.current;
        var syncZone = rootZone.fork(new SyncTestZoneSpec('jest.describe'));
        var proxyZoneSpec = new ProxyZoneSpec();
        var proxyZone = rootZone.fork(proxyZoneSpec);
        function wrapDescribeFactoryInZone(originalJestFn) {
            return function () {
                var tableArgs = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    tableArgs[_i] = arguments[_i];
                }
                var originalDescribeFn = originalJestFn.apply(this, tableArgs);
                return function () {
                    var args = [];
                    for (var _i = 0; _i < arguments.length; _i++) {
                        args[_i] = arguments[_i];
                    }
                    args[1] = wrapDescribeInZone(args[1]);
                    return originalDescribeFn.apply(this, args);
                };
            };
        }
        function wrapTestFactoryInZone(originalJestFn) {
            return function () {
                var tableArgs = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    tableArgs[_i] = arguments[_i];
                }
                return function () {
                    var args = [];
                    for (var _i = 0; _i < arguments.length; _i++) {
                        args[_i] = arguments[_i];
                    }
                    args[1] = wrapTestInZone(args[1]);
                    return originalJestFn.apply(this, tableArgs).apply(this, args);
                };
            };
        }
        /**
         * Gets a function wrapping the body of a jest `describe` block to execute in a
         * synchronous-only zone.
         */
        function wrapDescribeInZone(describeBody) {
            return function () {
                var args = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    args[_i] = arguments[_i];
                }
                return syncZone.run(describeBody, this, args);
            };
        }
        /**
         * Gets a function wrapping the body of a jest `it/beforeEach/afterEach` block to
         * execute in a ProxyZone zone.
         * This will run in the `proxyZone`.
         */
        function wrapTestInZone(testBody, isTestFunc) {
            if (isTestFunc === void 0) { isTestFunc = false; }
            if (typeof testBody !== 'function') {
                return testBody;
            }
            var wrappedFunc = function () {
                if (Zone[api.symbol('useFakeTimersCalled')] === true && testBody &&
                    !testBody.isFakeAsync) {
                    // jest.useFakeTimers is called, run into fakeAsyncTest automatically.
                    var fakeAsyncModule = Zone[Zone.__symbol__('fakeAsyncTest')];
                    if (fakeAsyncModule && typeof fakeAsyncModule.fakeAsync === 'function') {
                        testBody = fakeAsyncModule.fakeAsync(testBody);
                    }
                }
                proxyZoneSpec.isTestFunc = isTestFunc;
                return proxyZone.run(testBody, null, arguments);
            };
            // Update the length of wrappedFunc to be the same as the length of the testBody
            // So jest core can handle whether the test function has `done()` or not correctly
            Object.defineProperty(wrappedFunc, 'length', { configurable: true, writable: true, enumerable: false });
            wrappedFunc.length = testBody.length;
            return wrappedFunc;
        }
        ['describe', 'xdescribe', 'fdescribe'].forEach(function (methodName) {
            var originalJestFn = context[methodName];
            if (context[Zone.__symbol__(methodName)]) {
                return;
            }
            context[Zone.__symbol__(methodName)] = originalJestFn;
            context[methodName] = function () {
                var args = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    args[_i] = arguments[_i];
                }
                args[1] = wrapDescribeInZone(args[1]);
                return originalJestFn.apply(this, args);
            };
            context[methodName].each = wrapDescribeFactoryInZone(originalJestFn.each);
        });
        context.describe.only = context.fdescribe;
        context.describe.skip = context.xdescribe;
        ['it', 'xit', 'fit', 'test', 'xtest'].forEach(function (methodName) {
            var originalJestFn = context[methodName];
            if (context[Zone.__symbol__(methodName)]) {
                return;
            }
            context[Zone.__symbol__(methodName)] = originalJestFn;
            context[methodName] = function () {
                var args = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    args[_i] = arguments[_i];
                }
                args[1] = wrapTestInZone(args[1], true);
                return originalJestFn.apply(this, args);
            };
            context[methodName].each = wrapTestFactoryInZone(originalJestFn.each);
            context[methodName].todo = originalJestFn.todo;
        });
        context.it.only = context.fit;
        context.it.skip = context.xit;
        context.test.only = context.fit;
        context.test.skip = context.xit;
        ['beforeEach', 'afterEach', 'beforeAll', 'afterAll'].forEach(function (methodName) {
            var originalJestFn = context[methodName];
            if (context[Zone.__symbol__(methodName)]) {
                return;
            }
            context[Zone.__symbol__(methodName)] = originalJestFn;
            context[methodName] = function () {
                var args = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    args[_i] = arguments[_i];
                }
                args[0] = wrapTestInZone(args[0]);
                return originalJestFn.apply(this, args);
            };
        });
        Zone.patchJestObject = function patchJestObject(Timer, isModern) {
            if (isModern === void 0) { isModern = false; }
            // check whether currently the test is inside fakeAsync()
            function isPatchingFakeTimer() {
                var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                return !!fakeAsyncZoneSpec;
            }
            // check whether the current function is inside `test/it` or other methods
            // such as `describe/beforeEach`
            function isInTestFunc() {
                var proxyZoneSpec = Zone.current.get('ProxyZoneSpec');
                return proxyZoneSpec && proxyZoneSpec.isTestFunc;
            }
            if (Timer[api.symbol('fakeTimers')]) {
                return;
            }
            Timer[api.symbol('fakeTimers')] = true;
            // patch jest fakeTimer internal method to make sure no console.warn print out
            api.patchMethod(Timer, '_checkFakeTimers', function (delegate) {
                return function (self, args) {
                    if (isPatchingFakeTimer()) {
                        return true;
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch useFakeTimers(), set useFakeTimersCalled flag, and make test auto run into fakeAsync
            api.patchMethod(Timer, 'useFakeTimers', function (delegate) {
                return function (self, args) {
                    Zone[api.symbol('useFakeTimersCalled')] = true;
                    if (isModern || isInTestFunc()) {
                        return delegate.apply(self, args);
                    }
                    return self;
                };
            });
            // patch useRealTimers(), unset useFakeTimers flag
            api.patchMethod(Timer, 'useRealTimers', function (delegate) {
                return function (self, args) {
                    Zone[api.symbol('useFakeTimersCalled')] = false;
                    if (isModern || isInTestFunc()) {
                        return delegate.apply(self, args);
                    }
                    return self;
                };
            });
            // patch setSystemTime(), call setCurrentRealTime() in the fakeAsyncTest
            api.patchMethod(Timer, 'setSystemTime', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec && isPatchingFakeTimer()) {
                        fakeAsyncZoneSpec.setFakeBaseSystemTime(args[0]);
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch getSystemTime(), call getCurrentRealTime() in the fakeAsyncTest
            api.patchMethod(Timer, 'getRealSystemTime', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec && isPatchingFakeTimer()) {
                        return fakeAsyncZoneSpec.getRealSystemTime();
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch runAllTicks(), run all microTasks inside fakeAsync
            api.patchMethod(Timer, 'runAllTicks', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.flushMicrotasks();
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch runAllTimers(), run all macroTasks inside fakeAsync
            api.patchMethod(Timer, 'runAllTimers', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.flush(100, true);
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch advanceTimersByTime(), call tick() in the fakeAsyncTest
            api.patchMethod(Timer, 'advanceTimersByTime', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.tick(args[0]);
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch runOnlyPendingTimers(), call flushOnlyPendingTimers() in the fakeAsyncTest
            api.patchMethod(Timer, 'runOnlyPendingTimers', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.flushOnlyPendingTimers();
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch advanceTimersToNextTimer(), call tickToNext() in the fakeAsyncTest
            api.patchMethod(Timer, 'advanceTimersToNextTimer', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.tickToNext(args[0]);
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch clearAllTimers(), call removeAllTimers() in the fakeAsyncTest
            api.patchMethod(Timer, 'clearAllTimers', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        fakeAsyncZoneSpec.removeAllTimers();
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
            // patch getTimerCount(), call getTimerCount() in the fakeAsyncTest
            api.patchMethod(Timer, 'getTimerCount', function (delegate) {
                return function (self, args) {
                    var fakeAsyncZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                    if (fakeAsyncZoneSpec) {
                        return fakeAsyncZoneSpec.getTimerCount();
                    }
                    else {
                        return delegate.apply(self, args);
                    }
                };
            });
        };
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('mocha', function (global, Zone) {
        var Mocha = global.Mocha;
        if (typeof Mocha === 'undefined') {
            // return if Mocha is not available, because now zone-testing
            // will load mocha patch with jasmine/jest patch
            return;
        }
        if (typeof Zone === 'undefined') {
            throw new Error('Missing Zone.js');
        }
        var ProxyZoneSpec = Zone['ProxyZoneSpec'];
        var SyncTestZoneSpec = Zone['SyncTestZoneSpec'];
        if (!ProxyZoneSpec) {
            throw new Error('Missing ProxyZoneSpec');
        }
        if (Mocha['__zone_patch__']) {
            throw new Error('"Mocha" has already been patched with "Zone".');
        }
        Mocha['__zone_patch__'] = true;
        var rootZone = Zone.current;
        var syncZone = rootZone.fork(new SyncTestZoneSpec('Mocha.describe'));
        var testZone = null;
        var suiteZone = rootZone.fork(new ProxyZoneSpec());
        var mochaOriginal = {
            after: Mocha.after,
            afterEach: Mocha.afterEach,
            before: Mocha.before,
            beforeEach: Mocha.beforeEach,
            describe: Mocha.describe,
            it: Mocha.it
        };
        function modifyArguments(args, syncTest, asyncTest) {
            var _loop_1 = function (i) {
                var arg = args[i];
                if (typeof arg === 'function') {
                    // The `done` callback is only passed through if the function expects at
                    // least one argument.
                    // Note we have to make a function with correct number of arguments,
                    // otherwise mocha will
                    // think that all functions are sync or async.
                    args[i] = (arg.length === 0) ? syncTest(arg) : asyncTest(arg);
                    // Mocha uses toString to view the test body in the result list, make sure we return the
                    // correct function body
                    args[i].toString = function () {
                        return arg.toString();
                    };
                }
            };
            for (var i = 0; i < args.length; i++) {
                _loop_1(i);
            }
            return args;
        }
        function wrapDescribeInZone(args) {
            var syncTest = function (fn) {
                return function () {
                    return syncZone.run(fn, this, arguments);
                };
            };
            return modifyArguments(args, syncTest);
        }
        function wrapTestInZone(args) {
            var asyncTest = function (fn) {
                return function (done) {
                    return testZone.run(fn, this, [done]);
                };
            };
            var syncTest = function (fn) {
                return function () {
                    return testZone.run(fn, this);
                };
            };
            return modifyArguments(args, syncTest, asyncTest);
        }
        function wrapSuiteInZone(args) {
            var asyncTest = function (fn) {
                return function (done) {
                    return suiteZone.run(fn, this, [done]);
                };
            };
            var syncTest = function (fn) {
                return function () {
                    return suiteZone.run(fn, this);
                };
            };
            return modifyArguments(args, syncTest, asyncTest);
        }
        global.describe = global.suite = Mocha.describe = function () {
            return mochaOriginal.describe.apply(this, wrapDescribeInZone(arguments));
        };
        global.xdescribe = global.suite.skip = Mocha.describe.skip = function () {
            return mochaOriginal.describe.skip.apply(this, wrapDescribeInZone(arguments));
        };
        global.describe.only = global.suite.only = Mocha.describe.only = function () {
            return mochaOriginal.describe.only.apply(this, wrapDescribeInZone(arguments));
        };
        global.it = global.specify = global.test = Mocha.it = function () {
            return mochaOriginal.it.apply(this, wrapTestInZone(arguments));
        };
        global.xit = global.xspecify = Mocha.it.skip = function () {
            return mochaOriginal.it.skip.apply(this, wrapTestInZone(arguments));
        };
        global.it.only = global.test.only = Mocha.it.only = function () {
            return mochaOriginal.it.only.apply(this, wrapTestInZone(arguments));
        };
        global.after = global.suiteTeardown = Mocha.after = function () {
            return mochaOriginal.after.apply(this, wrapSuiteInZone(arguments));
        };
        global.afterEach = global.teardown = Mocha.afterEach = function () {
            return mochaOriginal.afterEach.apply(this, wrapTestInZone(arguments));
        };
        global.before = global.suiteSetup = Mocha.before = function () {
            return mochaOriginal.before.apply(this, wrapSuiteInZone(arguments));
        };
        global.beforeEach = global.setup = Mocha.beforeEach = function () {
            return mochaOriginal.beforeEach.apply(this, wrapTestInZone(arguments));
        };
        (function (originalRunTest, originalRun) {
            Mocha.Runner.prototype.runTest = function (fn) {
                var _this = this;
                Zone.current.scheduleMicroTask('mocha.forceTask', function () {
                    originalRunTest.call(_this, fn);
                });
            };
            Mocha.Runner.prototype.run = function (fn) {
                this.on('test', function (e) {
                    testZone = rootZone.fork(new ProxyZoneSpec());
                });
                this.on('fail', function (test, err) {
                    var proxyZoneSpec = testZone && testZone.get('ProxyZoneSpec');
                    if (proxyZoneSpec && err) {
                        try {
                            // try catch here in case err.message is not writable
                            err.message += proxyZoneSpec.getAndClearPendingTasksInfo();
                        }
                        catch (error) {
                        }
                    }
                });
                return originalRun.call(this, fn);
            };
        })(Mocha.Runner.prototype.runTest, Mocha.Runner.prototype.run);
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    (function (_global) {
        var AsyncTestZoneSpec = /** @class */ (function () {
            function AsyncTestZoneSpec(finishCallback, failCallback, namePrefix) {
                this.finishCallback = finishCallback;
                this.failCallback = failCallback;
                this._pendingMicroTasks = false;
                this._pendingMacroTasks = false;
                this._alreadyErrored = false;
                this._isSync = false;
                this.runZone = Zone.current;
                this.unresolvedChainedPromiseCount = 0;
                this.supportWaitUnresolvedChainedPromise = false;
                this.name = 'asyncTestZone for ' + namePrefix;
                this.properties = { 'AsyncTestZoneSpec': this };
                this.supportWaitUnresolvedChainedPromise =
                    _global[Zone.__symbol__('supportWaitUnResolvedChainedPromise')] === true;
            }
            AsyncTestZoneSpec.prototype.isUnresolvedChainedPromisePending = function () {
                return this.unresolvedChainedPromiseCount > 0;
            };
            AsyncTestZoneSpec.prototype._finishCallbackIfDone = function () {
                var _this = this;
                if (!(this._pendingMicroTasks || this._pendingMacroTasks ||
                    (this.supportWaitUnresolvedChainedPromise && this.isUnresolvedChainedPromisePending()))) {
                    // We do this because we would like to catch unhandled rejected promises.
                    this.runZone.run(function () {
                        setTimeout(function () {
                            if (!_this._alreadyErrored && !(_this._pendingMicroTasks || _this._pendingMacroTasks)) {
                                _this.finishCallback();
                            }
                        }, 0);
                    });
                }
            };
            AsyncTestZoneSpec.prototype.patchPromiseForTest = function () {
                if (!this.supportWaitUnresolvedChainedPromise) {
                    return;
                }
                var patchPromiseForTest = Promise[Zone.__symbol__('patchPromiseForTest')];
                if (patchPromiseForTest) {
                    patchPromiseForTest();
                }
            };
            AsyncTestZoneSpec.prototype.unPatchPromiseForTest = function () {
                if (!this.supportWaitUnresolvedChainedPromise) {
                    return;
                }
                var unPatchPromiseForTest = Promise[Zone.__symbol__('unPatchPromiseForTest')];
                if (unPatchPromiseForTest) {
                    unPatchPromiseForTest();
                }
            };
            AsyncTestZoneSpec.prototype.onScheduleTask = function (delegate, current, target, task) {
                if (task.type !== 'eventTask') {
                    this._isSync = false;
                }
                if (task.type === 'microTask' && task.data && task.data instanceof Promise) {
                    // check whether the promise is a chained promise
                    if (task.data[AsyncTestZoneSpec.symbolParentUnresolved] === true) {
                        // chained promise is being scheduled
                        this.unresolvedChainedPromiseCount--;
                    }
                }
                return delegate.scheduleTask(target, task);
            };
            AsyncTestZoneSpec.prototype.onInvokeTask = function (delegate, current, target, task, applyThis, applyArgs) {
                if (task.type !== 'eventTask') {
                    this._isSync = false;
                }
                return delegate.invokeTask(target, task, applyThis, applyArgs);
            };
            AsyncTestZoneSpec.prototype.onCancelTask = function (delegate, current, target, task) {
                if (task.type !== 'eventTask') {
                    this._isSync = false;
                }
                return delegate.cancelTask(target, task);
            };
            // Note - we need to use onInvoke at the moment to call finish when a test is
            // fully synchronous. TODO(juliemr): remove this when the logic for
            // onHasTask changes and it calls whenever the task queues are dirty.
            // updated by(JiaLiPassion), only call finish callback when no task
            // was scheduled/invoked/canceled.
            AsyncTestZoneSpec.prototype.onInvoke = function (parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source) {
                try {
                    this._isSync = true;
                    return parentZoneDelegate.invoke(targetZone, delegate, applyThis, applyArgs, source);
                }
                finally {
                    var afterTaskCounts = parentZoneDelegate._taskCounts;
                    if (this._isSync) {
                        this._finishCallbackIfDone();
                    }
                }
            };
            AsyncTestZoneSpec.prototype.onHandleError = function (parentZoneDelegate, currentZone, targetZone, error) {
                // Let the parent try to handle the error.
                var result = parentZoneDelegate.handleError(targetZone, error);
                if (result) {
                    this.failCallback(error);
                    this._alreadyErrored = true;
                }
                return false;
            };
            AsyncTestZoneSpec.prototype.onHasTask = function (delegate, current, target, hasTaskState) {
                delegate.hasTask(target, hasTaskState);
                if (hasTaskState.change == 'microTask') {
                    this._pendingMicroTasks = hasTaskState.microTask;
                    this._finishCallbackIfDone();
                }
                else if (hasTaskState.change == 'macroTask') {
                    this._pendingMacroTasks = hasTaskState.macroTask;
                    this._finishCallbackIfDone();
                }
            };
            return AsyncTestZoneSpec;
        }());
        AsyncTestZoneSpec.symbolParentUnresolved = Zone.__symbol__('parentUnresolved');
        // Export the class so that new instances can be created with proper
        // constructor params.
        Zone['AsyncTestZoneSpec'] = AsyncTestZoneSpec;
    })(typeof window !== 'undefined' && window || typeof self !== 'undefined' && self || global);
    Zone.__load_patch('asynctest', function (global, Zone, api) {
        /**
         * Wraps a test function in an asynchronous test zone. The test will automatically
         * complete when all asynchronous calls within this zone are done.
         */
        Zone[api.symbol('asyncTest')] = function asyncTest(fn) {
            // If we're running using the Jasmine test framework, adapt to call the 'done'
            // function when asynchronous activity is finished.
            if (global.jasmine) {
                // Not using an arrow function to preserve context passed from call site
                return function (done) {
                    if (!done) {
                        // if we run beforeEach in @angular/core/testing/testing_internal then we get no done
                        // fake it here and assume sync.
                        done = function () { };
                        done.fail = function (e) {
                            throw e;
                        };
                    }
                    runInTestZone(fn, this, done, function (err) {
                        if (typeof err === 'string') {
                            return done.fail(new Error(err));
                        }
                        else {
                            done.fail(err);
                        }
                    });
                };
            }
            // Otherwise, return a promise which will resolve when asynchronous activity
            // is finished. This will be correctly consumed by the Mocha framework with
            // it('...', async(myFn)); or can be used in a custom framework.
            // Not using an arrow function to preserve context passed from call site
            return function () {
                var _this = this;
                return new Promise(function (finishCallback, failCallback) {
                    runInTestZone(fn, _this, finishCallback, failCallback);
                });
            };
        };
        function runInTestZone(fn, context, finishCallback, failCallback) {
            var currentZone = Zone.current;
            var AsyncTestZoneSpec = Zone['AsyncTestZoneSpec'];
            if (AsyncTestZoneSpec === undefined) {
                throw new Error('AsyncTestZoneSpec is needed for the async() test helper but could not be found. ' +
                    'Please make sure that your environment includes zone.js/dist/async-test.js');
            }
            var ProxyZoneSpec = Zone['ProxyZoneSpec'];
            if (!ProxyZoneSpec) {
                throw new Error('ProxyZoneSpec is needed for the async() test helper but could not be found. ' +
                    'Please make sure that your environment includes zone.js/dist/proxy.js');
            }
            var proxyZoneSpec = ProxyZoneSpec.get();
            ProxyZoneSpec.assertPresent();
            // We need to create the AsyncTestZoneSpec outside the ProxyZone.
            // If we do it in ProxyZone then we will get to infinite recursion.
            var proxyZone = Zone.current.getZoneWith('ProxyZoneSpec');
            var previousDelegate = proxyZoneSpec.getDelegate();
            proxyZone.parent.run(function () {
                var testZoneSpec = new AsyncTestZoneSpec(function () {
                    // Need to restore the original zone.
                    if (proxyZoneSpec.getDelegate() == testZoneSpec) {
                        // Only reset the zone spec if it's
                        // sill this one. Otherwise, assume
                        // it's OK.
                        proxyZoneSpec.setDelegate(previousDelegate);
                    }
                    testZoneSpec.unPatchPromiseForTest();
                    currentZone.run(function () {
                        finishCallback();
                    });
                }, function (error) {
                    // Need to restore the original zone.
                    if (proxyZoneSpec.getDelegate() == testZoneSpec) {
                        // Only reset the zone spec if it's sill this one. Otherwise, assume it's OK.
                        proxyZoneSpec.setDelegate(previousDelegate);
                    }
                    testZoneSpec.unPatchPromiseForTest();
                    currentZone.run(function () {
                        failCallback(error);
                    });
                }, 'test');
                proxyZoneSpec.setDelegate(testZoneSpec);
                testZoneSpec.patchPromiseForTest();
            });
            return Zone.current.runGuarded(fn, context);
        }
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    (function (global) {
        var OriginalDate = global.Date;
        // Since when we compile this file to `es2015`, and if we define
        // this `FakeDate` as `class FakeDate`, and then set `FakeDate.prototype`
        // there will be an error which is `Cannot assign to read only property 'prototype'`
        // so we need to use function implementation here.
        function FakeDate() {
            if (arguments.length === 0) {
                var d = new OriginalDate();
                d.setTime(FakeDate.now());
                return d;
            }
            else {
                var args = Array.prototype.slice.call(arguments);
                return new (OriginalDate.bind.apply(OriginalDate, __spreadArrays([void 0], args)))();
            }
        }
        FakeDate.now = function () {
            var fakeAsyncTestZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
            if (fakeAsyncTestZoneSpec) {
                return fakeAsyncTestZoneSpec.getFakeSystemTime();
            }
            return OriginalDate.now.apply(this, arguments);
        };
        FakeDate.UTC = OriginalDate.UTC;
        FakeDate.parse = OriginalDate.parse;
        // keep a reference for zone patched timer function
        var timers = {
            setTimeout: global.setTimeout,
            setInterval: global.setInterval,
            clearTimeout: global.clearTimeout,
            clearInterval: global.clearInterval
        };
        var Scheduler = /** @class */ (function () {
            function Scheduler() {
                // Scheduler queue with the tuple of end time and callback function - sorted by end time.
                this._schedulerQueue = [];
                // Current simulated time in millis.
                this._currentTickTime = 0;
                // Current fake system base time in millis.
                this._currentFakeBaseSystemTime = OriginalDate.now();
                // track requeuePeriodicTimer
                this._currentTickRequeuePeriodicEntries = [];
            }
            Scheduler.prototype.getCurrentTickTime = function () {
                return this._currentTickTime;
            };
            Scheduler.prototype.getFakeSystemTime = function () {
                return this._currentFakeBaseSystemTime + this._currentTickTime;
            };
            Scheduler.prototype.setFakeBaseSystemTime = function (fakeBaseSystemTime) {
                this._currentFakeBaseSystemTime = fakeBaseSystemTime;
            };
            Scheduler.prototype.getRealSystemTime = function () {
                return OriginalDate.now();
            };
            Scheduler.prototype.scheduleFunction = function (cb, delay, options) {
                options = Object.assign({
                    args: [],
                    isPeriodic: false,
                    isRequestAnimationFrame: false,
                    id: -1,
                    isRequeuePeriodic: false
                }, options);
                var currentId = options.id < 0 ? Scheduler.nextId++ : options.id;
                var endTime = this._currentTickTime + delay;
                // Insert so that scheduler queue remains sorted by end time.
                var newEntry = {
                    endTime: endTime,
                    id: currentId,
                    func: cb,
                    args: options.args,
                    delay: delay,
                    isPeriodic: options.isPeriodic,
                    isRequestAnimationFrame: options.isRequestAnimationFrame
                };
                if (options.isRequeuePeriodic) {
                    this._currentTickRequeuePeriodicEntries.push(newEntry);
                }
                var i = 0;
                for (; i < this._schedulerQueue.length; i++) {
                    var currentEntry = this._schedulerQueue[i];
                    if (newEntry.endTime < currentEntry.endTime) {
                        break;
                    }
                }
                this._schedulerQueue.splice(i, 0, newEntry);
                return currentId;
            };
            Scheduler.prototype.removeScheduledFunctionWithId = function (id) {
                for (var i = 0; i < this._schedulerQueue.length; i++) {
                    if (this._schedulerQueue[i].id == id) {
                        this._schedulerQueue.splice(i, 1);
                        break;
                    }
                }
            };
            Scheduler.prototype.removeAll = function () {
                this._schedulerQueue = [];
            };
            Scheduler.prototype.getTimerCount = function () {
                return this._schedulerQueue.length;
            };
            Scheduler.prototype.tickToNext = function (step, doTick, tickOptions) {
                if (step === void 0) { step = 1; }
                if (this._schedulerQueue.length < step) {
                    return;
                }
                // Find the last task currently queued in the scheduler queue and tick
                // till that time.
                var startTime = this._currentTickTime;
                var targetTask = this._schedulerQueue[step - 1];
                this.tick(targetTask.endTime - startTime, doTick, tickOptions);
            };
            Scheduler.prototype.tick = function (millis, doTick, tickOptions) {
                if (millis === void 0) { millis = 0; }
                var finalTime = this._currentTickTime + millis;
                var lastCurrentTime = 0;
                tickOptions = Object.assign({ processNewMacroTasksSynchronously: true }, tickOptions);
                // we need to copy the schedulerQueue so nested timeout
                // will not be wrongly called in the current tick
                // https://github.com/angular/angular/issues/33799
                var schedulerQueue = tickOptions.processNewMacroTasksSynchronously ?
                    this._schedulerQueue :
                    this._schedulerQueue.slice();
                if (schedulerQueue.length === 0 && doTick) {
                    doTick(millis);
                    return;
                }
                while (schedulerQueue.length > 0) {
                    // clear requeueEntries before each loop
                    this._currentTickRequeuePeriodicEntries = [];
                    var current = schedulerQueue[0];
                    if (finalTime < current.endTime) {
                        // Done processing the queue since it's sorted by endTime.
                        break;
                    }
                    else {
                        // Time to run scheduled function. Remove it from the head of queue.
                        var current_1 = schedulerQueue.shift();
                        if (!tickOptions.processNewMacroTasksSynchronously) {
                            var idx = this._schedulerQueue.indexOf(current_1);
                            if (idx >= 0) {
                                this._schedulerQueue.splice(idx, 1);
                            }
                        }
                        lastCurrentTime = this._currentTickTime;
                        this._currentTickTime = current_1.endTime;
                        if (doTick) {
                            doTick(this._currentTickTime - lastCurrentTime);
                        }
                        var retval = current_1.func.apply(global, current_1.isRequestAnimationFrame ? [this._currentTickTime] : current_1.args);
                        if (!retval) {
                            // Uncaught exception in the current scheduled function. Stop processing the queue.
                            break;
                        }
                        // check is there any requeue periodic entry is added in
                        // current loop, if there is, we need to add to current loop
                        if (!tickOptions.processNewMacroTasksSynchronously) {
                            this._currentTickRequeuePeriodicEntries.forEach(function (newEntry) {
                                var i = 0;
                                for (; i < schedulerQueue.length; i++) {
                                    var currentEntry = schedulerQueue[i];
                                    if (newEntry.endTime < currentEntry.endTime) {
                                        break;
                                    }
                                }
                                schedulerQueue.splice(i, 0, newEntry);
                            });
                        }
                    }
                }
                lastCurrentTime = this._currentTickTime;
                this._currentTickTime = finalTime;
                if (doTick) {
                    doTick(this._currentTickTime - lastCurrentTime);
                }
            };
            Scheduler.prototype.flushOnlyPendingTimers = function (doTick) {
                if (this._schedulerQueue.length === 0) {
                    return 0;
                }
                // Find the last task currently queued in the scheduler queue and tick
                // till that time.
                var startTime = this._currentTickTime;
                var lastTask = this._schedulerQueue[this._schedulerQueue.length - 1];
                this.tick(lastTask.endTime - startTime, doTick, { processNewMacroTasksSynchronously: false });
                return this._currentTickTime - startTime;
            };
            Scheduler.prototype.flush = function (limit, flushPeriodic, doTick) {
                if (limit === void 0) { limit = 20; }
                if (flushPeriodic === void 0) { flushPeriodic = false; }
                if (flushPeriodic) {
                    return this.flushPeriodic(doTick);
                }
                else {
                    return this.flushNonPeriodic(limit, doTick);
                }
            };
            Scheduler.prototype.flushPeriodic = function (doTick) {
                if (this._schedulerQueue.length === 0) {
                    return 0;
                }
                // Find the last task currently queued in the scheduler queue and tick
                // till that time.
                var startTime = this._currentTickTime;
                var lastTask = this._schedulerQueue[this._schedulerQueue.length - 1];
                this.tick(lastTask.endTime - startTime, doTick);
                return this._currentTickTime - startTime;
            };
            Scheduler.prototype.flushNonPeriodic = function (limit, doTick) {
                var startTime = this._currentTickTime;
                var lastCurrentTime = 0;
                var count = 0;
                while (this._schedulerQueue.length > 0) {
                    count++;
                    if (count > limit) {
                        throw new Error('flush failed after reaching the limit of ' + limit +
                            ' tasks. Does your code use a polling timeout?');
                    }
                    // flush only non-periodic timers.
                    // If the only remaining tasks are periodic(or requestAnimationFrame), finish flushing.
                    if (this._schedulerQueue.filter(function (task) { return !task.isPeriodic && !task.isRequestAnimationFrame; })
                        .length === 0) {
                        break;
                    }
                    var current = this._schedulerQueue.shift();
                    lastCurrentTime = this._currentTickTime;
                    this._currentTickTime = current.endTime;
                    if (doTick) {
                        // Update any secondary schedulers like Jasmine mock Date.
                        doTick(this._currentTickTime - lastCurrentTime);
                    }
                    var retval = current.func.apply(global, current.args);
                    if (!retval) {
                        // Uncaught exception in the current scheduled function. Stop processing the queue.
                        break;
                    }
                }
                return this._currentTickTime - startTime;
            };
            return Scheduler;
        }());
        // Next scheduler id.
        Scheduler.nextId = 1;
        var FakeAsyncTestZoneSpec = /** @class */ (function () {
            function FakeAsyncTestZoneSpec(namePrefix, trackPendingRequestAnimationFrame, macroTaskOptions) {
                if (trackPendingRequestAnimationFrame === void 0) { trackPendingRequestAnimationFrame = false; }
                this.trackPendingRequestAnimationFrame = trackPendingRequestAnimationFrame;
                this.macroTaskOptions = macroTaskOptions;
                this._scheduler = new Scheduler();
                this._microtasks = [];
                this._lastError = null;
                this._uncaughtPromiseErrors = Promise[Zone.__symbol__('uncaughtPromiseErrors')];
                this.pendingPeriodicTimers = [];
                this.pendingTimers = [];
                this.patchDateLocked = false;
                this.properties = { 'FakeAsyncTestZoneSpec': this };
                this.name = 'fakeAsyncTestZone for ' + namePrefix;
                // in case user can't access the construction of FakeAsyncTestSpec
                // user can also define macroTaskOptions by define a global variable.
                if (!this.macroTaskOptions) {
                    this.macroTaskOptions = global[Zone.__symbol__('FakeAsyncTestMacroTask')];
                }
            }
            FakeAsyncTestZoneSpec.assertInZone = function () {
                if (Zone.current.get('FakeAsyncTestZoneSpec') == null) {
                    throw new Error('The code should be running in the fakeAsync zone to call this function');
                }
            };
            FakeAsyncTestZoneSpec.prototype._fnAndFlush = function (fn, completers) {
                var _this = this;
                return function () {
                    var args = [];
                    for (var _i = 0; _i < arguments.length; _i++) {
                        args[_i] = arguments[_i];
                    }
                    fn.apply(global, args);
                    if (_this._lastError === null) { // Success
                        if (completers.onSuccess != null) {
                            completers.onSuccess.apply(global);
                        }
                        // Flush microtasks only on success.
                        _this.flushMicrotasks();
                    }
                    else { // Failure
                        if (completers.onError != null) {
                            completers.onError.apply(global);
                        }
                    }
                    // Return true if there were no errors, false otherwise.
                    return _this._lastError === null;
                };
            };
            FakeAsyncTestZoneSpec._removeTimer = function (timers, id) {
                var index = timers.indexOf(id);
                if (index > -1) {
                    timers.splice(index, 1);
                }
            };
            FakeAsyncTestZoneSpec.prototype._dequeueTimer = function (id) {
                var _this = this;
                return function () {
                    FakeAsyncTestZoneSpec._removeTimer(_this.pendingTimers, id);
                };
            };
            FakeAsyncTestZoneSpec.prototype._requeuePeriodicTimer = function (fn, interval, args, id) {
                var _this = this;
                return function () {
                    // Requeue the timer callback if it's not been canceled.
                    if (_this.pendingPeriodicTimers.indexOf(id) !== -1) {
                        _this._scheduler.scheduleFunction(fn, interval, { args: args, isPeriodic: true, id: id, isRequeuePeriodic: true });
                    }
                };
            };
            FakeAsyncTestZoneSpec.prototype._dequeuePeriodicTimer = function (id) {
                var _this = this;
                return function () {
                    FakeAsyncTestZoneSpec._removeTimer(_this.pendingPeriodicTimers, id);
                };
            };
            FakeAsyncTestZoneSpec.prototype._setTimeout = function (fn, delay, args, isTimer) {
                if (isTimer === void 0) { isTimer = true; }
                var removeTimerFn = this._dequeueTimer(Scheduler.nextId);
                // Queue the callback and dequeue the timer on success and error.
                var cb = this._fnAndFlush(fn, { onSuccess: removeTimerFn, onError: removeTimerFn });
                var id = this._scheduler.scheduleFunction(cb, delay, { args: args, isRequestAnimationFrame: !isTimer });
                if (isTimer) {
                    this.pendingTimers.push(id);
                }
                return id;
            };
            FakeAsyncTestZoneSpec.prototype._clearTimeout = function (id) {
                FakeAsyncTestZoneSpec._removeTimer(this.pendingTimers, id);
                this._scheduler.removeScheduledFunctionWithId(id);
            };
            FakeAsyncTestZoneSpec.prototype._setInterval = function (fn, interval, args) {
                var id = Scheduler.nextId;
                var completers = { onSuccess: null, onError: this._dequeuePeriodicTimer(id) };
                var cb = this._fnAndFlush(fn, completers);
                // Use the callback created above to requeue on success.
                completers.onSuccess = this._requeuePeriodicTimer(cb, interval, args, id);
                // Queue the callback and dequeue the periodic timer only on error.
                this._scheduler.scheduleFunction(cb, interval, { args: args, isPeriodic: true });
                this.pendingPeriodicTimers.push(id);
                return id;
            };
            FakeAsyncTestZoneSpec.prototype._clearInterval = function (id) {
                FakeAsyncTestZoneSpec._removeTimer(this.pendingPeriodicTimers, id);
                this._scheduler.removeScheduledFunctionWithId(id);
            };
            FakeAsyncTestZoneSpec.prototype._resetLastErrorAndThrow = function () {
                var error = this._lastError || this._uncaughtPromiseErrors[0];
                this._uncaughtPromiseErrors.length = 0;
                this._lastError = null;
                throw error;
            };
            FakeAsyncTestZoneSpec.prototype.getCurrentTickTime = function () {
                return this._scheduler.getCurrentTickTime();
            };
            FakeAsyncTestZoneSpec.prototype.getFakeSystemTime = function () {
                return this._scheduler.getFakeSystemTime();
            };
            FakeAsyncTestZoneSpec.prototype.setFakeBaseSystemTime = function (realTime) {
                this._scheduler.setFakeBaseSystemTime(realTime);
            };
            FakeAsyncTestZoneSpec.prototype.getRealSystemTime = function () {
                return this._scheduler.getRealSystemTime();
            };
            FakeAsyncTestZoneSpec.patchDate = function () {
                if (!!global[Zone.__symbol__('disableDatePatching')]) {
                    // we don't want to patch global Date
                    // because in some case, global Date
                    // is already being patched, we need to provide
                    // an option to let user still use their
                    // own version of Date.
                    return;
                }
                if (global['Date'] === FakeDate) {
                    // already patched
                    return;
                }
                global['Date'] = FakeDate;
                FakeDate.prototype = OriginalDate.prototype;
                // try check and reset timers
                // because jasmine.clock().install() may
                // have replaced the global timer
                FakeAsyncTestZoneSpec.checkTimerPatch();
            };
            FakeAsyncTestZoneSpec.resetDate = function () {
                if (global['Date'] === FakeDate) {
                    global['Date'] = OriginalDate;
                }
            };
            FakeAsyncTestZoneSpec.checkTimerPatch = function () {
                if (global.setTimeout !== timers.setTimeout) {
                    global.setTimeout = timers.setTimeout;
                    global.clearTimeout = timers.clearTimeout;
                }
                if (global.setInterval !== timers.setInterval) {
                    global.setInterval = timers.setInterval;
                    global.clearInterval = timers.clearInterval;
                }
            };
            FakeAsyncTestZoneSpec.prototype.lockDatePatch = function () {
                this.patchDateLocked = true;
                FakeAsyncTestZoneSpec.patchDate();
            };
            FakeAsyncTestZoneSpec.prototype.unlockDatePatch = function () {
                this.patchDateLocked = false;
                FakeAsyncTestZoneSpec.resetDate();
            };
            FakeAsyncTestZoneSpec.prototype.tickToNext = function (steps, doTick, tickOptions) {
                if (steps === void 0) { steps = 1; }
                if (tickOptions === void 0) { tickOptions = { processNewMacroTasksSynchronously: true }; }
                if (steps <= 0) {
                    return;
                }
                FakeAsyncTestZoneSpec.assertInZone();
                this.flushMicrotasks();
                this._scheduler.tickToNext(steps, doTick, tickOptions);
                if (this._lastError !== null) {
                    this._resetLastErrorAndThrow();
                }
            };
            FakeAsyncTestZoneSpec.prototype.tick = function (millis, doTick, tickOptions) {
                if (millis === void 0) { millis = 0; }
                if (tickOptions === void 0) { tickOptions = { processNewMacroTasksSynchronously: true }; }
                FakeAsyncTestZoneSpec.assertInZone();
                this.flushMicrotasks();
                this._scheduler.tick(millis, doTick, tickOptions);
                if (this._lastError !== null) {
                    this._resetLastErrorAndThrow();
                }
            };
            FakeAsyncTestZoneSpec.prototype.flushMicrotasks = function () {
                var _this = this;
                FakeAsyncTestZoneSpec.assertInZone();
                var flushErrors = function () {
                    if (_this._lastError !== null || _this._uncaughtPromiseErrors.length) {
                        // If there is an error stop processing the microtask queue and rethrow the error.
                        _this._resetLastErrorAndThrow();
                    }
                };
                while (this._microtasks.length > 0) {
                    var microtask = this._microtasks.shift();
                    microtask.func.apply(microtask.target, microtask.args);
                }
                flushErrors();
            };
            FakeAsyncTestZoneSpec.prototype.flush = function (limit, flushPeriodic, doTick) {
                FakeAsyncTestZoneSpec.assertInZone();
                this.flushMicrotasks();
                var elapsed = this._scheduler.flush(limit, flushPeriodic, doTick);
                if (this._lastError !== null) {
                    this._resetLastErrorAndThrow();
                }
                return elapsed;
            };
            FakeAsyncTestZoneSpec.prototype.flushOnlyPendingTimers = function (doTick) {
                FakeAsyncTestZoneSpec.assertInZone();
                this.flushMicrotasks();
                var elapsed = this._scheduler.flushOnlyPendingTimers(doTick);
                if (this._lastError !== null) {
                    this._resetLastErrorAndThrow();
                }
                return elapsed;
            };
            FakeAsyncTestZoneSpec.prototype.removeAllTimers = function () {
                FakeAsyncTestZoneSpec.assertInZone();
                this._scheduler.removeAll();
                this.pendingPeriodicTimers = [];
                this.pendingTimers = [];
            };
            FakeAsyncTestZoneSpec.prototype.getTimerCount = function () {
                return this._scheduler.getTimerCount() + this._microtasks.length;
            };
            FakeAsyncTestZoneSpec.prototype.onScheduleTask = function (delegate, current, target, task) {
                switch (task.type) {
                    case 'microTask':
                        var args = task.data && task.data.args;
                        // should pass additional arguments to callback if have any
                        // currently we know process.nextTick will have such additional
                        // arguments
                        var additionalArgs = void 0;
                        if (args) {
                            var callbackIndex = task.data.cbIdx;
                            if (typeof args.length === 'number' && args.length > callbackIndex + 1) {
                                additionalArgs = Array.prototype.slice.call(args, callbackIndex + 1);
                            }
                        }
                        this._microtasks.push({
                            func: task.invoke,
                            args: additionalArgs,
                            target: task.data && task.data.target
                        });
                        break;
                    case 'macroTask':
                        switch (task.source) {
                            case 'setTimeout':
                                task.data['handleId'] = this._setTimeout(task.invoke, task.data['delay'], Array.prototype.slice.call(task.data['args'], 2));
                                break;
                            case 'setImmediate':
                                task.data['handleId'] = this._setTimeout(task.invoke, 0, Array.prototype.slice.call(task.data['args'], 1));
                                break;
                            case 'setInterval':
                                task.data['handleId'] = this._setInterval(task.invoke, task.data['delay'], Array.prototype.slice.call(task.data['args'], 2));
                                break;
                            case 'XMLHttpRequest.send':
                                throw new Error('Cannot make XHRs from within a fake async test. Request URL: ' +
                                    task.data['url']);
                            case 'requestAnimationFrame':
                            case 'webkitRequestAnimationFrame':
                            case 'mozRequestAnimationFrame':
                                // Simulate a requestAnimationFrame by using a setTimeout with 16 ms.
                                // (60 frames per second)
                                task.data['handleId'] = this._setTimeout(task.invoke, 16, task.data['args'], this.trackPendingRequestAnimationFrame);
                                break;
                            default:
                                // user can define which macroTask they want to support by passing
                                // macroTaskOptions
                                var macroTaskOption = this.findMacroTaskOption(task);
                                if (macroTaskOption) {
                                    var args_1 = task.data && task.data['args'];
                                    var delay = args_1 && args_1.length > 1 ? args_1[1] : 0;
                                    var callbackArgs = macroTaskOption.callbackArgs ? macroTaskOption.callbackArgs : args_1;
                                    if (!!macroTaskOption.isPeriodic) {
                                        // periodic macroTask, use setInterval to simulate
                                        task.data['handleId'] = this._setInterval(task.invoke, delay, callbackArgs);
                                        task.data.isPeriodic = true;
                                    }
                                    else {
                                        // not periodic, use setTimeout to simulate
                                        task.data['handleId'] = this._setTimeout(task.invoke, delay, callbackArgs);
                                    }
                                    break;
                                }
                                throw new Error('Unknown macroTask scheduled in fake async test: ' + task.source);
                        }
                        break;
                    case 'eventTask':
                        task = delegate.scheduleTask(target, task);
                        break;
                }
                return task;
            };
            FakeAsyncTestZoneSpec.prototype.onCancelTask = function (delegate, current, target, task) {
                switch (task.source) {
                    case 'setTimeout':
                    case 'requestAnimationFrame':
                    case 'webkitRequestAnimationFrame':
                    case 'mozRequestAnimationFrame':
                        return this._clearTimeout(task.data['handleId']);
                    case 'setInterval':
                        return this._clearInterval(task.data['handleId']);
                    default:
                        // user can define which macroTask they want to support by passing
                        // macroTaskOptions
                        var macroTaskOption = this.findMacroTaskOption(task);
                        if (macroTaskOption) {
                            var handleId = task.data['handleId'];
                            return macroTaskOption.isPeriodic ? this._clearInterval(handleId) :
                                this._clearTimeout(handleId);
                        }
                        return delegate.cancelTask(target, task);
                }
            };
            FakeAsyncTestZoneSpec.prototype.onInvoke = function (delegate, current, target, callback, applyThis, applyArgs, source) {
                try {
                    FakeAsyncTestZoneSpec.patchDate();
                    return delegate.invoke(target, callback, applyThis, applyArgs, source);
                }
                finally {
                    if (!this.patchDateLocked) {
                        FakeAsyncTestZoneSpec.resetDate();
                    }
                }
            };
            FakeAsyncTestZoneSpec.prototype.findMacroTaskOption = function (task) {
                if (!this.macroTaskOptions) {
                    return null;
                }
                for (var i = 0; i < this.macroTaskOptions.length; i++) {
                    var macroTaskOption = this.macroTaskOptions[i];
                    if (macroTaskOption.source === task.source) {
                        return macroTaskOption;
                    }
                }
                return null;
            };
            FakeAsyncTestZoneSpec.prototype.onHandleError = function (parentZoneDelegate, currentZone, targetZone, error) {
                this._lastError = error;
                return false; // Don't propagate error to parent zone.
            };
            return FakeAsyncTestZoneSpec;
        }());
        // Export the class so that new instances can be created with proper
        // constructor params.
        Zone['FakeAsyncTestZoneSpec'] = FakeAsyncTestZoneSpec;
    })(typeof window === 'object' && window || typeof self === 'object' && self || global);
    Zone.__load_patch('fakeasync', function (global, Zone, api) {
        var FakeAsyncTestZoneSpec = Zone && Zone['FakeAsyncTestZoneSpec'];
        function getProxyZoneSpec() {
            return Zone && Zone['ProxyZoneSpec'];
        }
        var _fakeAsyncTestZoneSpec = null;
        /**
         * Clears out the shared fake async zone for a test.
         * To be called in a global `beforeEach`.
         *
         * @experimental
         */
        function resetFakeAsyncZone() {
            if (_fakeAsyncTestZoneSpec) {
                _fakeAsyncTestZoneSpec.unlockDatePatch();
            }
            _fakeAsyncTestZoneSpec = null;
            // in node.js testing we may not have ProxyZoneSpec in which case there is nothing to reset.
            getProxyZoneSpec() && getProxyZoneSpec().assertPresent().resetDelegate();
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
         * ## Example
         *
         * {@example core/testing/ts/fake_async.ts region='basic'}
         *
         * @param fn
         * @returns The function wrapped to be executed in the fakeAsync zone
         *
         * @experimental
         */
        function fakeAsync(fn) {
            // Not using an arrow function to preserve context passed from call site
            var fakeAsyncFn = function () {
                var args = [];
                for (var _i = 0; _i < arguments.length; _i++) {
                    args[_i] = arguments[_i];
                }
                var ProxyZoneSpec = getProxyZoneSpec();
                if (!ProxyZoneSpec) {
                    throw new Error('ProxyZoneSpec is needed for the async() test helper but could not be found. ' +
                        'Please make sure that your environment includes zone.js/dist/proxy.js');
                }
                var proxyZoneSpec = ProxyZoneSpec.assertPresent();
                if (Zone.current.get('FakeAsyncTestZoneSpec')) {
                    throw new Error('fakeAsync() calls can not be nested');
                }
                try {
                    // in case jasmine.clock init a fakeAsyncTestZoneSpec
                    if (!_fakeAsyncTestZoneSpec) {
                        if (proxyZoneSpec.getDelegate() instanceof FakeAsyncTestZoneSpec) {
                            throw new Error('fakeAsync() calls can not be nested');
                        }
                        _fakeAsyncTestZoneSpec = new FakeAsyncTestZoneSpec();
                    }
                    var res = void 0;
                    var lastProxyZoneSpec = proxyZoneSpec.getDelegate();
                    proxyZoneSpec.setDelegate(_fakeAsyncTestZoneSpec);
                    _fakeAsyncTestZoneSpec.lockDatePatch();
                    try {
                        res = fn.apply(this, args);
                        flushMicrotasks();
                    }
                    finally {
                        proxyZoneSpec.setDelegate(lastProxyZoneSpec);
                    }
                    if (_fakeAsyncTestZoneSpec.pendingPeriodicTimers.length > 0) {
                        throw new Error(_fakeAsyncTestZoneSpec.pendingPeriodicTimers.length + " " +
                            "periodic timer(s) still in the queue.");
                    }
                    if (_fakeAsyncTestZoneSpec.pendingTimers.length > 0) {
                        throw new Error(_fakeAsyncTestZoneSpec.pendingTimers.length + " timer(s) still in the queue.");
                    }
                    return res;
                }
                finally {
                    resetFakeAsyncZone();
                }
            };
            fakeAsyncFn.isFakeAsync = true;
            return fakeAsyncFn;
        }
        function _getFakeAsyncZoneSpec() {
            if (_fakeAsyncTestZoneSpec == null) {
                _fakeAsyncTestZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
                if (_fakeAsyncTestZoneSpec == null) {
                    throw new Error('The code should be running in the fakeAsync zone to call this function');
                }
            }
            return _fakeAsyncTestZoneSpec;
        }
        /**
         * Simulates the asynchronous passage of time for the timers in the fakeAsync zone.
         *
         * The microtasks queue is drained at the very start of this function and after any timer callback
         * has been executed.
         *
         * ## Example
         *
         * {@example core/testing/ts/fake_async.ts region='basic'}
         *
         * @experimental
         */
        function tick(millis, ignoreNestedTimeout) {
            if (millis === void 0) { millis = 0; }
            if (ignoreNestedTimeout === void 0) { ignoreNestedTimeout = false; }
            _getFakeAsyncZoneSpec().tick(millis, null, ignoreNestedTimeout);
        }
        /**
         * Simulates the asynchronous passage of time for the timers in the fakeAsync zone by
         * draining the macrotask queue until it is empty. The returned value is the milliseconds
         * of time that would have been elapsed.
         *
         * @param maxTurns
         * @returns The simulated time elapsed, in millis.
         *
         * @experimental
         */
        function flush(maxTurns) {
            return _getFakeAsyncZoneSpec().flush(maxTurns);
        }
        /**
         * Discard all remaining periodic tasks.
         *
         * @experimental
         */
        function discardPeriodicTasks() {
            var zoneSpec = _getFakeAsyncZoneSpec();
            var pendingTimers = zoneSpec.pendingPeriodicTimers;
            zoneSpec.pendingPeriodicTimers.length = 0;
        }
        /**
         * Flush any pending microtasks.
         *
         * @experimental
         */
        function flushMicrotasks() {
            _getFakeAsyncZoneSpec().flushMicrotasks();
        }
        Zone[api.symbol('fakeAsyncTest')] =
            { resetFakeAsyncZone: resetFakeAsyncZone, flushMicrotasks: flushMicrotasks, discardPeriodicTasks: discardPeriodicTasks, tick: tick, flush: flush, fakeAsync: fakeAsync };
    }, true);
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Promise for async/fakeAsync zoneSpec test
     * can support async operation which not supported by zone.js
     * such as
     * it ('test jsonp in AsyncZone', async() => {
     *   new Promise(res => {
     *     jsonp(url, (data) => {
     *       // success callback
     *       res(data);
     *     });
     *   }).then((jsonpResult) => {
     *     // get jsonp result.
     *
     *     // user will expect AsyncZoneSpec wait for
     *     // then, but because jsonp is not zone aware
     *     // AsyncZone will finish before then is called.
     *   });
     * });
     */
    Zone.__load_patch('promisefortest', function (global, Zone, api) {
        var symbolState = api.symbol('state');
        var UNRESOLVED = null;
        var symbolParentUnresolved = api.symbol('parentUnresolved');
        // patch Promise.prototype.then to keep an internal
        // number for tracking unresolved chained promise
        // we will decrease this number when the parent promise
        // being resolved/rejected and chained promise was
        // scheduled as a microTask.
        // so we can know such kind of chained promise still
        // not resolved in AsyncTestZone
        Promise[api.symbol('patchPromiseForTest')] = function patchPromiseForTest() {
            var oriThen = Promise[Zone.__symbol__('ZonePromiseThen')];
            if (oriThen) {
                return;
            }
            oriThen = Promise[Zone.__symbol__('ZonePromiseThen')] = Promise.prototype.then;
            Promise.prototype.then = function () {
                var chained = oriThen.apply(this, arguments);
                if (this[symbolState] === UNRESOLVED) {
                    // parent promise is unresolved.
                    var asyncTestZoneSpec = Zone.current.get('AsyncTestZoneSpec');
                    if (asyncTestZoneSpec) {
                        asyncTestZoneSpec.unresolvedChainedPromiseCount++;
                        chained[symbolParentUnresolved] = true;
                    }
                }
                return chained;
            };
        };
        Promise[api.symbol('unPatchPromiseForTest')] = function unpatchPromiseForTest() {
            // restore origin then
            var oriThen = Promise[Zone.__symbol__('ZonePromiseThen')];
            if (oriThen) {
                Promise.prototype.then = oriThen;
                Promise[Zone.__symbol__('ZonePromiseThen')] = undefined;
            }
        };
    });
})));
