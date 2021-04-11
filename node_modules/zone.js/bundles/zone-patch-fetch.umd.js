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
    /**
     * @fileoverview
     * @suppress {missingRequire}
     */
    Zone.__load_patch('fetch', function (global, Zone, api) {
        var fetch = global['fetch'];
        if (typeof fetch !== 'function') {
            return;
        }
        var originalFetch = global[api.symbol('fetch')];
        if (originalFetch) {
            // restore unpatched fetch first
            fetch = originalFetch;
        }
        var ZoneAwarePromise = global.Promise;
        var symbolThenPatched = api.symbol('thenPatched');
        var fetchTaskScheduling = api.symbol('fetchTaskScheduling');
        var fetchTaskAborting = api.symbol('fetchTaskAborting');
        var OriginalAbortController = global['AbortController'];
        var supportAbort = typeof OriginalAbortController === 'function';
        var abortNative = null;
        if (supportAbort) {
            global['AbortController'] = function () {
                var abortController = new OriginalAbortController();
                var signal = abortController.signal;
                signal.abortController = abortController;
                return abortController;
            };
            abortNative = api.patchMethod(OriginalAbortController.prototype, 'abort', function (delegate) { return function (self, args) {
                if (self.task) {
                    return self.task.zone.cancelTask(self.task);
                }
                return delegate.apply(self, args);
            }; });
        }
        var placeholder = function () { };
        global['fetch'] = function () {
            var _this = this;
            var args = Array.prototype.slice.call(arguments);
            var options = args.length > 1 ? args[1] : null;
            var signal = options && options.signal;
            return new Promise(function (res, rej) {
                var task = Zone.current.scheduleMacroTask('fetch', placeholder, { fetchArgs: args }, function () {
                    var fetchPromise;
                    var zone = Zone.current;
                    try {
                        zone[fetchTaskScheduling] = true;
                        fetchPromise = fetch.apply(_this, args);
                    }
                    catch (error) {
                        rej(error);
                        return;
                    }
                    finally {
                        zone[fetchTaskScheduling] = false;
                    }
                    if (!(fetchPromise instanceof ZoneAwarePromise)) {
                        var ctor = fetchPromise.constructor;
                        if (!ctor[symbolThenPatched]) {
                            api.patchThen(ctor);
                        }
                    }
                    fetchPromise.then(function (resource) {
                        if (task.state !== 'notScheduled') {
                            task.invoke();
                        }
                        res(resource);
                    }, function (error) {
                        if (task.state !== 'notScheduled') {
                            task.invoke();
                        }
                        rej(error);
                    });
                }, function () {
                    if (!supportAbort) {
                        rej('No AbortController supported, can not cancel fetch');
                        return;
                    }
                    if (signal && signal.abortController && !signal.aborted &&
                        typeof signal.abortController.abort === 'function' && abortNative) {
                        try {
                            Zone.current[fetchTaskAborting] = true;
                            abortNative.call(signal.abortController);
                        }
                        finally {
                            Zone.current[fetchTaskAborting] = false;
                        }
                    }
                    else {
                        rej('cancel fetch need a AbortController.signal');
                    }
                });
                if (signal && signal.abortController) {
                    signal.abortController.task = task;
                }
            });
        };
    });
})));
