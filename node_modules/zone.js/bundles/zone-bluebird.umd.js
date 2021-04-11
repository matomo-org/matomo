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
    Zone.__load_patch('bluebird', function (global, Zone, api) {
        // TODO: @JiaLiPassion, we can automatically patch bluebird
        // if global.Promise = Bluebird, but sometimes in nodejs,
        // global.Promise is not Bluebird, and Bluebird is just be
        // used by other libraries such as sequelize, so I think it is
        // safe to just expose a method to patch Bluebird explicitly
        var BLUEBIRD = 'bluebird';
        Zone[Zone.__symbol__(BLUEBIRD)] = function patchBluebird(Bluebird) {
            // patch method of Bluebird.prototype which not using `then` internally
            var bluebirdApis = ['then', 'spread', 'finally'];
            bluebirdApis.forEach(function (bapi) {
                api.patchMethod(Bluebird.prototype, bapi, function (delegate) { return function (self, args) {
                    var zone = Zone.current;
                    var _loop_1 = function (i) {
                        var func = args[i];
                        if (typeof func === 'function') {
                            args[i] = function () {
                                var argSelf = this;
                                var argArgs = arguments;
                                return new Bluebird(function (res, rej) {
                                    zone.scheduleMicroTask('Promise.then', function () {
                                        try {
                                            res(func.apply(argSelf, argArgs));
                                        }
                                        catch (error) {
                                            rej(error);
                                        }
                                    });
                                });
                            };
                        }
                    };
                    for (var i = 0; i < args.length; i++) {
                        _loop_1(i);
                    }
                    return delegate.apply(self, args);
                }; });
            });
            if (typeof window !== 'undefined') {
                window.addEventListener('unhandledrejection', function (event) {
                    var error = event.detail && event.detail.reason;
                    if (error && error.isHandledByZone) {
                        event.preventDefault();
                        if (typeof event.stopImmediatePropagation === 'function') {
                            event.stopImmediatePropagation();
                        }
                    }
                });
            }
            else if (typeof process !== 'undefined') {
                process.on('unhandledRejection', function (reason, p) {
                    if (reason && reason.isHandledByZone) {
                        var listeners_1 = process.listeners('unhandledRejection');
                        if (listeners_1) {
                            // remove unhandledRejection listeners so the callback
                            // will not be triggered.
                            process.removeAllListeners('unhandledRejection');
                            process.nextTick(function () {
                                listeners_1.forEach(function (listener) { return process.on('unhandledRejection', listener); });
                            });
                        }
                    }
                });
            }
            Bluebird.onPossiblyUnhandledRejection(function (e, promise) {
                try {
                    Zone.current.runGuarded(function () {
                        e.isHandledByZone = true;
                        throw e;
                    });
                }
                catch (err) {
                    err.isHandledByZone = false;
                    api.onUnhandledError(err);
                }
            });
            // override global promise
            global.Promise = Bluebird;
        };
    });
})));
