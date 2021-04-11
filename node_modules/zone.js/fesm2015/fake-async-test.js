'use strict';
/**
 * @license Angular v12.0.0-next.0
 * (c) 2010-2020 Google LLC. https://angular.io/
 * License: MIT
 */
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (global) {
    const OriginalDate = global.Date;
    // Since when we compile this file to `es2015`, and if we define
    // this `FakeDate` as `class FakeDate`, and then set `FakeDate.prototype`
    // there will be an error which is `Cannot assign to read only property 'prototype'`
    // so we need to use function implementation here.
    function FakeDate() {
        if (arguments.length === 0) {
            const d = new OriginalDate();
            d.setTime(FakeDate.now());
            return d;
        }
        else {
            const args = Array.prototype.slice.call(arguments);
            return new OriginalDate(...args);
        }
    }
    FakeDate.now = function () {
        const fakeAsyncTestZoneSpec = Zone.current.get('FakeAsyncTestZoneSpec');
        if (fakeAsyncTestZoneSpec) {
            return fakeAsyncTestZoneSpec.getFakeSystemTime();
        }
        return OriginalDate.now.apply(this, arguments);
    };
    FakeDate.UTC = OriginalDate.UTC;
    FakeDate.parse = OriginalDate.parse;
    // keep a reference for zone patched timer function
    const timers = {
        setTimeout: global.setTimeout,
        setInterval: global.setInterval,
        clearTimeout: global.clearTimeout,
        clearInterval: global.clearInterval
    };
    class Scheduler {
        constructor() {
            // Scheduler queue with the tuple of end time and callback function - sorted by end time.
            this._schedulerQueue = [];
            // Current simulated time in millis.
            this._currentTickTime = 0;
            // Current fake system base time in millis.
            this._currentFakeBaseSystemTime = OriginalDate.now();
            // track requeuePeriodicTimer
            this._currentTickRequeuePeriodicEntries = [];
        }
        getCurrentTickTime() {
            return this._currentTickTime;
        }
        getFakeSystemTime() {
            return this._currentFakeBaseSystemTime + this._currentTickTime;
        }
        setFakeBaseSystemTime(fakeBaseSystemTime) {
            this._currentFakeBaseSystemTime = fakeBaseSystemTime;
        }
        getRealSystemTime() {
            return OriginalDate.now();
        }
        scheduleFunction(cb, delay, options) {
            options = Object.assign({
                args: [],
                isPeriodic: false,
                isRequestAnimationFrame: false,
                id: -1,
                isRequeuePeriodic: false
            }, options);
            let currentId = options.id < 0 ? Scheduler.nextId++ : options.id;
            let endTime = this._currentTickTime + delay;
            // Insert so that scheduler queue remains sorted by end time.
            let newEntry = {
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
            let i = 0;
            for (; i < this._schedulerQueue.length; i++) {
                let currentEntry = this._schedulerQueue[i];
                if (newEntry.endTime < currentEntry.endTime) {
                    break;
                }
            }
            this._schedulerQueue.splice(i, 0, newEntry);
            return currentId;
        }
        removeScheduledFunctionWithId(id) {
            for (let i = 0; i < this._schedulerQueue.length; i++) {
                if (this._schedulerQueue[i].id == id) {
                    this._schedulerQueue.splice(i, 1);
                    break;
                }
            }
        }
        removeAll() {
            this._schedulerQueue = [];
        }
        getTimerCount() {
            return this._schedulerQueue.length;
        }
        tickToNext(step = 1, doTick, tickOptions) {
            if (this._schedulerQueue.length < step) {
                return;
            }
            // Find the last task currently queued in the scheduler queue and tick
            // till that time.
            const startTime = this._currentTickTime;
            const targetTask = this._schedulerQueue[step - 1];
            this.tick(targetTask.endTime - startTime, doTick, tickOptions);
        }
        tick(millis = 0, doTick, tickOptions) {
            let finalTime = this._currentTickTime + millis;
            let lastCurrentTime = 0;
            tickOptions = Object.assign({ processNewMacroTasksSynchronously: true }, tickOptions);
            // we need to copy the schedulerQueue so nested timeout
            // will not be wrongly called in the current tick
            // https://github.com/angular/angular/issues/33799
            const schedulerQueue = tickOptions.processNewMacroTasksSynchronously ?
                this._schedulerQueue :
                this._schedulerQueue.slice();
            if (schedulerQueue.length === 0 && doTick) {
                doTick(millis);
                return;
            }
            while (schedulerQueue.length > 0) {
                // clear requeueEntries before each loop
                this._currentTickRequeuePeriodicEntries = [];
                let current = schedulerQueue[0];
                if (finalTime < current.endTime) {
                    // Done processing the queue since it's sorted by endTime.
                    break;
                }
                else {
                    // Time to run scheduled function. Remove it from the head of queue.
                    let current = schedulerQueue.shift();
                    if (!tickOptions.processNewMacroTasksSynchronously) {
                        const idx = this._schedulerQueue.indexOf(current);
                        if (idx >= 0) {
                            this._schedulerQueue.splice(idx, 1);
                        }
                    }
                    lastCurrentTime = this._currentTickTime;
                    this._currentTickTime = current.endTime;
                    if (doTick) {
                        doTick(this._currentTickTime - lastCurrentTime);
                    }
                    let retval = current.func.apply(global, current.isRequestAnimationFrame ? [this._currentTickTime] : current.args);
                    if (!retval) {
                        // Uncaught exception in the current scheduled function. Stop processing the queue.
                        break;
                    }
                    // check is there any requeue periodic entry is added in
                    // current loop, if there is, we need to add to current loop
                    if (!tickOptions.processNewMacroTasksSynchronously) {
                        this._currentTickRequeuePeriodicEntries.forEach(newEntry => {
                            let i = 0;
                            for (; i < schedulerQueue.length; i++) {
                                const currentEntry = schedulerQueue[i];
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
        }
        flushOnlyPendingTimers(doTick) {
            if (this._schedulerQueue.length === 0) {
                return 0;
            }
            // Find the last task currently queued in the scheduler queue and tick
            // till that time.
            const startTime = this._currentTickTime;
            const lastTask = this._schedulerQueue[this._schedulerQueue.length - 1];
            this.tick(lastTask.endTime - startTime, doTick, { processNewMacroTasksSynchronously: false });
            return this._currentTickTime - startTime;
        }
        flush(limit = 20, flushPeriodic = false, doTick) {
            if (flushPeriodic) {
                return this.flushPeriodic(doTick);
            }
            else {
                return this.flushNonPeriodic(limit, doTick);
            }
        }
        flushPeriodic(doTick) {
            if (this._schedulerQueue.length === 0) {
                return 0;
            }
            // Find the last task currently queued in the scheduler queue and tick
            // till that time.
            const startTime = this._currentTickTime;
            const lastTask = this._schedulerQueue[this._schedulerQueue.length - 1];
            this.tick(lastTask.endTime - startTime, doTick);
            return this._currentTickTime - startTime;
        }
        flushNonPeriodic(limit, doTick) {
            const startTime = this._currentTickTime;
            let lastCurrentTime = 0;
            let count = 0;
            while (this._schedulerQueue.length > 0) {
                count++;
                if (count > limit) {
                    throw new Error('flush failed after reaching the limit of ' + limit +
                        ' tasks. Does your code use a polling timeout?');
                }
                // flush only non-periodic timers.
                // If the only remaining tasks are periodic(or requestAnimationFrame), finish flushing.
                if (this._schedulerQueue.filter(task => !task.isPeriodic && !task.isRequestAnimationFrame)
                    .length === 0) {
                    break;
                }
                const current = this._schedulerQueue.shift();
                lastCurrentTime = this._currentTickTime;
                this._currentTickTime = current.endTime;
                if (doTick) {
                    // Update any secondary schedulers like Jasmine mock Date.
                    doTick(this._currentTickTime - lastCurrentTime);
                }
                const retval = current.func.apply(global, current.args);
                if (!retval) {
                    // Uncaught exception in the current scheduled function. Stop processing the queue.
                    break;
                }
            }
            return this._currentTickTime - startTime;
        }
    }
    // Next scheduler id.
    Scheduler.nextId = 1;
    class FakeAsyncTestZoneSpec {
        constructor(namePrefix, trackPendingRequestAnimationFrame = false, macroTaskOptions) {
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
        static assertInZone() {
            if (Zone.current.get('FakeAsyncTestZoneSpec') == null) {
                throw new Error('The code should be running in the fakeAsync zone to call this function');
            }
        }
        _fnAndFlush(fn, completers) {
            return (...args) => {
                fn.apply(global, args);
                if (this._lastError === null) { // Success
                    if (completers.onSuccess != null) {
                        completers.onSuccess.apply(global);
                    }
                    // Flush microtasks only on success.
                    this.flushMicrotasks();
                }
                else { // Failure
                    if (completers.onError != null) {
                        completers.onError.apply(global);
                    }
                }
                // Return true if there were no errors, false otherwise.
                return this._lastError === null;
            };
        }
        static _removeTimer(timers, id) {
            let index = timers.indexOf(id);
            if (index > -1) {
                timers.splice(index, 1);
            }
        }
        _dequeueTimer(id) {
            return () => {
                FakeAsyncTestZoneSpec._removeTimer(this.pendingTimers, id);
            };
        }
        _requeuePeriodicTimer(fn, interval, args, id) {
            return () => {
                // Requeue the timer callback if it's not been canceled.
                if (this.pendingPeriodicTimers.indexOf(id) !== -1) {
                    this._scheduler.scheduleFunction(fn, interval, { args, isPeriodic: true, id, isRequeuePeriodic: true });
                }
            };
        }
        _dequeuePeriodicTimer(id) {
            return () => {
                FakeAsyncTestZoneSpec._removeTimer(this.pendingPeriodicTimers, id);
            };
        }
        _setTimeout(fn, delay, args, isTimer = true) {
            let removeTimerFn = this._dequeueTimer(Scheduler.nextId);
            // Queue the callback and dequeue the timer on success and error.
            let cb = this._fnAndFlush(fn, { onSuccess: removeTimerFn, onError: removeTimerFn });
            let id = this._scheduler.scheduleFunction(cb, delay, { args, isRequestAnimationFrame: !isTimer });
            if (isTimer) {
                this.pendingTimers.push(id);
            }
            return id;
        }
        _clearTimeout(id) {
            FakeAsyncTestZoneSpec._removeTimer(this.pendingTimers, id);
            this._scheduler.removeScheduledFunctionWithId(id);
        }
        _setInterval(fn, interval, args) {
            let id = Scheduler.nextId;
            let completers = { onSuccess: null, onError: this._dequeuePeriodicTimer(id) };
            let cb = this._fnAndFlush(fn, completers);
            // Use the callback created above to requeue on success.
            completers.onSuccess = this._requeuePeriodicTimer(cb, interval, args, id);
            // Queue the callback and dequeue the periodic timer only on error.
            this._scheduler.scheduleFunction(cb, interval, { args, isPeriodic: true });
            this.pendingPeriodicTimers.push(id);
            return id;
        }
        _clearInterval(id) {
            FakeAsyncTestZoneSpec._removeTimer(this.pendingPeriodicTimers, id);
            this._scheduler.removeScheduledFunctionWithId(id);
        }
        _resetLastErrorAndThrow() {
            let error = this._lastError || this._uncaughtPromiseErrors[0];
            this._uncaughtPromiseErrors.length = 0;
            this._lastError = null;
            throw error;
        }
        getCurrentTickTime() {
            return this._scheduler.getCurrentTickTime();
        }
        getFakeSystemTime() {
            return this._scheduler.getFakeSystemTime();
        }
        setFakeBaseSystemTime(realTime) {
            this._scheduler.setFakeBaseSystemTime(realTime);
        }
        getRealSystemTime() {
            return this._scheduler.getRealSystemTime();
        }
        static patchDate() {
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
        }
        static resetDate() {
            if (global['Date'] === FakeDate) {
                global['Date'] = OriginalDate;
            }
        }
        static checkTimerPatch() {
            if (global.setTimeout !== timers.setTimeout) {
                global.setTimeout = timers.setTimeout;
                global.clearTimeout = timers.clearTimeout;
            }
            if (global.setInterval !== timers.setInterval) {
                global.setInterval = timers.setInterval;
                global.clearInterval = timers.clearInterval;
            }
        }
        lockDatePatch() {
            this.patchDateLocked = true;
            FakeAsyncTestZoneSpec.patchDate();
        }
        unlockDatePatch() {
            this.patchDateLocked = false;
            FakeAsyncTestZoneSpec.resetDate();
        }
        tickToNext(steps = 1, doTick, tickOptions = { processNewMacroTasksSynchronously: true }) {
            if (steps <= 0) {
                return;
            }
            FakeAsyncTestZoneSpec.assertInZone();
            this.flushMicrotasks();
            this._scheduler.tickToNext(steps, doTick, tickOptions);
            if (this._lastError !== null) {
                this._resetLastErrorAndThrow();
            }
        }
        tick(millis = 0, doTick, tickOptions = { processNewMacroTasksSynchronously: true }) {
            FakeAsyncTestZoneSpec.assertInZone();
            this.flushMicrotasks();
            this._scheduler.tick(millis, doTick, tickOptions);
            if (this._lastError !== null) {
                this._resetLastErrorAndThrow();
            }
        }
        flushMicrotasks() {
            FakeAsyncTestZoneSpec.assertInZone();
            const flushErrors = () => {
                if (this._lastError !== null || this._uncaughtPromiseErrors.length) {
                    // If there is an error stop processing the microtask queue and rethrow the error.
                    this._resetLastErrorAndThrow();
                }
            };
            while (this._microtasks.length > 0) {
                let microtask = this._microtasks.shift();
                microtask.func.apply(microtask.target, microtask.args);
            }
            flushErrors();
        }
        flush(limit, flushPeriodic, doTick) {
            FakeAsyncTestZoneSpec.assertInZone();
            this.flushMicrotasks();
            const elapsed = this._scheduler.flush(limit, flushPeriodic, doTick);
            if (this._lastError !== null) {
                this._resetLastErrorAndThrow();
            }
            return elapsed;
        }
        flushOnlyPendingTimers(doTick) {
            FakeAsyncTestZoneSpec.assertInZone();
            this.flushMicrotasks();
            const elapsed = this._scheduler.flushOnlyPendingTimers(doTick);
            if (this._lastError !== null) {
                this._resetLastErrorAndThrow();
            }
            return elapsed;
        }
        removeAllTimers() {
            FakeAsyncTestZoneSpec.assertInZone();
            this._scheduler.removeAll();
            this.pendingPeriodicTimers = [];
            this.pendingTimers = [];
        }
        getTimerCount() {
            return this._scheduler.getTimerCount() + this._microtasks.length;
        }
        onScheduleTask(delegate, current, target, task) {
            switch (task.type) {
                case 'microTask':
                    let args = task.data && task.data.args;
                    // should pass additional arguments to callback if have any
                    // currently we know process.nextTick will have such additional
                    // arguments
                    let additionalArgs;
                    if (args) {
                        let callbackIndex = task.data.cbIdx;
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
                            const macroTaskOption = this.findMacroTaskOption(task);
                            if (macroTaskOption) {
                                const args = task.data && task.data['args'];
                                const delay = args && args.length > 1 ? args[1] : 0;
                                let callbackArgs = macroTaskOption.callbackArgs ? macroTaskOption.callbackArgs : args;
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
        }
        onCancelTask(delegate, current, target, task) {
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
                    const macroTaskOption = this.findMacroTaskOption(task);
                    if (macroTaskOption) {
                        const handleId = task.data['handleId'];
                        return macroTaskOption.isPeriodic ? this._clearInterval(handleId) :
                            this._clearTimeout(handleId);
                    }
                    return delegate.cancelTask(target, task);
            }
        }
        onInvoke(delegate, current, target, callback, applyThis, applyArgs, source) {
            try {
                FakeAsyncTestZoneSpec.patchDate();
                return delegate.invoke(target, callback, applyThis, applyArgs, source);
            }
            finally {
                if (!this.patchDateLocked) {
                    FakeAsyncTestZoneSpec.resetDate();
                }
            }
        }
        findMacroTaskOption(task) {
            if (!this.macroTaskOptions) {
                return null;
            }
            for (let i = 0; i < this.macroTaskOptions.length; i++) {
                const macroTaskOption = this.macroTaskOptions[i];
                if (macroTaskOption.source === task.source) {
                    return macroTaskOption;
                }
            }
            return null;
        }
        onHandleError(parentZoneDelegate, currentZone, targetZone, error) {
            this._lastError = error;
            return false; // Don't propagate error to parent zone.
        }
    }
    // Export the class so that new instances can be created with proper
    // constructor params.
    Zone['FakeAsyncTestZoneSpec'] = FakeAsyncTestZoneSpec;
})(typeof window === 'object' && window || typeof self === 'object' && self || global);
Zone.__load_patch('fakeasync', (global, Zone, api) => {
    const FakeAsyncTestZoneSpec = Zone && Zone['FakeAsyncTestZoneSpec'];
    function getProxyZoneSpec() {
        return Zone && Zone['ProxyZoneSpec'];
    }
    let _fakeAsyncTestZoneSpec = null;
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
        const fakeAsyncFn = function (...args) {
            const ProxyZoneSpec = getProxyZoneSpec();
            if (!ProxyZoneSpec) {
                throw new Error('ProxyZoneSpec is needed for the async() test helper but could not be found. ' +
                    'Please make sure that your environment includes zone.js/dist/proxy.js');
            }
            const proxyZoneSpec = ProxyZoneSpec.assertPresent();
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
                let res;
                const lastProxyZoneSpec = proxyZoneSpec.getDelegate();
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
                    throw new Error(`${_fakeAsyncTestZoneSpec.pendingPeriodicTimers.length} ` +
                        `periodic timer(s) still in the queue.`);
                }
                if (_fakeAsyncTestZoneSpec.pendingTimers.length > 0) {
                    throw new Error(`${_fakeAsyncTestZoneSpec.pendingTimers.length} timer(s) still in the queue.`);
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
    function tick(millis = 0, ignoreNestedTimeout = false) {
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
        const zoneSpec = _getFakeAsyncZoneSpec();
        const pendingTimers = zoneSpec.pendingPeriodicTimers;
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
        { resetFakeAsyncZone, flushMicrotasks, discardPeriodicTasks, tick, flush, fakeAsync };
}, true);
