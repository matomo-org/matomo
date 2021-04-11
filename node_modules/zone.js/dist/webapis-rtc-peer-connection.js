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
    Zone.__load_patch('RTCPeerConnection', function (global, Zone, api) {
        var RTCPeerConnection = global['RTCPeerConnection'];
        if (!RTCPeerConnection) {
            return;
        }
        var addSymbol = api.symbol('addEventListener');
        var removeSymbol = api.symbol('removeEventListener');
        RTCPeerConnection.prototype.addEventListener = RTCPeerConnection.prototype[addSymbol];
        RTCPeerConnection.prototype.removeEventListener = RTCPeerConnection.prototype[removeSymbol];
        // RTCPeerConnection extends EventTarget, so we must clear the symbol
        // to allow patch RTCPeerConnection.prototype.addEventListener again
        RTCPeerConnection.prototype[addSymbol] = null;
        RTCPeerConnection.prototype[removeSymbol] = null;
        api.patchEventTarget(global, [RTCPeerConnection.prototype], { useG: false });
    });
})));
