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
    Zone.__load_patch('getUserMedia', function (global, Zone, api) {
        function wrapFunctionArgs(func, source) {
            return function () {
                var args = Array.prototype.slice.call(arguments);
                var wrappedArgs = api.bindArguments(args, source ? source : func.name);
                return func.apply(this, wrappedArgs);
            };
        }
        var navigator = global['navigator'];
        if (navigator && navigator.getUserMedia) {
            navigator.getUserMedia = wrapFunctionArgs(navigator.getUserMedia);
        }
    });
})));
