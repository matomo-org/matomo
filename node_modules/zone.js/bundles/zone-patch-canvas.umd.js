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
    Zone.__load_patch('canvas', function (global, Zone, api) {
        var HTMLCanvasElement = global['HTMLCanvasElement'];
        if (typeof HTMLCanvasElement !== 'undefined' && HTMLCanvasElement.prototype &&
            HTMLCanvasElement.prototype.toBlob) {
            api.patchMacroTask(HTMLCanvasElement.prototype, 'toBlob', function (self, args) {
                return { name: 'HTMLCanvasElement.toBlob', target: self, cbIdx: 0, args: args };
            });
        }
    });
})));
