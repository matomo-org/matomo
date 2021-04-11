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
Zone.__load_patch('bluebird', (global, Zone, api) => {
    // TODO: @JiaLiPassion, we can automatically patch bluebird
    // if global.Promise = Bluebird, but sometimes in nodejs,
    // global.Promise is not Bluebird, and Bluebird is just be
    // used by other libraries such as sequelize, so I think it is
    // safe to just expose a method to patch Bluebird explicitly
    const BLUEBIRD = 'bluebird';
    Zone[Zone.__symbol__(BLUEBIRD)] = function patchBluebird(Bluebird) {
        // patch method of Bluebird.prototype which not using `then` internally
        const bluebirdApis = ['then', 'spread', 'finally'];
        bluebirdApis.forEach(bapi => {
            api.patchMethod(Bluebird.prototype, bapi, (delegate) => (self, args) => {
                const zone = Zone.current;
                for (let i = 0; i < args.length; i++) {
                    const func = args[i];
                    if (typeof func === 'function') {
                        args[i] = function () {
                            const argSelf = this;
                            const argArgs = arguments;
                            return new Bluebird((res, rej) => {
                                zone.scheduleMicroTask('Promise.then', () => {
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
                }
                return delegate.apply(self, args);
            });
        });
        if (typeof window !== 'undefined') {
            window.addEventListener('unhandledrejection', function (event) {
                const error = event.detail && event.detail.reason;
                if (error && error.isHandledByZone) {
                    event.preventDefault();
                    if (typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                }
            });
        }
        else if (typeof process !== 'undefined') {
            process.on('unhandledRejection', (reason, p) => {
                if (reason && reason.isHandledByZone) {
                    const listeners = process.listeners('unhandledRejection');
                    if (listeners) {
                        // remove unhandledRejection listeners so the callback
                        // will not be triggered.
                        process.removeAllListeners('unhandledRejection');
                        process.nextTick(() => {
                            listeners.forEach(listener => process.on('unhandledRejection', listener));
                        });
                    }
                }
            });
        }
        Bluebird.onPossiblyUnhandledRejection(function (e, promise) {
            try {
                Zone.current.runGuarded(() => {
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
