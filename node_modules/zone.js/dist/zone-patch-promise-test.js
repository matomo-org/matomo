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
