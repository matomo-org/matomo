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
    Zone.__load_patch('jsonp', function (global, Zone, api) {
        // because jsonp is not a standard api, there are a lot of
        // implementations, so zone.js just provide a helper util to
        // patch the jsonp send and onSuccess/onError callback
        // the options is an object which contains
        // - jsonp, the jsonp object which hold the send function
        // - sendFuncName, the name of the send function
        // - successFuncName, success func name
        // - failedFuncName, failed func name
        Zone[Zone.__symbol__('jsonp')] = function patchJsonp(options) {
            if (!options || !options.jsonp || !options.sendFuncName) {
                return;
            }
            var noop = function () { };
            [options.successFuncName, options.failedFuncName].forEach(function (methodName) {
                if (!methodName) {
                    return;
                }
                var oriFunc = global[methodName];
                if (oriFunc) {
                    api.patchMethod(global, methodName, function (delegate) { return function (self, args) {
                        var task = global[api.symbol('jsonTask')];
                        if (task) {
                            task.callback = delegate;
                            return task.invoke.apply(self, args);
                        }
                        else {
                            return delegate.apply(self, args);
                        }
                    }; });
                }
                else {
                    Object.defineProperty(global, methodName, {
                        configurable: true,
                        enumerable: true,
                        get: function () {
                            return function () {
                                var task = global[api.symbol('jsonpTask')];
                                var delegate = global[api.symbol("jsonp" + methodName + "callback")];
                                if (task) {
                                    if (delegate) {
                                        task.callback = delegate;
                                    }
                                    global[api.symbol('jsonpTask')] = undefined;
                                    return task.invoke.apply(this, arguments);
                                }
                                else {
                                    if (delegate) {
                                        return delegate.apply(this, arguments);
                                    }
                                }
                                return null;
                            };
                        },
                        set: function (callback) {
                            this[api.symbol("jsonp" + methodName + "callback")] = callback;
                        }
                    });
                }
            });
            api.patchMethod(options.jsonp, options.sendFuncName, function (delegate) { return function (self, args) {
                global[api.symbol('jsonpTask')] =
                    Zone.current.scheduleMacroTask('jsonp', noop, {}, function (task) {
                        return delegate.apply(self, args);
                    }, noop);
            }; });
        };
    });
})));
