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
})));
