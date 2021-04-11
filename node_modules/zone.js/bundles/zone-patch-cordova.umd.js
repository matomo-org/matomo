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
    Zone.__load_patch('cordova', function (global, Zone, api) {
        if (global.cordova) {
            var SUCCESS_SOURCE_1 = 'cordova.exec.success';
            var ERROR_SOURCE_1 = 'cordova.exec.error';
            var FUNCTION_1 = 'function';
            var nativeExec_1 = api.patchMethod(global.cordova, 'exec', function () { return function (self, args) {
                if (args.length > 0 && typeof args[0] === FUNCTION_1) {
                    args[0] = Zone.current.wrap(args[0], SUCCESS_SOURCE_1);
                }
                if (args.length > 1 && typeof args[1] === FUNCTION_1) {
                    args[1] = Zone.current.wrap(args[1], ERROR_SOURCE_1);
                }
                return nativeExec_1.apply(self, args);
            }; });
        }
    });
    Zone.__load_patch('cordova.FileReader', function (global, Zone) {
        if (global.cordova && typeof global['FileReader'] !== 'undefined') {
            document.addEventListener('deviceReady', function () {
                var FileReader = global['FileReader'];
                ['abort', 'error', 'load', 'loadstart', 'loadend', 'progress'].forEach(function (prop) {
                    var eventNameSymbol = Zone.__symbol__('ON_PROPERTY' + prop);
                    Object.defineProperty(FileReader.prototype, eventNameSymbol, {
                        configurable: true,
                        get: function () {
                            return this._realReader && this._realReader[eventNameSymbol];
                        }
                    });
                });
            });
        }
    });
})));
