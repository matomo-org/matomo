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
(function (_global) {
    class AsyncTestZoneSpec {
        constructor(finishCallback, failCallback, namePrefix) {
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
        isUnresolvedChainedPromisePending() {
            return this.unresolvedChainedPromiseCount > 0;
        }
        _finishCallbackIfDone() {
            if (!(this._pendingMicroTasks || this._pendingMacroTasks ||
                (this.supportWaitUnresolvedChainedPromise && this.isUnresolvedChainedPromisePending()))) {
                // We do this because we would like to catch unhandled rejected promises.
                this.runZone.run(() => {
                    setTimeout(() => {
                        if (!this._alreadyErrored && !(this._pendingMicroTasks || this._pendingMacroTasks)) {
                            this.finishCallback();
                        }
                    }, 0);
                });
            }
        }
        patchPromiseForTest() {
            if (!this.supportWaitUnresolvedChainedPromise) {
                return;
            }
            const patchPromiseForTest = Promise[Zone.__symbol__('patchPromiseForTest')];
            if (patchPromiseForTest) {
                patchPromiseForTest();
            }
        }
        unPatchPromiseForTest() {
            if (!this.supportWaitUnresolvedChainedPromise) {
                return;
            }
            const unPatchPromiseForTest = Promise[Zone.__symbol__('unPatchPromiseForTest')];
            if (unPatchPromiseForTest) {
                unPatchPromiseForTest();
            }
        }
        onScheduleTask(delegate, current, target, task) {
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
        }
        onInvokeTask(delegate, current, target, task, applyThis, applyArgs) {
            if (task.type !== 'eventTask') {
                this._isSync = false;
            }
            return delegate.invokeTask(target, task, applyThis, applyArgs);
        }
        onCancelTask(delegate, current, target, task) {
            if (task.type !== 'eventTask') {
                this._isSync = false;
            }
            return delegate.cancelTask(target, task);
        }
        // Note - we need to use onInvoke at the moment to call finish when a test is
        // fully synchronous. TODO(juliemr): remove this when the logic for
        // onHasTask changes and it calls whenever the task queues are dirty.
        // updated by(JiaLiPassion), only call finish callback when no task
        // was scheduled/invoked/canceled.
        onInvoke(parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source) {
            try {
                this._isSync = true;
                return parentZoneDelegate.invoke(targetZone, delegate, applyThis, applyArgs, source);
            }
            finally {
                const afterTaskCounts = parentZoneDelegate._taskCounts;
                if (this._isSync) {
                    this._finishCallbackIfDone();
                }
            }
        }
        onHandleError(parentZoneDelegate, currentZone, targetZone, error) {
            // Let the parent try to handle the error.
            const result = parentZoneDelegate.handleError(targetZone, error);
            if (result) {
                this.failCallback(error);
                this._alreadyErrored = true;
            }
            return false;
        }
        onHasTask(delegate, current, target, hasTaskState) {
            delegate.hasTask(target, hasTaskState);
            if (hasTaskState.change == 'microTask') {
                this._pendingMicroTasks = hasTaskState.microTask;
                this._finishCallbackIfDone();
            }
            else if (hasTaskState.change == 'macroTask') {
                this._pendingMacroTasks = hasTaskState.macroTask;
                this._finishCallbackIfDone();
            }
        }
    }
    AsyncTestZoneSpec.symbolParentUnresolved = Zone.__symbol__('parentUnresolved');
    // Export the class so that new instances can be created with proper
    // constructor params.
    Zone['AsyncTestZoneSpec'] = AsyncTestZoneSpec;
})(typeof window !== 'undefined' && window || typeof self !== 'undefined' && self || global);
Zone.__load_patch('asynctest', (global, Zone, api) => {
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
                runInTestZone(fn, this, done, (err) => {
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
            return new Promise((finishCallback, failCallback) => {
                runInTestZone(fn, this, finishCallback, failCallback);
            });
        };
    };
    function runInTestZone(fn, context, finishCallback, failCallback) {
        const currentZone = Zone.current;
        const AsyncTestZoneSpec = Zone['AsyncTestZoneSpec'];
        if (AsyncTestZoneSpec === undefined) {
            throw new Error('AsyncTestZoneSpec is needed for the async() test helper but could not be found. ' +
                'Please make sure that your environment includes zone.js/dist/async-test.js');
        }
        const ProxyZoneSpec = Zone['ProxyZoneSpec'];
        if (!ProxyZoneSpec) {
            throw new Error('ProxyZoneSpec is needed for the async() test helper but could not be found. ' +
                'Please make sure that your environment includes zone.js/dist/proxy.js');
        }
        const proxyZoneSpec = ProxyZoneSpec.get();
        ProxyZoneSpec.assertPresent();
        // We need to create the AsyncTestZoneSpec outside the ProxyZone.
        // If we do it in ProxyZone then we will get to infinite recursion.
        const proxyZone = Zone.current.getZoneWith('ProxyZoneSpec');
        const previousDelegate = proxyZoneSpec.getDelegate();
        proxyZone.parent.run(() => {
            const testZoneSpec = new AsyncTestZoneSpec(() => {
                // Need to restore the original zone.
                if (proxyZoneSpec.getDelegate() == testZoneSpec) {
                    // Only reset the zone spec if it's
                    // sill this one. Otherwise, assume
                    // it's OK.
                    proxyZoneSpec.setDelegate(previousDelegate);
                }
                testZoneSpec.unPatchPromiseForTest();
                currentZone.run(() => {
                    finishCallback();
                });
            }, (error) => {
                // Need to restore the original zone.
                if (proxyZoneSpec.getDelegate() == testZoneSpec) {
                    // Only reset the zone spec if it's sill this one. Otherwise, assume it's OK.
                    proxyZoneSpec.setDelegate(previousDelegate);
                }
                testZoneSpec.unPatchPromiseForTest();
                currentZone.run(() => {
                    failCallback(error);
                });
            }, 'test');
            proxyZoneSpec.setDelegate(testZoneSpec);
            testZoneSpec.patchPromiseForTest();
        });
        return Zone.current.runGuarded(fn, context);
    }
});
