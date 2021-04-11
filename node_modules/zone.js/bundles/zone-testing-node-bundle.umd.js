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
    var Zone$1 = (function (global) {
        var performance = global['performance'];
        function mark(name) {
            performance && performance['mark'] && performance['mark'](name);
        }
        function performanceMeasure(name, label) {
            performance && performance['measure'] && performance['measure'](name, label);
        }
        mark('Zone');
        // Initialize before it's accessed below.
        // __Zone_symbol_prefix global can be used to override the default zone
        // symbol prefix with a custom one if needed.
        var symbolPrefix = global['__Zone_symbol_prefix'] || '__zone_symbol__';
        function __symbol__(name) {
            return symbolPrefix + name;
        }
        var checkDuplicate = global[__symbol__('forceDuplicateZoneCheck')] === true;
        if (global['Zone']) {
            // if global['Zone'] already exists (maybe zone.js was already loaded or
            // some other lib also registered a global object named Zone), we may need
            // to throw an error, but sometimes user may not want this error.
            // For example,
            // we have two web pages, page1 includes zone.js, page2 doesn't.
            // and the 1st time user load page1 and page2, everything work fine,
            // but when user load page2 again, error occurs because global['Zone'] already exists.
            // so we add a flag to let user choose whether to throw this error or not.
            // By default, if existing Zone is from zone.js, we will not throw the error.
            if (checkDuplicate || typeof global['Zone'].__symbol__ !== 'function') {
                throw new Error('Zone already loaded.');
            }
            else {
                return global['Zone'];
            }
        }
        var Zone = /** @class */ (function () {
            function Zone(parent, zoneSpec) {
                this._parent = parent;
                this._name = zoneSpec ? zoneSpec.name || 'unnamed' : '<root>';
                this._properties = zoneSpec && zoneSpec.properties || {};
                this._zoneDelegate =
                    new ZoneDelegate(this, this._parent && this._parent._zoneDelegate, zoneSpec);
            }
            Zone.assertZonePatched = function () {
                if (global['Promise'] !== patches['ZoneAwarePromise']) {
                    throw new Error('Zone.js has detected that ZoneAwarePromise `(window|global).Promise` ' +
                        'has been overwritten.\n' +
                        'Most likely cause is that a Promise polyfill has been loaded ' +
                        'after Zone.js (Polyfilling Promise api is not necessary when zone.js is loaded. ' +
                        'If you must load one, do so before loading zone.js.)');
                }
            };
            Object.defineProperty(Zone, "root", {
                get: function () {
                    var zone = Zone.current;
                    while (zone.parent) {
                        zone = zone.parent;
                    }
                    return zone;
                },
                enumerable: false,
                configurable: true
            });
            Object.defineProperty(Zone, "current", {
                get: function () {
                    return _currentZoneFrame.zone;
                },
                enumerable: false,
                configurable: true
            });
            Object.defineProperty(Zone, "currentTask", {
                get: function () {
                    return _currentTask;
                },
                enumerable: false,
                configurable: true
            });
            // tslint:disable-next-line:require-internal-with-underscore
            Zone.__load_patch = function (name, fn, ignoreDuplicate) {
                if (ignoreDuplicate === void 0) { ignoreDuplicate = false; }
                if (patches.hasOwnProperty(name)) {
                    // `checkDuplicate` option is defined from global variable
                    // so it works for all modules.
                    // `ignoreDuplicate` can work for the specified module
                    if (!ignoreDuplicate && checkDuplicate) {
                        throw Error('Already loaded patch: ' + name);
                    }
                }
                else if (!global['__Zone_disable_' + name]) {
                    var perfName = 'Zone:' + name;
                    mark(perfName);
                    patches[name] = fn(global, Zone, _api);
                    performanceMeasure(perfName, perfName);
                }
            };
            Object.defineProperty(Zone.prototype, "parent", {
                get: function () {
                    return this._parent;
                },
                enumerable: false,
                configurable: true
            });
            Object.defineProperty(Zone.prototype, "name", {
                get: function () {
                    return this._name;
                },
                enumerable: false,
                configurable: true
            });
            Zone.prototype.get = function (key) {
                var zone = this.getZoneWith(key);
                if (zone)
                    return zone._properties[key];
            };
            Zone.prototype.getZoneWith = function (key) {
                var current = this;
                while (current) {
                    if (current._properties.hasOwnProperty(key)) {
                        return current;
                    }
                    current = current._parent;
                }
                return null;
            };
            Zone.prototype.fork = function (zoneSpec) {
                if (!zoneSpec)
                    throw new Error('ZoneSpec required!');
                return this._zoneDelegate.fork(this, zoneSpec);
            };
            Zone.prototype.wrap = function (callback, source) {
                if (typeof callback !== 'function') {
                    throw new Error('Expecting function got: ' + callback);
                }
                var _callback = this._zoneDelegate.intercept(this, callback, source);
                var zone = this;
                return function () {
                    return zone.runGuarded(_callback, this, arguments, source);
                };
            };
            Zone.prototype.run = function (callback, applyThis, applyArgs, source) {
                _currentZoneFrame = { parent: _currentZoneFrame, zone: this };
                try {
                    return this._zoneDelegate.invoke(this, callback, applyThis, applyArgs, source);
                }
                finally {
                    _currentZoneFrame = _currentZoneFrame.parent;
                }
            };
            Zone.prototype.runGuarded = function (callback, applyThis, applyArgs, source) {
                if (applyThis === void 0) { applyThis = null; }
                _currentZoneFrame = { parent: _currentZoneFrame, zone: this };
                try {
                    try {
                        return this._zoneDelegate.invoke(this, callback, applyThis, applyArgs, source);
                    }
                    catch (error) {
                        if (this._zoneDelegate.handleError(this, error)) {
                            throw error;
                        }
                    }
                }
                finally {
                    _currentZoneFrame = _currentZoneFrame.parent;
                }
            };
            Zone.prototype.runTask = function (task, applyThis, applyArgs) {
                if (task.zone != this) {
                    throw new Error('A task can only be run in the zone of creation! (Creation: ' +
                        (task.zone || NO_ZONE).name + '; Execution: ' + this.name + ')');
                }
                // https://github.com/angular/zone.js/issues/778, sometimes eventTask
                // will run in notScheduled(canceled) state, we should not try to
                // run such kind of task but just return
                if (task.state === notScheduled && (task.type === eventTask || task.type === macroTask)) {
                    return;
                }
                var reEntryGuard = task.state != running;
                reEntryGuard && task._transitionTo(running, scheduled);
                task.runCount++;
                var previousTask = _currentTask;
                _currentTask = task;
                _currentZoneFrame = { parent: _currentZoneFrame, zone: this };
                try {
                    if (task.type == macroTask && task.data && !task.data.isPeriodic) {
                        task.cancelFn = undefined;
                    }
                    try {
                        return this._zoneDelegate.invokeTask(this, task, applyThis, applyArgs);
                    }
                    catch (error) {
                        if (this._zoneDelegate.handleError(this, error)) {
                            throw error;
                        }
                    }
                }
                finally {
                    // if the task's state is notScheduled or unknown, then it has already been cancelled
                    // we should not reset the state to scheduled
                    if (task.state !== notScheduled && task.state !== unknown) {
                        if (task.type == eventTask || (task.data && task.data.isPeriodic)) {
                            reEntryGuard && task._transitionTo(scheduled, running);
                        }
                        else {
                            task.runCount = 0;
                            this._updateTaskCount(task, -1);
                            reEntryGuard &&
                                task._transitionTo(notScheduled, running, notScheduled);
                        }
                    }
                    _currentZoneFrame = _currentZoneFrame.parent;
                    _currentTask = previousTask;
                }
            };
            Zone.prototype.scheduleTask = function (task) {
                if (task.zone && task.zone !== this) {
                    // check if the task was rescheduled, the newZone
                    // should not be the children of the original zone
                    var newZone = this;
                    while (newZone) {
                        if (newZone === task.zone) {
                            throw Error("can not reschedule task to " + this.name + " which is descendants of the original zone " + task.zone.name);
                        }
                        newZone = newZone.parent;
                    }
                }
                task._transitionTo(scheduling, notScheduled);
                var zoneDelegates = [];
                task._zoneDelegates = zoneDelegates;
                task._zone = this;
                try {
                    task = this._zoneDelegate.scheduleTask(this, task);
                }
                catch (err) {
                    // should set task's state to unknown when scheduleTask throw error
                    // because the err may from reschedule, so the fromState maybe notScheduled
                    task._transitionTo(unknown, scheduling, notScheduled);
                    // TODO: @JiaLiPassion, should we check the result from handleError?
                    this._zoneDelegate.handleError(this, err);
                    throw err;
                }
                if (task._zoneDelegates === zoneDelegates) {
                    // we have to check because internally the delegate can reschedule the task.
                    this._updateTaskCount(task, 1);
                }
                if (task.state == scheduling) {
                    task._transitionTo(scheduled, scheduling);
                }
                return task;
            };
            Zone.prototype.scheduleMicroTask = function (source, callback, data, customSchedule) {
                return this.scheduleTask(new ZoneTask(microTask, source, callback, data, customSchedule, undefined));
            };
            Zone.prototype.scheduleMacroTask = function (source, callback, data, customSchedule, customCancel) {
                return this.scheduleTask(new ZoneTask(macroTask, source, callback, data, customSchedule, customCancel));
            };
            Zone.prototype.scheduleEventTask = function (source, callback, data, customSchedule, customCancel) {
                return this.scheduleTask(new ZoneTask(eventTask, source, callback, data, customSchedule, customCancel));
            };
            Zone.prototype.cancelTask = function (task) {
                if (task.zone != this)
                    throw new Error('A task can only be cancelled in the zone of creation! (Creation: ' +
                        (task.zone || NO_ZONE).name + '; Execution: ' + this.name + ')');
                task._transitionTo(canceling, scheduled, running);
                try {
                    this._zoneDelegate.cancelTask(this, task);
                }
                catch (err) {
                    // if error occurs when cancelTask, transit the state to unknown
                    task._transitionTo(unknown, canceling);
                    this._zoneDelegate.handleError(this, err);
                    throw err;
                }
                this._updateTaskCount(task, -1);
                task._transitionTo(notScheduled, canceling);
                task.runCount = 0;
                return task;
            };
            Zone.prototype._updateTaskCount = function (task, count) {
                var zoneDelegates = task._zoneDelegates;
                if (count == -1) {
                    task._zoneDelegates = null;
                }
                for (var i = 0; i < zoneDelegates.length; i++) {
                    zoneDelegates[i]._updateTaskCount(task.type, count);
                }
            };
            return Zone;
        }());
        // tslint:disable-next-line:require-internal-with-underscore
        Zone.__symbol__ = __symbol__;
        var DELEGATE_ZS = {
            name: '',
            onHasTask: function (delegate, _, target, hasTaskState) { return delegate.hasTask(target, hasTaskState); },
            onScheduleTask: function (delegate, _, target, task) { return delegate.scheduleTask(target, task); },
            onInvokeTask: function (delegate, _, target, task, applyThis, applyArgs) { return delegate.invokeTask(target, task, applyThis, applyArgs); },
            onCancelTask: function (delegate, _, target, task) { return delegate.cancelTask(target, task); }
        };
        var ZoneDelegate = /** @class */ (function () {
            function ZoneDelegate(zone, parentDelegate, zoneSpec) {
                this._taskCounts = { 'microTask': 0, 'macroTask': 0, 'eventTask': 0 };
                this.zone = zone;
                this._parentDelegate = parentDelegate;
                this._forkZS = zoneSpec && (zoneSpec && zoneSpec.onFork ? zoneSpec : parentDelegate._forkZS);
                this._forkDlgt = zoneSpec && (zoneSpec.onFork ? parentDelegate : parentDelegate._forkDlgt);
                this._forkCurrZone =
                    zoneSpec && (zoneSpec.onFork ? this.zone : parentDelegate._forkCurrZone);
                this._interceptZS =
                    zoneSpec && (zoneSpec.onIntercept ? zoneSpec : parentDelegate._interceptZS);
                this._interceptDlgt =
                    zoneSpec && (zoneSpec.onIntercept ? parentDelegate : parentDelegate._interceptDlgt);
                this._interceptCurrZone =
                    zoneSpec && (zoneSpec.onIntercept ? this.zone : parentDelegate._interceptCurrZone);
                this._invokeZS = zoneSpec && (zoneSpec.onInvoke ? zoneSpec : parentDelegate._invokeZS);
                this._invokeDlgt =
                    zoneSpec && (zoneSpec.onInvoke ? parentDelegate : parentDelegate._invokeDlgt);
                this._invokeCurrZone =
                    zoneSpec && (zoneSpec.onInvoke ? this.zone : parentDelegate._invokeCurrZone);
                this._handleErrorZS =
                    zoneSpec && (zoneSpec.onHandleError ? zoneSpec : parentDelegate._handleErrorZS);
                this._handleErrorDlgt =
                    zoneSpec && (zoneSpec.onHandleError ? parentDelegate : parentDelegate._handleErrorDlgt);
                this._handleErrorCurrZone =
                    zoneSpec && (zoneSpec.onHandleError ? this.zone : parentDelegate._handleErrorCurrZone);
                this._scheduleTaskZS =
                    zoneSpec && (zoneSpec.onScheduleTask ? zoneSpec : parentDelegate._scheduleTaskZS);
                this._scheduleTaskDlgt = zoneSpec &&
                    (zoneSpec.onScheduleTask ? parentDelegate : parentDelegate._scheduleTaskDlgt);
                this._scheduleTaskCurrZone =
                    zoneSpec && (zoneSpec.onScheduleTask ? this.zone : parentDelegate._scheduleTaskCurrZone);
                this._invokeTaskZS =
                    zoneSpec && (zoneSpec.onInvokeTask ? zoneSpec : parentDelegate._invokeTaskZS);
                this._invokeTaskDlgt =
                    zoneSpec && (zoneSpec.onInvokeTask ? parentDelegate : parentDelegate._invokeTaskDlgt);
                this._invokeTaskCurrZone =
                    zoneSpec && (zoneSpec.onInvokeTask ? this.zone : parentDelegate._invokeTaskCurrZone);
                this._cancelTaskZS =
                    zoneSpec && (zoneSpec.onCancelTask ? zoneSpec : parentDelegate._cancelTaskZS);
                this._cancelTaskDlgt =
                    zoneSpec && (zoneSpec.onCancelTask ? parentDelegate : parentDelegate._cancelTaskDlgt);
                this._cancelTaskCurrZone =
                    zoneSpec && (zoneSpec.onCancelTask ? this.zone : parentDelegate._cancelTaskCurrZone);
                this._hasTaskZS = null;
                this._hasTaskDlgt = null;
                this._hasTaskDlgtOwner = null;
                this._hasTaskCurrZone = null;
                var zoneSpecHasTask = zoneSpec && zoneSpec.onHasTask;
                var parentHasTask = parentDelegate && parentDelegate._hasTaskZS;
                if (zoneSpecHasTask || parentHasTask) {
                    // If we need to report hasTask, than this ZS needs to do ref counting on tasks. In such
                    // a case all task related interceptors must go through this ZD. We can't short circuit it.
                    this._hasTaskZS = zoneSpecHasTask ? zoneSpec : DELEGATE_ZS;
                    this._hasTaskDlgt = parentDelegate;
                    this._hasTaskDlgtOwner = this;
                    this._hasTaskCurrZone = zone;
                    if (!zoneSpec.onScheduleTask) {
                        this._scheduleTaskZS = DELEGATE_ZS;
                        this._scheduleTaskDlgt = parentDelegate;
                        this._scheduleTaskCurrZone = this.zone;
                    }
                    if (!zoneSpec.onInvokeTask) {
                        this._invokeTaskZS = DELEGATE_ZS;
                        this._invokeTaskDlgt = parentDelegate;
                        this._invokeTaskCurrZone = this.zone;
                    }
                    if (!zoneSpec.onCancelTask) {
                        this._cancelTaskZS = DELEGATE_ZS;
                        this._cancelTaskDlgt = parentDelegate;
                        this._cancelTaskCurrZone = this.zone;
                    }
                }
            }
            ZoneDelegate.prototype.fork = function (targetZone, zoneSpec) {
                return this._forkZS ? this._forkZS.onFork(this._forkDlgt, this.zone, targetZone, zoneSpec) :
                    new Zone(targetZone, zoneSpec);
            };
            ZoneDelegate.prototype.intercept = function (targetZone, callback, source) {
                return this._interceptZS ?
                    this._interceptZS.onIntercept(this._interceptDlgt, this._interceptCurrZone, targetZone, callback, source) :
                    callback;
            };
            ZoneDelegate.prototype.invoke = function (targetZone, callback, applyThis, applyArgs, source) {
                return this._invokeZS ? this._invokeZS.onInvoke(this._invokeDlgt, this._invokeCurrZone, targetZone, callback, applyThis, applyArgs, source) :
                    callback.apply(applyThis, applyArgs);
            };
            ZoneDelegate.prototype.handleError = function (targetZone, error) {
                return this._handleErrorZS ?
                    this._handleErrorZS.onHandleError(this._handleErrorDlgt, this._handleErrorCurrZone, targetZone, error) :
                    true;
            };
            ZoneDelegate.prototype.scheduleTask = function (targetZone, task) {
                var returnTask = task;
                if (this._scheduleTaskZS) {
                    if (this._hasTaskZS) {
                        returnTask._zoneDelegates.push(this._hasTaskDlgtOwner);
                    }
                    // clang-format off
                    returnTask = this._scheduleTaskZS.onScheduleTask(this._scheduleTaskDlgt, this._scheduleTaskCurrZone, targetZone, task);
                    // clang-format on
                    if (!returnTask)
                        returnTask = task;
                }
                else {
                    if (task.scheduleFn) {
                        task.scheduleFn(task);
                    }
                    else if (task.type == microTask) {
                        scheduleMicroTask(task);
                    }
                    else {
                        throw new Error('Task is missing scheduleFn.');
                    }
                }
                return returnTask;
            };
            ZoneDelegate.prototype.invokeTask = function (targetZone, task, applyThis, applyArgs) {
                return this._invokeTaskZS ? this._invokeTaskZS.onInvokeTask(this._invokeTaskDlgt, this._invokeTaskCurrZone, targetZone, task, applyThis, applyArgs) :
                    task.callback.apply(applyThis, applyArgs);
            };
            ZoneDelegate.prototype.cancelTask = function (targetZone, task) {
                var value;
                if (this._cancelTaskZS) {
                    value = this._cancelTaskZS.onCancelTask(this._cancelTaskDlgt, this._cancelTaskCurrZone, targetZone, task);
                }
                else {
                    if (!task.cancelFn) {
                        throw Error('Task is not cancelable');
                    }
                    value = task.cancelFn(task);
                }
                return value;
            };
            ZoneDelegate.prototype.hasTask = function (targetZone, isEmpty) {
                // hasTask should not throw error so other ZoneDelegate
                // can still trigger hasTask callback
                try {
                    this._hasTaskZS &&
                        this._hasTaskZS.onHasTask(this._hasTaskDlgt, this._hasTaskCurrZone, targetZone, isEmpty);
                }
                catch (err) {
                    this.handleError(targetZone, err);
                }
            };
            // tslint:disable-next-line:require-internal-with-underscore
            ZoneDelegate.prototype._updateTaskCount = function (type, count) {
                var counts = this._taskCounts;
                var prev = counts[type];
                var next = counts[type] = prev + count;
                if (next < 0) {
                    throw new Error('More tasks executed then were scheduled.');
                }
                if (prev == 0 || next == 0) {
                    var isEmpty = {
                        microTask: counts['microTask'] > 0,
                        macroTask: counts['macroTask'] > 0,
                        eventTask: counts['eventTask'] > 0,
                        change: type
                    };
                    this.hasTask(this.zone, isEmpty);
                }
            };
            return ZoneDelegate;
        }());
        var ZoneTask = /** @class */ (function () {
            function ZoneTask(type, source, callback, options, scheduleFn, cancelFn) {
                // tslint:disable-next-line:require-internal-with-underscore
                this._zone = null;
                this.runCount = 0;
                // tslint:disable-next-line:require-internal-with-underscore
                this._zoneDelegates = null;
                // tslint:disable-next-line:require-internal-with-underscore
                this._state = 'notScheduled';
                this.type = type;
                this.source = source;
                this.data = options;
                this.scheduleFn = scheduleFn;
                this.cancelFn = cancelFn;
                if (!callback) {
                    throw new Error('callback is not defined');
                }
                this.callback = callback;
                var self = this;
                // TODO: @JiaLiPassion options should have interface
                if (type === eventTask && options && options.useG) {
                    this.invoke = ZoneTask.invokeTask;
                }
                else {
                    this.invoke = function () {
                        return ZoneTask.invokeTask.call(global, self, this, arguments);
                    };
                }
            }
            ZoneTask.invokeTask = function (task, target, args) {
                if (!task) {
                    task = this;
                }
                _numberOfNestedTaskFrames++;
                try {
                    task.runCount++;
                    return task.zone.runTask(task, target, args);
                }
                finally {
                    if (_numberOfNestedTaskFrames == 1) {
                        drainMicroTaskQueue();
                    }
                    _numberOfNestedTaskFrames--;
                }
            };
            Object.defineProperty(ZoneTask.prototype, "zone", {
                get: function () {
                    return this._zone;
                },
                enumerable: false,
                configurable: true
            });
            Object.defineProperty(ZoneTask.prototype, "state", {
                get: function () {
                    return this._state;
                },
                enumerable: false,
                configurable: true
            });
            ZoneTask.prototype.cancelScheduleRequest = function () {
                this._transitionTo(notScheduled, scheduling);
            };
            // tslint:disable-next-line:require-internal-with-underscore
            ZoneTask.prototype._transitionTo = function (toState, fromState1, fromState2) {
                if (this._state === fromState1 || this._state === fromState2) {
                    this._state = toState;
                    if (toState == notScheduled) {
                        this._zoneDelegates = null;
                    }
                }
                else {
                    throw new Error(this.type + " '" + this.source + "': can not transition to '" + toState + "', expecting state '" + fromState1 + "'" + (fromState2 ? ' or \'' + fromState2 + '\'' : '') + ", was '" + this._state + "'.");
                }
            };
            ZoneTask.prototype.toString = function () {
                if (this.data && typeof this.data.handleId !== 'undefined') {
                    return this.data.handleId.toString();
                }
                else {
                    return Object.prototype.toString.call(this);
                }
            };
            // add toJSON method to prevent cyclic error when
            // call JSON.stringify(zoneTask)
            ZoneTask.prototype.toJSON = function () {
                return {
                    type: this.type,
                    state: this.state,
                    source: this.source,
                    zone: this.zone.name,
                    runCount: this.runCount
                };
            };
            return ZoneTask;
        }());
        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        ///  MICROTASK QUEUE
        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        var symbolSetTimeout = __symbol__('setTimeout');
        var symbolPromise = __symbol__('Promise');
        var symbolThen = __symbol__('then');
        var _microTaskQueue = [];
        var _isDrainingMicrotaskQueue = false;
        var nativeMicroTaskQueuePromise;
        function scheduleMicroTask(task) {
            // if we are not running in any task, and there has not been anything scheduled
            // we must bootstrap the initial task creation by manually scheduling the drain
            if (_numberOfNestedTaskFrames === 0 && _microTaskQueue.length === 0) {
                // We are not running in Task, so we need to kickstart the microtask queue.
                if (!nativeMicroTaskQueuePromise) {
                    if (global[symbolPromise]) {
                        nativeMicroTaskQueuePromise = global[symbolPromise].resolve(0);
                    }
                }
                if (nativeMicroTaskQueuePromise) {
                    var nativeThen = nativeMicroTaskQueuePromise[symbolThen];
                    if (!nativeThen) {
                        // native Promise is not patchable, we need to use `then` directly
                        // issue 1078
                        nativeThen = nativeMicroTaskQueuePromise['then'];
                    }
                    nativeThen.call(nativeMicroTaskQueuePromise, drainMicroTaskQueue);
                }
                else {
                    global[symbolSetTimeout](drainMicroTaskQueue, 0);
                }
            }
            task && _microTaskQueue.push(task);
        }
        function drainMicroTaskQueue() {
            if (!_isDrainingMicrotaskQueue) {
                _isDrainingMicrotaskQueue = true;
                while (_microTaskQueue.length) {
                    var queue = _microTaskQueue;
                    _microTaskQueue = [];
                    for (var i = 0; i < queue.length; i++) {
                        var task = queue[i];
                        try {
                            task.zone.runTask(task, null, null);
                        }
                        catch (error) {
                            _api.onUnhandledError(error);
                        }
                    }
                }
                _api.microtaskDrainDone();
                _isDrainingMicrotaskQueue = false;
            }
        }
        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        ///  BOOTSTRAP
        //////////////////////////////////////////////////////
        //////////////////////////////////////////////////////
        var NO_ZONE = { name: 'NO ZONE' };
        var notScheduled = 'notScheduled', scheduling = 'scheduling', scheduled = 'scheduled', running = 'running', canceling = 'canceling', unknown = 'unknown';
        var microTask = 'microTask', macroTask = 'macroTask', eventTask = 'eventTask';
        var patches = {};
        var _api = {
            symbol: __symbol__,
            currentZoneFrame: function () { return _currentZoneFrame; },
            onUnhandledError: noop,
            microtaskDrainDone: noop,
            scheduleMicroTask: scheduleMicroTask,
            showUncaughtError: function () { return !Zone[__symbol__('ignoreConsoleErrorUncaughtError')]; },
            patchEventTarget: function () { return []; },
            patchOnProperties: noop,
            patchMethod: function () { return noop; },
            bindArguments: function () { return []; },
            patchThen: function () { return noop; },
            patchMacroTask: function () { return noop; },
            patchEventPrototype: function () { return noop; },
            isIEOrEdge: function () { return false; },
            getGlobalObjects: function () { return undefined; },
            ObjectDefineProperty: function () { return noop; },
            ObjectGetOwnPropertyDescriptor: function () { return undefined; },
            ObjectCreate: function () { return undefined; },
            ArraySlice: function () { return []; },
            patchClass: function () { return noop; },
            wrapWithCurrentZone: function () { return noop; },
            filterProperties: function () { return []; },
            attachOriginToPatched: function () { return noop; },
            _redefineProperty: function () { return noop; },
            patchCallbacks: function () { return noop; }
        };
        var _currentZoneFrame = { parent: null, zone: new Zone(null, null) };
        var _currentTask = null;
        var _numberOfNestedTaskFrames = 0;
        function noop() { }
        performanceMeasure('Zone', 'Zone');
        return global['Zone'] = Zone;
    })(typeof window !== 'undefined' && window || typeof self !== 'undefined' && self || global);
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    /**
     * Suppress closure compiler errors about unknown 'Zone' variable
     * @fileoverview
     * @suppress {undefinedVars,globalThis,missingRequire}
     */
    /// <reference types="node"/>
    // issue #989, to reduce bundle size, use short name
    /** Object.getOwnPropertyDescriptor */
    var ObjectGetOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
    /** Object.defineProperty */
    var ObjectDefineProperty = Object.defineProperty;
    /** Object.getPrototypeOf */
    var ObjectGetPrototypeOf = Object.getPrototypeOf;
    /** Array.prototype.slice */
    var ArraySlice = Array.prototype.slice;
    /** addEventListener string const */
    var ADD_EVENT_LISTENER_STR = 'addEventListener';
    /** removeEventListener string const */
    var REMOVE_EVENT_LISTENER_STR = 'removeEventListener';
    /** zoneSymbol addEventListener */
    var ZONE_SYMBOL_ADD_EVENT_LISTENER = Zone.__symbol__(ADD_EVENT_LISTENER_STR);
    /** zoneSymbol removeEventListener */
    var ZONE_SYMBOL_REMOVE_EVENT_LISTENER = Zone.__symbol__(REMOVE_EVENT_LISTENER_STR);
    /** true string const */
    var TRUE_STR = 'true';
    /** false string const */
    var FALSE_STR = 'false';
    /** Zone symbol prefix string const. */
    var ZONE_SYMBOL_PREFIX = Zone.__symbol__('');
    function wrapWithCurrentZone(callback, source) {
        return Zone.current.wrap(callback, source);
    }
    function scheduleMacroTaskWithCurrentZone(source, callback, data, customSchedule, customCancel) {
        return Zone.current.scheduleMacroTask(source, callback, data, customSchedule, customCancel);
    }
    var zoneSymbol = Zone.__symbol__;
    var isWindowExists = typeof window !== 'undefined';
    var internalWindow = isWindowExists ? window : undefined;
    var _global = isWindowExists && internalWindow || typeof self === 'object' && self || global;
    var REMOVE_ATTRIBUTE = 'removeAttribute';
    var NULL_ON_PROP_VALUE = [null];
    function bindArguments(args, source) {
        for (var i = args.length - 1; i >= 0; i--) {
            if (typeof args[i] === 'function') {
                args[i] = wrapWithCurrentZone(args[i], source + '_' + i);
            }
        }
        return args;
    }
    function isPropertyWritable(propertyDesc) {
        if (!propertyDesc) {
            return true;
        }
        if (propertyDesc.writable === false) {
            return false;
        }
        return !(typeof propertyDesc.get === 'function' && typeof propertyDesc.set === 'undefined');
    }
    var isWebWorker = (typeof WorkerGlobalScope !== 'undefined' && self instanceof WorkerGlobalScope);
    // Make sure to access `process` through `_global` so that WebPack does not accidentally browserify
    // this code.
    var isNode = (!('nw' in _global) && typeof _global.process !== 'undefined' &&
        {}.toString.call(_global.process) === '[object process]');
    var isBrowser = !isNode && !isWebWorker && !!(isWindowExists && internalWindow['HTMLElement']);
    // we are in electron of nw, so we are both browser and nodejs
    // Make sure to access `process` through `_global` so that WebPack does not accidentally browserify
    // this code.
    var isMix = typeof _global.process !== 'undefined' &&
        {}.toString.call(_global.process) === '[object process]' && !isWebWorker &&
        !!(isWindowExists && internalWindow['HTMLElement']);
    var zoneSymbolEventNames = {};
    var wrapFn = function (event) {
        // https://github.com/angular/zone.js/issues/911, in IE, sometimes
        // event will be undefined, so we need to use window.event
        event = event || _global.event;
        if (!event) {
            return;
        }
        var eventNameSymbol = zoneSymbolEventNames[event.type];
        if (!eventNameSymbol) {
            eventNameSymbol = zoneSymbolEventNames[event.type] = zoneSymbol('ON_PROPERTY' + event.type);
        }
        var target = this || event.target || _global;
        var listener = target[eventNameSymbol];
        var result;
        if (isBrowser && target === internalWindow && event.type === 'error') {
            // window.onerror have different signiture
            // https://developer.mozilla.org/en-US/docs/Web/API/GlobalEventHandlers/onerror#window.onerror
            // and onerror callback will prevent default when callback return true
            var errorEvent = event;
            result = listener &&
                listener.call(this, errorEvent.message, errorEvent.filename, errorEvent.lineno, errorEvent.colno, errorEvent.error);
            if (result === true) {
                event.preventDefault();
            }
        }
        else {
            result = listener && listener.apply(this, arguments);
            if (result != undefined && !result) {
                event.preventDefault();
            }
        }
        return result;
    };
    function patchProperty(obj, prop, prototype) {
        var desc = ObjectGetOwnPropertyDescriptor(obj, prop);
        if (!desc && prototype) {
            // when patch window object, use prototype to check prop exist or not
            var prototypeDesc = ObjectGetOwnPropertyDescriptor(prototype, prop);
            if (prototypeDesc) {
                desc = { enumerable: true, configurable: true };
            }
        }
        // if the descriptor not exists or is not configurable
        // just return
        if (!desc || !desc.configurable) {
            return;
        }
        var onPropPatchedSymbol = zoneSymbol('on' + prop + 'patched');
        if (obj.hasOwnProperty(onPropPatchedSymbol) && obj[onPropPatchedSymbol]) {
            return;
        }
        // A property descriptor cannot have getter/setter and be writable
        // deleting the writable and value properties avoids this error:
        //
        // TypeError: property descriptors must not specify a value or be writable when a
        // getter or setter has been specified
        delete desc.writable;
        delete desc.value;
        var originalDescGet = desc.get;
        var originalDescSet = desc.set;
        // substr(2) cuz 'onclick' -> 'click', etc
        var eventName = prop.substr(2);
        var eventNameSymbol = zoneSymbolEventNames[eventName];
        if (!eventNameSymbol) {
            eventNameSymbol = zoneSymbolEventNames[eventName] = zoneSymbol('ON_PROPERTY' + eventName);
        }
        desc.set = function (newValue) {
            // in some of windows's onproperty callback, this is undefined
            // so we need to check it
            var target = this;
            if (!target && obj === _global) {
                target = _global;
            }
            if (!target) {
                return;
            }
            var previousValue = target[eventNameSymbol];
            if (previousValue) {
                target.removeEventListener(eventName, wrapFn);
            }
            // issue #978, when onload handler was added before loading zone.js
            // we should remove it with originalDescSet
            if (originalDescSet) {
                originalDescSet.apply(target, NULL_ON_PROP_VALUE);
            }
            if (typeof newValue === 'function') {
                target[eventNameSymbol] = newValue;
                target.addEventListener(eventName, wrapFn, false);
            }
            else {
                target[eventNameSymbol] = null;
            }
        };
        // The getter would return undefined for unassigned properties but the default value of an
        // unassigned property is null
        desc.get = function () {
            // in some of windows's onproperty callback, this is undefined
            // so we need to check it
            var target = this;
            if (!target && obj === _global) {
                target = _global;
            }
            if (!target) {
                return null;
            }
            var listener = target[eventNameSymbol];
            if (listener) {
                return listener;
            }
            else if (originalDescGet) {
                // result will be null when use inline event attribute,
                // such as <button onclick="func();">OK</button>
                // because the onclick function is internal raw uncompiled handler
                // the onclick will be evaluated when first time event was triggered or
                // the property is accessed, https://github.com/angular/zone.js/issues/525
                // so we should use original native get to retrieve the handler
                var value = originalDescGet && originalDescGet.call(this);
                if (value) {
                    desc.set.call(this, value);
                    if (typeof target[REMOVE_ATTRIBUTE] === 'function') {
                        target.removeAttribute(prop);
                    }
                    return value;
                }
            }
            return null;
        };
        ObjectDefineProperty(obj, prop, desc);
        obj[onPropPatchedSymbol] = true;
    }
    function patchOnProperties(obj, properties, prototype) {
        if (properties) {
            for (var i = 0; i < properties.length; i++) {
                patchProperty(obj, 'on' + properties[i], prototype);
            }
        }
        else {
            var onProperties = [];
            for (var prop in obj) {
                if (prop.substr(0, 2) == 'on') {
                    onProperties.push(prop);
                }
            }
            for (var j = 0; j < onProperties.length; j++) {
                patchProperty(obj, onProperties[j], prototype);
            }
        }
    }
    var originalInstanceKey = zoneSymbol('originalInstance');
    function copySymbolProperties(src, dest) {
        if (typeof Object.getOwnPropertySymbols !== 'function') {
            return;
        }
        var symbols = Object.getOwnPropertySymbols(src);
        symbols.forEach(function (symbol) {
            var desc = Object.getOwnPropertyDescriptor(src, symbol);
            Object.defineProperty(dest, symbol, {
                get: function () {
                    return src[symbol];
                },
                set: function (value) {
                    if (desc && (!desc.writable || typeof desc.set !== 'function')) {
                        // if src[symbol] is not writable or not have a setter, just return
                        return;
                    }
                    src[symbol] = value;
                },
                enumerable: desc ? desc.enumerable : true,
                configurable: desc ? desc.configurable : true
            });
        });
    }
    var shouldCopySymbolProperties = false;
    function setShouldCopySymbolProperties(flag) {
        shouldCopySymbolProperties = flag;
    }
    function patchMethod(target, name, patchFn) {
        var proto = target;
        while (proto && !proto.hasOwnProperty(name)) {
            proto = ObjectGetPrototypeOf(proto);
        }
        if (!proto && target[name]) {
            // somehow we did not find it, but we can see it. This happens on IE for Window properties.
            proto = target;
        }
        var delegateName = zoneSymbol(name);
        var delegate = null;
        if (proto && (!(delegate = proto[delegateName]) || !proto.hasOwnProperty(delegateName))) {
            delegate = proto[delegateName] = proto[name];
            // check whether proto[name] is writable
            // some property is readonly in safari, such as HtmlCanvasElement.prototype.toBlob
            var desc = proto && ObjectGetOwnPropertyDescriptor(proto, name);
            if (isPropertyWritable(desc)) {
                var patchDelegate_1 = patchFn(delegate, delegateName, name);
                proto[name] = function () {
                    return patchDelegate_1(this, arguments);
                };
                attachOriginToPatched(proto[name], delegate);
                if (shouldCopySymbolProperties) {
                    copySymbolProperties(delegate, proto[name]);
                }
            }
        }
        return delegate;
    }
    // TODO: @JiaLiPassion, support cancel task later if necessary
    function patchMacroTask(obj, funcName, metaCreator) {
        var setNative = null;
        function scheduleTask(task) {
            var data = task.data;
            data.args[data.cbIdx] = function () {
                task.invoke.apply(this, arguments);
            };
            setNative.apply(data.target, data.args);
            return task;
        }
        setNative = patchMethod(obj, funcName, function (delegate) { return function (self, args) {
            var meta = metaCreator(self, args);
            if (meta.cbIdx >= 0 && typeof args[meta.cbIdx] === 'function') {
                return scheduleMacroTaskWithCurrentZone(meta.name, args[meta.cbIdx], meta, scheduleTask);
            }
            else {
                // cause an error by calling it directly.
                return delegate.apply(self, args);
            }
        }; });
    }
    function patchMicroTask(obj, funcName, metaCreator) {
        var setNative = null;
        function scheduleTask(task) {
            var data = task.data;
            data.args[data.cbIdx] = function () {
                task.invoke.apply(this, arguments);
            };
            setNative.apply(data.target, data.args);
            return task;
        }
        setNative = patchMethod(obj, funcName, function (delegate) { return function (self, args) {
            var meta = metaCreator(self, args);
            if (meta.cbIdx >= 0 && typeof args[meta.cbIdx] === 'function') {
                return Zone.current.scheduleMicroTask(meta.name, args[meta.cbIdx], meta, scheduleTask);
            }
            else {
                // cause an error by calling it directly.
                return delegate.apply(self, args);
            }
        }; });
    }
    function attachOriginToPatched(patched, original) {
        patched[zoneSymbol('OriginalDelegate')] = original;
    }
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('ZoneAwarePromise', function (global, Zone, api) {
        var ObjectGetOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
        var ObjectDefineProperty = Object.defineProperty;
        function readableObjectToString(obj) {
            if (obj && obj.toString === Object.prototype.toString) {
                var className = obj.constructor && obj.constructor.name;
                return (className ? className : '') + ': ' + JSON.stringify(obj);
            }
            return obj ? obj.toString() : Object.prototype.toString.call(obj);
        }
        var __symbol__ = api.symbol;
        var _uncaughtPromiseErrors = [];
        var isDisableWrappingUncaughtPromiseRejection = global[__symbol__('DISABLE_WRAPPING_UNCAUGHT_PROMISE_REJECTION')] === true;
        var symbolPromise = __symbol__('Promise');
        var symbolThen = __symbol__('then');
        var creationTrace = '__creationTrace__';
        api.onUnhandledError = function (e) {
            if (api.showUncaughtError()) {
                var rejection = e && e.rejection;
                if (rejection) {
                    console.error('Unhandled Promise rejection:', rejection instanceof Error ? rejection.message : rejection, '; Zone:', e.zone.name, '; Task:', e.task && e.task.source, '; Value:', rejection, rejection instanceof Error ? rejection.stack : undefined);
                }
                else {
                    console.error(e);
                }
            }
        };
        api.microtaskDrainDone = function () {
            var _loop_1 = function () {
                var uncaughtPromiseError = _uncaughtPromiseErrors.shift();
                try {
                    uncaughtPromiseError.zone.runGuarded(function () {
                        if (uncaughtPromiseError.throwOriginal) {
                            throw uncaughtPromiseError.rejection;
                        }
                        throw uncaughtPromiseError;
                    });
                }
                catch (error) {
                    handleUnhandledRejection(error);
                }
            };
            while (_uncaughtPromiseErrors.length) {
                _loop_1();
            }
        };
        var UNHANDLED_PROMISE_REJECTION_HANDLER_SYMBOL = __symbol__('unhandledPromiseRejectionHandler');
        function handleUnhandledRejection(e) {
            api.onUnhandledError(e);
            try {
                var handler = Zone[UNHANDLED_PROMISE_REJECTION_HANDLER_SYMBOL];
                if (typeof handler === 'function') {
                    handler.call(this, e);
                }
            }
            catch (err) {
            }
        }
        function isThenable(value) {
            return value && value.then;
        }
        function forwardResolution(value) {
            return value;
        }
        function forwardRejection(rejection) {
            return ZoneAwarePromise.reject(rejection);
        }
        var symbolState = __symbol__('state');
        var symbolValue = __symbol__('value');
        var symbolFinally = __symbol__('finally');
        var symbolParentPromiseValue = __symbol__('parentPromiseValue');
        var symbolParentPromiseState = __symbol__('parentPromiseState');
        var source = 'Promise.then';
        var UNRESOLVED = null;
        var RESOLVED = true;
        var REJECTED = false;
        var REJECTED_NO_CATCH = 0;
        function makeResolver(promise, state) {
            return function (v) {
                try {
                    resolvePromise(promise, state, v);
                }
                catch (err) {
                    resolvePromise(promise, false, err);
                }
                // Do not return value or you will break the Promise spec.
            };
        }
        var once = function () {
            var wasCalled = false;
            return function wrapper(wrappedFunction) {
                return function () {
                    if (wasCalled) {
                        return;
                    }
                    wasCalled = true;
                    wrappedFunction.apply(null, arguments);
                };
            };
        };
        var TYPE_ERROR = 'Promise resolved with itself';
        var CURRENT_TASK_TRACE_SYMBOL = __symbol__('currentTaskTrace');
        // Promise Resolution
        function resolvePromise(promise, state, value) {
            var onceWrapper = once();
            if (promise === value) {
                throw new TypeError(TYPE_ERROR);
            }
            if (promise[symbolState] === UNRESOLVED) {
                // should only get value.then once based on promise spec.
                var then = null;
                try {
                    if (typeof value === 'object' || typeof value === 'function') {
                        then = value && value.then;
                    }
                }
                catch (err) {
                    onceWrapper(function () {
                        resolvePromise(promise, false, err);
                    })();
                    return promise;
                }
                // if (value instanceof ZoneAwarePromise) {
                if (state !== REJECTED && value instanceof ZoneAwarePromise &&
                    value.hasOwnProperty(symbolState) && value.hasOwnProperty(symbolValue) &&
                    value[symbolState] !== UNRESOLVED) {
                    clearRejectedNoCatch(value);
                    resolvePromise(promise, value[symbolState], value[symbolValue]);
                }
                else if (state !== REJECTED && typeof then === 'function') {
                    try {
                        then.call(value, onceWrapper(makeResolver(promise, state)), onceWrapper(makeResolver(promise, false)));
                    }
                    catch (err) {
                        onceWrapper(function () {
                            resolvePromise(promise, false, err);
                        })();
                    }
                }
                else {
                    promise[symbolState] = state;
                    var queue = promise[symbolValue];
                    promise[symbolValue] = value;
                    if (promise[symbolFinally] === symbolFinally) {
                        // the promise is generated by Promise.prototype.finally
                        if (state === RESOLVED) {
                            // the state is resolved, should ignore the value
                            // and use parent promise value
                            promise[symbolState] = promise[symbolParentPromiseState];
                            promise[symbolValue] = promise[symbolParentPromiseValue];
                        }
                    }
                    // record task information in value when error occurs, so we can
                    // do some additional work such as render longStackTrace
                    if (state === REJECTED && value instanceof Error) {
                        // check if longStackTraceZone is here
                        var trace = Zone.currentTask && Zone.currentTask.data &&
                            Zone.currentTask.data[creationTrace];
                        if (trace) {
                            // only keep the long stack trace into error when in longStackTraceZone
                            ObjectDefineProperty(value, CURRENT_TASK_TRACE_SYMBOL, { configurable: true, enumerable: false, writable: true, value: trace });
                        }
                    }
                    for (var i = 0; i < queue.length;) {
                        scheduleResolveOrReject(promise, queue[i++], queue[i++], queue[i++], queue[i++]);
                    }
                    if (queue.length == 0 && state == REJECTED) {
                        promise[symbolState] = REJECTED_NO_CATCH;
                        var uncaughtPromiseError = value;
                        try {
                            // Here we throws a new Error to print more readable error log
                            // and if the value is not an error, zone.js builds an `Error`
                            // Object here to attach the stack information.
                            throw new Error('Uncaught (in promise): ' + readableObjectToString(value) +
                                (value && value.stack ? '\n' + value.stack : ''));
                        }
                        catch (err) {
                            uncaughtPromiseError = err;
                        }
                        if (isDisableWrappingUncaughtPromiseRejection) {
                            // If disable wrapping uncaught promise reject
                            // use the value instead of wrapping it.
                            uncaughtPromiseError.throwOriginal = true;
                        }
                        uncaughtPromiseError.rejection = value;
                        uncaughtPromiseError.promise = promise;
                        uncaughtPromiseError.zone = Zone.current;
                        uncaughtPromiseError.task = Zone.currentTask;
                        _uncaughtPromiseErrors.push(uncaughtPromiseError);
                        api.scheduleMicroTask(); // to make sure that it is running
                    }
                }
            }
            // Resolving an already resolved promise is a noop.
            return promise;
        }
        var REJECTION_HANDLED_HANDLER = __symbol__('rejectionHandledHandler');
        function clearRejectedNoCatch(promise) {
            if (promise[symbolState] === REJECTED_NO_CATCH) {
                // if the promise is rejected no catch status
                // and queue.length > 0, means there is a error handler
                // here to handle the rejected promise, we should trigger
                // windows.rejectionhandled eventHandler or nodejs rejectionHandled
                // eventHandler
                try {
                    var handler = Zone[REJECTION_HANDLED_HANDLER];
                    if (handler && typeof handler === 'function') {
                        handler.call(this, { rejection: promise[symbolValue], promise: promise });
                    }
                }
                catch (err) {
                }
                promise[symbolState] = REJECTED;
                for (var i = 0; i < _uncaughtPromiseErrors.length; i++) {
                    if (promise === _uncaughtPromiseErrors[i].promise) {
                        _uncaughtPromiseErrors.splice(i, 1);
                    }
                }
            }
        }
        function scheduleResolveOrReject(promise, zone, chainPromise, onFulfilled, onRejected) {
            clearRejectedNoCatch(promise);
            var promiseState = promise[symbolState];
            var delegate = promiseState ?
                (typeof onFulfilled === 'function') ? onFulfilled : forwardResolution :
                (typeof onRejected === 'function') ? onRejected : forwardRejection;
            zone.scheduleMicroTask(source, function () {
                try {
                    var parentPromiseValue = promise[symbolValue];
                    var isFinallyPromise = !!chainPromise && symbolFinally === chainPromise[symbolFinally];
                    if (isFinallyPromise) {
                        // if the promise is generated from finally call, keep parent promise's state and value
                        chainPromise[symbolParentPromiseValue] = parentPromiseValue;
                        chainPromise[symbolParentPromiseState] = promiseState;
                    }
                    // should not pass value to finally callback
                    var value = zone.run(delegate, undefined, isFinallyPromise && delegate !== forwardRejection && delegate !== forwardResolution ?
                        [] :
                        [parentPromiseValue]);
                    resolvePromise(chainPromise, true, value);
                }
                catch (error) {
                    // if error occurs, should always return this error
                    resolvePromise(chainPromise, false, error);
                }
            }, chainPromise);
        }
        var ZONE_AWARE_PROMISE_TO_STRING = 'function ZoneAwarePromise() { [native code] }';
        var noop = function () { };
        var ZoneAwarePromise = /** @class */ (function () {
            function ZoneAwarePromise(executor) {
                var promise = this;
                if (!(promise instanceof ZoneAwarePromise)) {
                    throw new Error('Must be an instanceof Promise.');
                }
                promise[symbolState] = UNRESOLVED;
                promise[symbolValue] = []; // queue;
                try {
                    executor && executor(makeResolver(promise, RESOLVED), makeResolver(promise, REJECTED));
                }
                catch (error) {
                    resolvePromise(promise, false, error);
                }
            }
            ZoneAwarePromise.toString = function () {
                return ZONE_AWARE_PROMISE_TO_STRING;
            };
            ZoneAwarePromise.resolve = function (value) {
                return resolvePromise(new this(null), RESOLVED, value);
            };
            ZoneAwarePromise.reject = function (error) {
                return resolvePromise(new this(null), REJECTED, error);
            };
            ZoneAwarePromise.race = function (values) {
                var resolve;
                var reject;
                var promise = new this(function (res, rej) {
                    resolve = res;
                    reject = rej;
                });
                function onResolve(value) {
                    resolve(value);
                }
                function onReject(error) {
                    reject(error);
                }
                for (var _i = 0, values_1 = values; _i < values_1.length; _i++) {
                    var value = values_1[_i];
                    if (!isThenable(value)) {
                        value = this.resolve(value);
                    }
                    value.then(onResolve, onReject);
                }
                return promise;
            };
            ZoneAwarePromise.all = function (values) {
                return ZoneAwarePromise.allWithCallback(values);
            };
            ZoneAwarePromise.allSettled = function (values) {
                var P = this && this.prototype instanceof ZoneAwarePromise ? this : ZoneAwarePromise;
                return P.allWithCallback(values, {
                    thenCallback: function (value) { return ({ status: 'fulfilled', value: value }); },
                    errorCallback: function (err) { return ({ status: 'rejected', reason: err }); }
                });
            };
            ZoneAwarePromise.allWithCallback = function (values, callback) {
                var resolve;
                var reject;
                var promise = new this(function (res, rej) {
                    resolve = res;
                    reject = rej;
                });
                // Start at 2 to prevent prematurely resolving if .then is called immediately.
                var unresolvedCount = 2;
                var valueIndex = 0;
                var resolvedValues = [];
                var _loop_2 = function (value) {
                    if (!isThenable(value)) {
                        value = this_1.resolve(value);
                    }
                    var curValueIndex = valueIndex;
                    try {
                        value.then(function (value) {
                            resolvedValues[curValueIndex] = callback ? callback.thenCallback(value) : value;
                            unresolvedCount--;
                            if (unresolvedCount === 0) {
                                resolve(resolvedValues);
                            }
                        }, function (err) {
                            if (!callback) {
                                reject(err);
                            }
                            else {
                                resolvedValues[curValueIndex] = callback.errorCallback(err);
                                unresolvedCount--;
                                if (unresolvedCount === 0) {
                                    resolve(resolvedValues);
                                }
                            }
                        });
                    }
                    catch (thenErr) {
                        reject(thenErr);
                    }
                    unresolvedCount++;
                    valueIndex++;
                };
                var this_1 = this;
                for (var _i = 0, values_2 = values; _i < values_2.length; _i++) {
                    var value = values_2[_i];
                    _loop_2(value);
                }
                // Make the unresolvedCount zero-based again.
                unresolvedCount -= 2;
                if (unresolvedCount === 0) {
                    resolve(resolvedValues);
                }
                return promise;
            };
            Object.defineProperty(ZoneAwarePromise.prototype, Symbol.toStringTag, {
                get: function () {
                    return 'Promise';
                },
                enumerable: false,
                configurable: true
            });
            Object.defineProperty(ZoneAwarePromise.prototype, Symbol.species, {
                get: function () {
                    return ZoneAwarePromise;
                },
                enumerable: false,
                configurable: true
            });
            ZoneAwarePromise.prototype.then = function (onFulfilled, onRejected) {
                var C = this.constructor[Symbol.species];
                if (!C || typeof C !== 'function') {
                    C = this.constructor || ZoneAwarePromise;
                }
                var chainPromise = new C(noop);
                var zone = Zone.current;
                if (this[symbolState] == UNRESOLVED) {
                    this[symbolValue].push(zone, chainPromise, onFulfilled, onRejected);
                }
                else {
                    scheduleResolveOrReject(this, zone, chainPromise, onFulfilled, onRejected);
                }
                return chainPromise;
            };
            ZoneAwarePromise.prototype.catch = function (onRejected) {
                return this.then(null, onRejected);
            };
            ZoneAwarePromise.prototype.finally = function (onFinally) {
                var C = this.constructor[Symbol.species];
                if (!C || typeof C !== 'function') {
                    C = ZoneAwarePromise;
                }
                var chainPromise = new C(noop);
                chainPromise[symbolFinally] = symbolFinally;
                var zone = Zone.current;
                if (this[symbolState] == UNRESOLVED) {
                    this[symbolValue].push(zone, chainPromise, onFinally, onFinally);
                }
                else {
                    scheduleResolveOrReject(this, zone, chainPromise, onFinally, onFinally);
                }
                return chainPromise;
            };
            return ZoneAwarePromise;
        }());
        // Protect against aggressive optimizers dropping seemingly unused properties.
        // E.g. Closure Compiler in advanced mode.
        ZoneAwarePromise['resolve'] = ZoneAwarePromise.resolve;
        ZoneAwarePromise['reject'] = ZoneAwarePromise.reject;
        ZoneAwarePromise['race'] = ZoneAwarePromise.race;
        ZoneAwarePromise['all'] = ZoneAwarePromise.all;
        var NativePromise = global[symbolPromise] = global['Promise'];
        global['Promise'] = ZoneAwarePromise;
        var symbolThenPatched = __symbol__('thenPatched');
        function patchThen(Ctor) {
            var proto = Ctor.prototype;
            var prop = ObjectGetOwnPropertyDescriptor(proto, 'then');
            if (prop && (prop.writable === false || !prop.configurable)) {
                // check Ctor.prototype.then propertyDescriptor is writable or not
                // in meteor env, writable is false, we should ignore such case
                return;
            }
            var originalThen = proto.then;
            // Keep a reference to the original method.
            proto[symbolThen] = originalThen;
            Ctor.prototype.then = function (onResolve, onReject) {
                var _this = this;
                var wrapped = new ZoneAwarePromise(function (resolve, reject) {
                    originalThen.call(_this, resolve, reject);
                });
                return wrapped.then(onResolve, onReject);
            };
            Ctor[symbolThenPatched] = true;
        }
        api.patchThen = patchThen;
        function zoneify(fn) {
            return function (self, args) {
                var resultPromise = fn.apply(self, args);
                if (resultPromise instanceof ZoneAwarePromise) {
                    return resultPromise;
                }
                var ctor = resultPromise.constructor;
                if (!ctor[symbolThenPatched]) {
                    patchThen(ctor);
                }
                return resultPromise;
            };
        }
        if (NativePromise) {
            patchThen(NativePromise);
            patchMethod(global, 'fetch', function (delegate) { return zoneify(delegate); });
        }
        // This is not part of public API, but it is useful for tests, so we expose it.
        Promise[Zone.__symbol__('uncaughtPromiseErrors')] = _uncaughtPromiseErrors;
        return ZoneAwarePromise;
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    // override Function.prototype.toString to make zone.js patched function
    // look like native function
    Zone.__load_patch('toString', function (global) {
        // patch Func.prototype.toString to let them look like native
        var originalFunctionToString = Function.prototype.toString;
        var ORIGINAL_DELEGATE_SYMBOL = zoneSymbol('OriginalDelegate');
        var PROMISE_SYMBOL = zoneSymbol('Promise');
        var ERROR_SYMBOL = zoneSymbol('Error');
        var newFunctionToString = function toString() {
            if (typeof this === 'function') {
                var originalDelegate = this[ORIGINAL_DELEGATE_SYMBOL];
                if (originalDelegate) {
                    if (typeof originalDelegate === 'function') {
                        return originalFunctionToString.call(originalDelegate);
                    }
                    else {
                        return Object.prototype.toString.call(originalDelegate);
                    }
                }
                if (this === Promise) {
                    var nativePromise = global[PROMISE_SYMBOL];
                    if (nativePromise) {
                        return originalFunctionToString.call(nativePromise);
                    }
                }
                if (this === Error) {
                    var nativeError = global[ERROR_SYMBOL];
                    if (nativeError) {
                        return originalFunctionToString.call(nativeError);
                    }
                }
            }
            return originalFunctionToString.call(this);
        };
        newFunctionToString[ORIGINAL_DELEGATE_SYMBOL] = originalFunctionToString;
        Function.prototype.toString = newFunctionToString;
        // patch Object.prototype.toString to let them look like native
        var originalObjectToString = Object.prototype.toString;
        var PROMISE_OBJECT_TO_STRING = '[object Promise]';
        Object.prototype.toString = function () {
            if (typeof Promise === 'function' && this instanceof Promise) {
                return PROMISE_OBJECT_TO_STRING;
            }
            return originalObjectToString.call(this);
        };
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('node_util', function (global, Zone, api) {
        api.patchOnProperties = patchOnProperties;
        api.patchMethod = patchMethod;
        api.bindArguments = bindArguments;
        api.patchMacroTask = patchMacroTask;
        setShouldCopySymbolProperties(true);
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var passiveSupported = false;
    if (typeof window !== 'undefined') {
        try {
            var options = Object.defineProperty({}, 'passive', {
                get: function () {
                    passiveSupported = true;
                }
            });
            window.addEventListener('test', options, options);
            window.removeEventListener('test', options, options);
        }
        catch (err) {
            passiveSupported = false;
        }
    }
    // an identifier to tell ZoneTask do not create a new invoke closure
    var OPTIMIZED_ZONE_EVENT_TASK_DATA = {
        useG: true
    };
    var zoneSymbolEventNames$1 = {};
    var globalSources = {};
    var EVENT_NAME_SYMBOL_REGX = new RegExp('^' + ZONE_SYMBOL_PREFIX + '(\\w+)(true|false)$');
    var IMMEDIATE_PROPAGATION_SYMBOL = zoneSymbol('propagationStopped');
    function prepareEventNames(eventName, eventNameToString) {
        var falseEventName = (eventNameToString ? eventNameToString(eventName) : eventName) + FALSE_STR;
        var trueEventName = (eventNameToString ? eventNameToString(eventName) : eventName) + TRUE_STR;
        var symbol = ZONE_SYMBOL_PREFIX + falseEventName;
        var symbolCapture = ZONE_SYMBOL_PREFIX + trueEventName;
        zoneSymbolEventNames$1[eventName] = {};
        zoneSymbolEventNames$1[eventName][FALSE_STR] = symbol;
        zoneSymbolEventNames$1[eventName][TRUE_STR] = symbolCapture;
    }
    function patchEventTarget(_global, apis, patchOptions) {
        var ADD_EVENT_LISTENER = (patchOptions && patchOptions.add) || ADD_EVENT_LISTENER_STR;
        var REMOVE_EVENT_LISTENER = (patchOptions && patchOptions.rm) || REMOVE_EVENT_LISTENER_STR;
        var LISTENERS_EVENT_LISTENER = (patchOptions && patchOptions.listeners) || 'eventListeners';
        var REMOVE_ALL_LISTENERS_EVENT_LISTENER = (patchOptions && patchOptions.rmAll) || 'removeAllListeners';
        var zoneSymbolAddEventListener = zoneSymbol(ADD_EVENT_LISTENER);
        var ADD_EVENT_LISTENER_SOURCE = '.' + ADD_EVENT_LISTENER + ':';
        var PREPEND_EVENT_LISTENER = 'prependListener';
        var PREPEND_EVENT_LISTENER_SOURCE = '.' + PREPEND_EVENT_LISTENER + ':';
        var invokeTask = function (task, target, event) {
            // for better performance, check isRemoved which is set
            // by removeEventListener
            if (task.isRemoved) {
                return;
            }
            var delegate = task.callback;
            if (typeof delegate === 'object' && delegate.handleEvent) {
                // create the bind version of handleEvent when invoke
                task.callback = function (event) { return delegate.handleEvent(event); };
                task.originalDelegate = delegate;
            }
            // invoke static task.invoke
            task.invoke(task, target, [event]);
            var options = task.options;
            if (options && typeof options === 'object' && options.once) {
                // if options.once is true, after invoke once remove listener here
                // only browser need to do this, nodejs eventEmitter will cal removeListener
                // inside EventEmitter.once
                var delegate_1 = task.originalDelegate ? task.originalDelegate : task.callback;
                target[REMOVE_EVENT_LISTENER].call(target, event.type, delegate_1, options);
            }
        };
        // global shared zoneAwareCallback to handle all event callback with capture = false
        var globalZoneAwareCallback = function (event) {
            // https://github.com/angular/zone.js/issues/911, in IE, sometimes
            // event will be undefined, so we need to use window.event
            event = event || _global.event;
            if (!event) {
                return;
            }
            // event.target is needed for Samsung TV and SourceBuffer
            // || global is needed https://github.com/angular/zone.js/issues/190
            var target = this || event.target || _global;
            var tasks = target[zoneSymbolEventNames$1[event.type][FALSE_STR]];
            if (tasks) {
                // invoke all tasks which attached to current target with given event.type and capture = false
                // for performance concern, if task.length === 1, just invoke
                if (tasks.length === 1) {
                    invokeTask(tasks[0], target, event);
                }
                else {
                    // https://github.com/angular/zone.js/issues/836
                    // copy the tasks array before invoke, to avoid
                    // the callback will remove itself or other listener
                    var copyTasks = tasks.slice();
                    for (var i = 0; i < copyTasks.length; i++) {
                        if (event && event[IMMEDIATE_PROPAGATION_SYMBOL] === true) {
                            break;
                        }
                        invokeTask(copyTasks[i], target, event);
                    }
                }
            }
        };
        // global shared zoneAwareCallback to handle all event callback with capture = true
        var globalZoneAwareCaptureCallback = function (event) {
            // https://github.com/angular/zone.js/issues/911, in IE, sometimes
            // event will be undefined, so we need to use window.event
            event = event || _global.event;
            if (!event) {
                return;
            }
            // event.target is needed for Samsung TV and SourceBuffer
            // || global is needed https://github.com/angular/zone.js/issues/190
            var target = this || event.target || _global;
            var tasks = target[zoneSymbolEventNames$1[event.type][TRUE_STR]];
            if (tasks) {
                // invoke all tasks which attached to current target with given event.type and capture = false
                // for performance concern, if task.length === 1, just invoke
                if (tasks.length === 1) {
                    invokeTask(tasks[0], target, event);
                }
                else {
                    // https://github.com/angular/zone.js/issues/836
                    // copy the tasks array before invoke, to avoid
                    // the callback will remove itself or other listener
                    var copyTasks = tasks.slice();
                    for (var i = 0; i < copyTasks.length; i++) {
                        if (event && event[IMMEDIATE_PROPAGATION_SYMBOL] === true) {
                            break;
                        }
                        invokeTask(copyTasks[i], target, event);
                    }
                }
            }
        };
        function patchEventTargetMethods(obj, patchOptions) {
            if (!obj) {
                return false;
            }
            var useGlobalCallback = true;
            if (patchOptions && patchOptions.useG !== undefined) {
                useGlobalCallback = patchOptions.useG;
            }
            var validateHandler = patchOptions && patchOptions.vh;
            var checkDuplicate = true;
            if (patchOptions && patchOptions.chkDup !== undefined) {
                checkDuplicate = patchOptions.chkDup;
            }
            var returnTarget = false;
            if (patchOptions && patchOptions.rt !== undefined) {
                returnTarget = patchOptions.rt;
            }
            var proto = obj;
            while (proto && !proto.hasOwnProperty(ADD_EVENT_LISTENER)) {
                proto = ObjectGetPrototypeOf(proto);
            }
            if (!proto && obj[ADD_EVENT_LISTENER]) {
                // somehow we did not find it, but we can see it. This happens on IE for Window properties.
                proto = obj;
            }
            if (!proto) {
                return false;
            }
            if (proto[zoneSymbolAddEventListener]) {
                return false;
            }
            var eventNameToString = patchOptions && patchOptions.eventNameToString;
            // a shared global taskData to pass data for scheduleEventTask
            // so we do not need to create a new object just for pass some data
            var taskData = {};
            var nativeAddEventListener = proto[zoneSymbolAddEventListener] = proto[ADD_EVENT_LISTENER];
            var nativeRemoveEventListener = proto[zoneSymbol(REMOVE_EVENT_LISTENER)] =
                proto[REMOVE_EVENT_LISTENER];
            var nativeListeners = proto[zoneSymbol(LISTENERS_EVENT_LISTENER)] =
                proto[LISTENERS_EVENT_LISTENER];
            var nativeRemoveAllListeners = proto[zoneSymbol(REMOVE_ALL_LISTENERS_EVENT_LISTENER)] =
                proto[REMOVE_ALL_LISTENERS_EVENT_LISTENER];
            var nativePrependEventListener;
            if (patchOptions && patchOptions.prepend) {
                nativePrependEventListener = proto[zoneSymbol(patchOptions.prepend)] =
                    proto[patchOptions.prepend];
            }
            /**
             * This util function will build an option object with passive option
             * to handle all possible input from the user.
             */
            function buildEventListenerOptions(options, passive) {
                if (!passiveSupported && typeof options === 'object' && options) {
                    // doesn't support passive but user want to pass an object as options.
                    // this will not work on some old browser, so we just pass a boolean
                    // as useCapture parameter
                    return !!options.capture;
                }
                if (!passiveSupported || !passive) {
                    return options;
                }
                if (typeof options === 'boolean') {
                    return { capture: options, passive: true };
                }
                if (!options) {
                    return { passive: true };
                }
                if (typeof options === 'object' && options.passive !== false) {
                    return Object.assign(Object.assign({}, options), { passive: true });
                }
                return options;
            }
            var customScheduleGlobal = function (task) {
                // if there is already a task for the eventName + capture,
                // just return, because we use the shared globalZoneAwareCallback here.
                if (taskData.isExisting) {
                    return;
                }
                return nativeAddEventListener.call(taskData.target, taskData.eventName, taskData.capture ? globalZoneAwareCaptureCallback : globalZoneAwareCallback, taskData.options);
            };
            var customCancelGlobal = function (task) {
                // if task is not marked as isRemoved, this call is directly
                // from Zone.prototype.cancelTask, we should remove the task
                // from tasksList of target first
                if (!task.isRemoved) {
                    var symbolEventNames = zoneSymbolEventNames$1[task.eventName];
                    var symbolEventName = void 0;
                    if (symbolEventNames) {
                        symbolEventName = symbolEventNames[task.capture ? TRUE_STR : FALSE_STR];
                    }
                    var existingTasks = symbolEventName && task.target[symbolEventName];
                    if (existingTasks) {
                        for (var i = 0; i < existingTasks.length; i++) {
                            var existingTask = existingTasks[i];
                            if (existingTask === task) {
                                existingTasks.splice(i, 1);
                                // set isRemoved to data for faster invokeTask check
                                task.isRemoved = true;
                                if (existingTasks.length === 0) {
                                    // all tasks for the eventName + capture have gone,
                                    // remove globalZoneAwareCallback and remove the task cache from target
                                    task.allRemoved = true;
                                    task.target[symbolEventName] = null;
                                }
                                break;
                            }
                        }
                    }
                }
                // if all tasks for the eventName + capture have gone,
                // we will really remove the global event callback,
                // if not, return
                if (!task.allRemoved) {
                    return;
                }
                return nativeRemoveEventListener.call(task.target, task.eventName, task.capture ? globalZoneAwareCaptureCallback : globalZoneAwareCallback, task.options);
            };
            var customScheduleNonGlobal = function (task) {
                return nativeAddEventListener.call(taskData.target, taskData.eventName, task.invoke, taskData.options);
            };
            var customSchedulePrepend = function (task) {
                return nativePrependEventListener.call(taskData.target, taskData.eventName, task.invoke, taskData.options);
            };
            var customCancelNonGlobal = function (task) {
                return nativeRemoveEventListener.call(task.target, task.eventName, task.invoke, task.options);
            };
            var customSchedule = useGlobalCallback ? customScheduleGlobal : customScheduleNonGlobal;
            var customCancel = useGlobalCallback ? customCancelGlobal : customCancelNonGlobal;
            var compareTaskCallbackVsDelegate = function (task, delegate) {
                var typeOfDelegate = typeof delegate;
                return (typeOfDelegate === 'function' && task.callback === delegate) ||
                    (typeOfDelegate === 'object' && task.originalDelegate === delegate);
            };
            var compare = (patchOptions && patchOptions.diff) ? patchOptions.diff : compareTaskCallbackVsDelegate;
            var unpatchedEvents = Zone[zoneSymbol('UNPATCHED_EVENTS')];
            var passiveEvents = _global[zoneSymbol('PASSIVE_EVENTS')];
            var makeAddListener = function (nativeListener, addSource, customScheduleFn, customCancelFn, returnTarget, prepend) {
                if (returnTarget === void 0) { returnTarget = false; }
                if (prepend === void 0) { prepend = false; }
                return function () {
                    var target = this || _global;
                    var eventName = arguments[0];
                    if (patchOptions && patchOptions.transferEventName) {
                        eventName = patchOptions.transferEventName(eventName);
                    }
                    var delegate = arguments[1];
                    if (!delegate) {
                        return nativeListener.apply(this, arguments);
                    }
                    if (isNode && eventName === 'uncaughtException') {
                        // don't patch uncaughtException of nodejs to prevent endless loop
                        return nativeListener.apply(this, arguments);
                    }
                    // don't create the bind delegate function for handleEvent
                    // case here to improve addEventListener performance
                    // we will create the bind delegate when invoke
                    var isHandleEvent = false;
                    if (typeof delegate !== 'function') {
                        if (!delegate.handleEvent) {
                            return nativeListener.apply(this, arguments);
                        }
                        isHandleEvent = true;
                    }
                    if (validateHandler && !validateHandler(nativeListener, delegate, target, arguments)) {
                        return;
                    }
                    var passive = passiveSupported && !!passiveEvents && passiveEvents.indexOf(eventName) !== -1;
                    var options = buildEventListenerOptions(arguments[2], passive);
                    if (unpatchedEvents) {
                        // check upatched list
                        for (var i = 0; i < unpatchedEvents.length; i++) {
                            if (eventName === unpatchedEvents[i]) {
                                if (passive) {
                                    return nativeListener.call(target, eventName, delegate, options);
                                }
                                else {
                                    return nativeListener.apply(this, arguments);
                                }
                            }
                        }
                    }
                    var capture = !options ? false : typeof options === 'boolean' ? true : options.capture;
                    var once = options && typeof options === 'object' ? options.once : false;
                    var zone = Zone.current;
                    var symbolEventNames = zoneSymbolEventNames$1[eventName];
                    if (!symbolEventNames) {
                        prepareEventNames(eventName, eventNameToString);
                        symbolEventNames = zoneSymbolEventNames$1[eventName];
                    }
                    var symbolEventName = symbolEventNames[capture ? TRUE_STR : FALSE_STR];
                    var existingTasks = target[symbolEventName];
                    var isExisting = false;
                    if (existingTasks) {
                        // already have task registered
                        isExisting = true;
                        if (checkDuplicate) {
                            for (var i = 0; i < existingTasks.length; i++) {
                                if (compare(existingTasks[i], delegate)) {
                                    // same callback, same capture, same event name, just return
                                    return;
                                }
                            }
                        }
                    }
                    else {
                        existingTasks = target[symbolEventName] = [];
                    }
                    var source;
                    var constructorName = target.constructor['name'];
                    var targetSource = globalSources[constructorName];
                    if (targetSource) {
                        source = targetSource[eventName];
                    }
                    if (!source) {
                        source = constructorName + addSource +
                            (eventNameToString ? eventNameToString(eventName) : eventName);
                    }
                    // do not create a new object as task.data to pass those things
                    // just use the global shared one
                    taskData.options = options;
                    if (once) {
                        // if addEventListener with once options, we don't pass it to
                        // native addEventListener, instead we keep the once setting
                        // and handle ourselves.
                        taskData.options.once = false;
                    }
                    taskData.target = target;
                    taskData.capture = capture;
                    taskData.eventName = eventName;
                    taskData.isExisting = isExisting;
                    var data = useGlobalCallback ? OPTIMIZED_ZONE_EVENT_TASK_DATA : undefined;
                    // keep taskData into data to allow onScheduleEventTask to access the task information
                    if (data) {
                        data.taskData = taskData;
                    }
                    var task = zone.scheduleEventTask(source, delegate, data, customScheduleFn, customCancelFn);
                    // should clear taskData.target to avoid memory leak
                    // issue, https://github.com/angular/angular/issues/20442
                    taskData.target = null;
                    // need to clear up taskData because it is a global object
                    if (data) {
                        data.taskData = null;
                    }
                    // have to save those information to task in case
                    // application may call task.zone.cancelTask() directly
                    if (once) {
                        options.once = true;
                    }
                    if (!(!passiveSupported && typeof task.options === 'boolean')) {
                        // if not support passive, and we pass an option object
                        // to addEventListener, we should save the options to task
                        task.options = options;
                    }
                    task.target = target;
                    task.capture = capture;
                    task.eventName = eventName;
                    if (isHandleEvent) {
                        // save original delegate for compare to check duplicate
                        task.originalDelegate = delegate;
                    }
                    if (!prepend) {
                        existingTasks.push(task);
                    }
                    else {
                        existingTasks.unshift(task);
                    }
                    if (returnTarget) {
                        return target;
                    }
                };
            };
            proto[ADD_EVENT_LISTENER] = makeAddListener(nativeAddEventListener, ADD_EVENT_LISTENER_SOURCE, customSchedule, customCancel, returnTarget);
            if (nativePrependEventListener) {
                proto[PREPEND_EVENT_LISTENER] = makeAddListener(nativePrependEventListener, PREPEND_EVENT_LISTENER_SOURCE, customSchedulePrepend, customCancel, returnTarget, true);
            }
            proto[REMOVE_EVENT_LISTENER] = function () {
                var target = this || _global;
                var eventName = arguments[0];
                if (patchOptions && patchOptions.transferEventName) {
                    eventName = patchOptions.transferEventName(eventName);
                }
                var options = arguments[2];
                var capture = !options ? false : typeof options === 'boolean' ? true : options.capture;
                var delegate = arguments[1];
                if (!delegate) {
                    return nativeRemoveEventListener.apply(this, arguments);
                }
                if (validateHandler &&
                    !validateHandler(nativeRemoveEventListener, delegate, target, arguments)) {
                    return;
                }
                var symbolEventNames = zoneSymbolEventNames$1[eventName];
                var symbolEventName;
                if (symbolEventNames) {
                    symbolEventName = symbolEventNames[capture ? TRUE_STR : FALSE_STR];
                }
                var existingTasks = symbolEventName && target[symbolEventName];
                if (existingTasks) {
                    for (var i = 0; i < existingTasks.length; i++) {
                        var existingTask = existingTasks[i];
                        if (compare(existingTask, delegate)) {
                            existingTasks.splice(i, 1);
                            // set isRemoved to data for faster invokeTask check
                            existingTask.isRemoved = true;
                            if (existingTasks.length === 0) {
                                // all tasks for the eventName + capture have gone,
                                // remove globalZoneAwareCallback and remove the task cache from target
                                existingTask.allRemoved = true;
                                target[symbolEventName] = null;
                                // in the target, we have an event listener which is added by on_property
                                // such as target.onclick = function() {}, so we need to clear this internal
                                // property too if all delegates all removed
                                if (typeof eventName === 'string') {
                                    var onPropertySymbol = ZONE_SYMBOL_PREFIX + 'ON_PROPERTY' + eventName;
                                    target[onPropertySymbol] = null;
                                }
                            }
                            existingTask.zone.cancelTask(existingTask);
                            if (returnTarget) {
                                return target;
                            }
                            return;
                        }
                    }
                }
                // issue 930, didn't find the event name or callback
                // from zone kept existingTasks, the callback maybe
                // added outside of zone, we need to call native removeEventListener
                // to try to remove it.
                return nativeRemoveEventListener.apply(this, arguments);
            };
            proto[LISTENERS_EVENT_LISTENER] = function () {
                var target = this || _global;
                var eventName = arguments[0];
                if (patchOptions && patchOptions.transferEventName) {
                    eventName = patchOptions.transferEventName(eventName);
                }
                var listeners = [];
                var tasks = findEventTasks(target, eventNameToString ? eventNameToString(eventName) : eventName);
                for (var i = 0; i < tasks.length; i++) {
                    var task = tasks[i];
                    var delegate = task.originalDelegate ? task.originalDelegate : task.callback;
                    listeners.push(delegate);
                }
                return listeners;
            };
            proto[REMOVE_ALL_LISTENERS_EVENT_LISTENER] = function () {
                var target = this || _global;
                var eventName = arguments[0];
                if (!eventName) {
                    var keys = Object.keys(target);
                    for (var i = 0; i < keys.length; i++) {
                        var prop = keys[i];
                        var match = EVENT_NAME_SYMBOL_REGX.exec(prop);
                        var evtName = match && match[1];
                        // in nodejs EventEmitter, removeListener event is
                        // used for monitoring the removeListener call,
                        // so just keep removeListener eventListener until
                        // all other eventListeners are removed
                        if (evtName && evtName !== 'removeListener') {
                            this[REMOVE_ALL_LISTENERS_EVENT_LISTENER].call(this, evtName);
                        }
                    }
                    // remove removeListener listener finally
                    this[REMOVE_ALL_LISTENERS_EVENT_LISTENER].call(this, 'removeListener');
                }
                else {
                    if (patchOptions && patchOptions.transferEventName) {
                        eventName = patchOptions.transferEventName(eventName);
                    }
                    var symbolEventNames = zoneSymbolEventNames$1[eventName];
                    if (symbolEventNames) {
                        var symbolEventName = symbolEventNames[FALSE_STR];
                        var symbolCaptureEventName = symbolEventNames[TRUE_STR];
                        var tasks = target[symbolEventName];
                        var captureTasks = target[symbolCaptureEventName];
                        if (tasks) {
                            var removeTasks = tasks.slice();
                            for (var i = 0; i < removeTasks.length; i++) {
                                var task = removeTasks[i];
                                var delegate = task.originalDelegate ? task.originalDelegate : task.callback;
                                this[REMOVE_EVENT_LISTENER].call(this, eventName, delegate, task.options);
                            }
                        }
                        if (captureTasks) {
                            var removeTasks = captureTasks.slice();
                            for (var i = 0; i < removeTasks.length; i++) {
                                var task = removeTasks[i];
                                var delegate = task.originalDelegate ? task.originalDelegate : task.callback;
                                this[REMOVE_EVENT_LISTENER].call(this, eventName, delegate, task.options);
                            }
                        }
                    }
                }
                if (returnTarget) {
                    return this;
                }
            };
            // for native toString patch
            attachOriginToPatched(proto[ADD_EVENT_LISTENER], nativeAddEventListener);
            attachOriginToPatched(proto[REMOVE_EVENT_LISTENER], nativeRemoveEventListener);
            if (nativeRemoveAllListeners) {
                attachOriginToPatched(proto[REMOVE_ALL_LISTENERS_EVENT_LISTENER], nativeRemoveAllListeners);
            }
            if (nativeListeners) {
                attachOriginToPatched(proto[LISTENERS_EVENT_LISTENER], nativeListeners);
            }
            return true;
        }
        var results = [];
        for (var i = 0; i < apis.length; i++) {
            results[i] = patchEventTargetMethods(apis[i], patchOptions);
        }
        return results;
    }
    function findEventTasks(target, eventName) {
        if (!eventName) {
            var foundTasks = [];
            for (var prop in target) {
                var match = EVENT_NAME_SYMBOL_REGX.exec(prop);
                var evtName = match && match[1];
                if (evtName && (!eventName || evtName === eventName)) {
                    var tasks = target[prop];
                    if (tasks) {
                        for (var i = 0; i < tasks.length; i++) {
                            foundTasks.push(tasks[i]);
                        }
                    }
                }
            }
            return foundTasks;
        }
        var symbolEventName = zoneSymbolEventNames$1[eventName];
        if (!symbolEventName) {
            prepareEventNames(eventName);
            symbolEventName = zoneSymbolEventNames$1[eventName];
        }
        var captureFalseTasks = target[symbolEventName[FALSE_STR]];
        var captureTrueTasks = target[symbolEventName[TRUE_STR]];
        if (!captureFalseTasks) {
            return captureTrueTasks ? captureTrueTasks.slice() : [];
        }
        else {
            return captureTrueTasks ? captureFalseTasks.concat(captureTrueTasks) :
                captureFalseTasks.slice();
        }
    }
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('EventEmitter', function (global) {
        // For EventEmitter
        var EE_ADD_LISTENER = 'addListener';
        var EE_PREPEND_LISTENER = 'prependListener';
        var EE_REMOVE_LISTENER = 'removeListener';
        var EE_REMOVE_ALL_LISTENER = 'removeAllListeners';
        var EE_LISTENERS = 'listeners';
        var EE_ON = 'on';
        var EE_OFF = 'off';
        var compareTaskCallbackVsDelegate = function (task, delegate) {
            // same callback, same capture, same event name, just return
            return task.callback === delegate || task.callback.listener === delegate;
        };
        var eventNameToString = function (eventName) {
            if (typeof eventName === 'string') {
                return eventName;
            }
            if (!eventName) {
                return '';
            }
            return eventName.toString().replace('(', '_').replace(')', '_');
        };
        function patchEventEmitterMethods(obj) {
            var result = patchEventTarget(global, [obj], {
                useG: false,
                add: EE_ADD_LISTENER,
                rm: EE_REMOVE_LISTENER,
                prepend: EE_PREPEND_LISTENER,
                rmAll: EE_REMOVE_ALL_LISTENER,
                listeners: EE_LISTENERS,
                chkDup: false,
                rt: true,
                diff: compareTaskCallbackVsDelegate,
                eventNameToString: eventNameToString
            });
            if (result && result[0]) {
                obj[EE_ON] = obj[EE_ADD_LISTENER];
                obj[EE_OFF] = obj[EE_REMOVE_LISTENER];
            }
        }
        // EventEmitter
        var events;
        try {
            events = require('events');
        }
        catch (err) {
        }
        if (events && events.EventEmitter) {
            patchEventEmitterMethods(events.EventEmitter.prototype);
        }
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    Zone.__load_patch('fs', function () {
        var fs;
        try {
            fs = require('fs');
        }
        catch (err) {
        }
        // watch, watchFile, unwatchFile has been patched
        // because EventEmitter has been patched
        var TO_PATCH_MACROTASK_METHODS = [
            'access', 'appendFile', 'chmod', 'chown', 'close', 'exists', 'fchmod',
            'fchown', 'fdatasync', 'fstat', 'fsync', 'ftruncate', 'futimes', 'lchmod',
            'lchown', 'link', 'lstat', 'mkdir', 'mkdtemp', 'open', 'read',
            'readdir', 'readFile', 'readlink', 'realpath', 'rename', 'rmdir', 'stat',
            'symlink', 'truncate', 'unlink', 'utimes', 'write', 'writeFile',
        ];
        if (fs) {
            TO_PATCH_MACROTASK_METHODS.filter(function (name) { return !!fs[name] && typeof fs[name] === 'function'; })
                .forEach(function (name) {
                patchMacroTask(fs, name, function (self, args) {
                    return {
                        name: 'fs.' + name,
                        args: args,
                        cbIdx: args.length > 0 ? args.length - 1 : -1,
                        target: self
                    };
                });
            });
        }
    });
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var taskSymbol = zoneSymbol('zoneTask');
    function patchTimer(window, setName, cancelName, nameSuffix) {
        var setNative = null;
        var clearNative = null;
        setName += nameSuffix;
        cancelName += nameSuffix;
        var tasksByHandleId = {};
        function scheduleTask(task) {
            var data = task.data;
            data.args[0] = function () {
                return task.invoke.apply(this, arguments);
            };
            data.handleId = setNative.apply(window, data.args);
            return task;
        }
        function clearTask(task) {
            return clearNative.call(window, task.data.handleId);
        }
        setNative =
            patchMethod(window, setName, function (delegate) { return function (self, args) {
                if (typeof args[0] === 'function') {
                    var options_1 = {
                        isPeriodic: nameSuffix === 'Interval',
                        delay: (nameSuffix === 'Timeout' || nameSuffix === 'Interval') ? args[1] || 0 :
                            undefined,
                        args: args
                    };
                    var callback_1 = args[0];
                    args[0] = function timer() {
                        try {
                            return callback_1.apply(this, arguments);
                        }
                        finally {
                            // issue-934, task will be cancelled
                            // even it is a periodic task such as
                            // setInterval
                            // https://github.com/angular/angular/issues/40387
                            // Cleanup tasksByHandleId should be handled before scheduleTask
                            // Since some zoneSpec may intercept and doesn't trigger
                            // scheduleFn(scheduleTask) provided here.
                            if (!(options_1.isPeriodic)) {
                                if (typeof options_1.handleId === 'number') {
                                    // in non-nodejs env, we remove timerId
                                    // from local cache
                                    delete tasksByHandleId[options_1.handleId];
                                }
                                else if (options_1.handleId) {
                                    // Node returns complex objects as handleIds
                                    // we remove task reference from timer object
                                    options_1.handleId[taskSymbol] = null;
                                }
                            }
                        }
                    };
                    var task = scheduleMacroTaskWithCurrentZone(setName, args[0], options_1, scheduleTask, clearTask);
                    if (!task) {
                        return task;
                    }
                    // Node.js must additionally support the ref and unref functions.
                    var handle = task.data.handleId;
                    if (typeof handle === 'number') {
                        // for non nodejs env, we save handleId: task
                        // mapping in local cache for clearTimeout
                        tasksByHandleId[handle] = task;
                    }
                    else if (handle) {
                        // for nodejs env, we save task
                        // reference in timerId Object for clearTimeout
                        handle[taskSymbol] = task;
                    }
                    // check whether handle is null, because some polyfill or browser
                    // may return undefined from setTimeout/setInterval/setImmediate/requestAnimationFrame
                    if (handle && handle.ref && handle.unref && typeof handle.ref === 'function' &&
                        typeof handle.unref === 'function') {
                        task.ref = handle.ref.bind(handle);
                        task.unref = handle.unref.bind(handle);
                    }
                    if (typeof handle === 'number' || handle) {
                        return handle;
                    }
                    return task;
                }
                else {
                    // cause an error by calling it directly.
                    return delegate.apply(window, args);
                }
            }; });
        clearNative =
            patchMethod(window, cancelName, function (delegate) { return function (self, args) {
                var id = args[0];
                var task;
                if (typeof id === 'number') {
                    // non nodejs env.
                    task = tasksByHandleId[id];
                }
                else {
                    // nodejs env.
                    task = id && id[taskSymbol];
                    // other environments.
                    if (!task) {
                        task = id;
                    }
                }
                if (task && typeof task.type === 'string') {
                    if (task.state !== 'notScheduled' &&
                        (task.cancelFn && task.data.isPeriodic || task.runCount === 0)) {
                        if (typeof id === 'number') {
                            delete tasksByHandleId[id];
                        }
                        else if (id) {
                            id[taskSymbol] = null;
                        }
                        // Do not cancel already canceled functions
                        task.zone.cancelTask(task);
                    }
                }
                else {
                    // cause an error by calling it directly.
                    delegate.apply(window, args);
                }
            }; });
    }
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var set = 'set';
    var clear = 'clear';
    Zone.__load_patch('node_timers', function (global, Zone) {
        // Timers
        var globalUseTimeoutFromTimer = false;
        try {
            var timers = require('timers');
            var globalEqualTimersTimeout = global.setTimeout === timers.setTimeout;
            if (!globalEqualTimersTimeout && !isMix) {
                // 1. if isMix, then we are in mix environment such as Electron
                // we should only patch timers.setTimeout because global.setTimeout
                // have been patched
                // 2. if global.setTimeout not equal timers.setTimeout, check
                // whether global.setTimeout use timers.setTimeout or not
                var originSetTimeout_1 = timers.setTimeout;
                timers.setTimeout = function () {
                    globalUseTimeoutFromTimer = true;
                    return originSetTimeout_1.apply(this, arguments);
                };
                var detectTimeout = global.setTimeout(function () { }, 100);
                clearTimeout(detectTimeout);
                timers.setTimeout = originSetTimeout_1;
            }
            patchTimer(timers, set, clear, 'Timeout');
            patchTimer(timers, set, clear, 'Interval');
            patchTimer(timers, set, clear, 'Immediate');
        }
        catch (error) {
            // timers module not exists, for example, when we using nativeScript
            // timers is not available
        }
        if (isMix) {
            // if we are in mix environment, such as Electron,
            // the global.setTimeout has already been patched,
            // so we just patch timers.setTimeout
            return;
        }
        if (!globalUseTimeoutFromTimer) {
            // 1. global setTimeout equals timers setTimeout
            // 2. or global don't use timers setTimeout(maybe some other library patch setTimeout)
            // 3. or load timers module error happens, we should patch global setTimeout
            patchTimer(global, set, clear, 'Timeout');
            patchTimer(global, set, clear, 'Interval');
            patchTimer(global, set, clear, 'Immediate');
        }
        else {
            // global use timers setTimeout, but not equals
            // this happens when use nodejs v0.10.x, global setTimeout will
            // use a lazy load version of timers setTimeout
            // we should not double patch timer's setTimeout
            // so we only store the __symbol__ for consistency
            global[Zone.__symbol__('setTimeout')] = global.setTimeout;
            global[Zone.__symbol__('setInterval')] = global.setInterval;
            global[Zone.__symbol__('setImmediate')] = global.setImmediate;
        }
    });
    // patch process related methods
    Zone.__load_patch('nextTick', function () {
        // patch nextTick as microTask
        patchMicroTask(process, 'nextTick', function (self, args) {
            return {
                name: 'process.nextTick',
                args: args,
                cbIdx: (args.length > 0 && typeof args[0] === 'function') ? 0 : -1,
                target: process
            };
        });
    });
    Zone.__load_patch('handleUnhandledPromiseRejection', function (global, Zone, api) {
        Zone[api.symbol('unhandledPromiseRejectionHandler')] =
            findProcessPromiseRejectionHandler('unhandledRejection');
        Zone[api.symbol('rejectionHandledHandler')] =
            findProcessPromiseRejectionHandler('rejectionHandled');
        // handle unhandled promise rejection
        function findProcessPromiseRejectionHandler(evtName) {
            return function (e) {
                var eventTasks = findEventTasks(process, evtName);
                eventTasks.forEach(function (eventTask) {
                    // process has added unhandledrejection event listener
                    // trigger the event listener
                    if (evtName === 'unhandledRejection') {
                        eventTask.invoke(e.rejection, e.promise);
                    }
                    else if (evtName === 'rejectionHandled') {
                        eventTask.invoke(e.promise);
                    }
                });
            };
        }
    });
    // Crypto
    Zone.__load_patch('crypto', function () {
        var crypto;
        try {
            crypto = require('crypto');
        }
        catch (err) {
        }
        // use the generic patchMacroTask to patch crypto
        if (crypto) {
            var methodNames = ['randomBytes', 'pbkdf2'];
            methodNames.forEach(function (name) {
                patchMacroTask(crypto, name, function (self, args) {
                    return {
                        name: 'crypto.' + name,
                        args: args,
                        cbIdx: (args.length > 0 && typeof args[args.length - 1] === 'function') ?
                            args.length - 1 :
                            -1,
                        target: crypto
                    };
                });
            });
        }
    });
    Zone.__load_patch('console', function (global, Zone) {
        var consoleMethods = ['dir', 'log', 'info', 'error', 'warn', 'assert', 'debug', 'timeEnd', 'trace'];
        consoleMethods.forEach(function (m) {
            var originalMethod = console[Zone.__symbol__(m)] = console[m];
            if (originalMethod) {
                console[m] = function () {
                    var args = ArraySlice.call(arguments);
                    if (Zone.current === Zone.root) {
                        return originalMethod.apply(this, args);
                    }
                    else {
                        return Zone.root.run(originalMethod, this, args);
                    }
                };
            }
        });
    });
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
            var _loop_3 = function (i) {
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
                _loop_3(i);
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
