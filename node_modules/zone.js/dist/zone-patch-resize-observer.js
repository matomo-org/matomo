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
    Zone.__load_patch('ResizeObserver', function (global, Zone, api) {
        var ResizeObserver = global['ResizeObserver'];
        if (!ResizeObserver) {
            return;
        }
        var resizeObserverSymbol = api.symbol('ResizeObserver');
        api.patchMethod(global, 'ResizeObserver', function (delegate) { return function (self, args) {
            var callback = args.length > 0 ? args[0] : null;
            if (callback) {
                args[0] = function (entries, observer) {
                    var _this = this;
                    var zones = {};
                    var currZone = Zone.current;
                    for (var _i = 0, entries_1 = entries; _i < entries_1.length; _i++) {
                        var entry = entries_1[_i];
                        var zone = entry.target[resizeObserverSymbol];
                        if (!zone) {
                            zone = currZone;
                        }
                        var zoneEntriesInfo = zones[zone.name];
                        if (!zoneEntriesInfo) {
                            zones[zone.name] = zoneEntriesInfo = { entries: [], zone: zone };
                        }
                        zoneEntriesInfo.entries.push(entry);
                    }
                    Object.keys(zones).forEach(function (zoneName) {
                        var zoneEntriesInfo = zones[zoneName];
                        if (zoneEntriesInfo.zone !== Zone.current) {
                            zoneEntriesInfo.zone.run(callback, _this, [zoneEntriesInfo.entries, observer], 'ResizeObserver');
                        }
                        else {
                            callback.call(_this, zoneEntriesInfo.entries, observer);
                        }
                    });
                };
            }
            return args.length > 0 ? new ResizeObserver(args[0]) : new ResizeObserver();
        }; });
        api.patchMethod(ResizeObserver.prototype, 'observe', function (delegate) { return function (self, args) {
            var target = args.length > 0 ? args[0] : null;
            if (!target) {
                return delegate.apply(self, args);
            }
            var targets = self[resizeObserverSymbol];
            if (!targets) {
                targets = self[resizeObserverSymbol] = [];
            }
            targets.push(target);
            target[resizeObserverSymbol] = Zone.current;
            return delegate.apply(self, args);
        }; });
        api.patchMethod(ResizeObserver.prototype, 'unobserve', function (delegate) { return function (self, args) {
            var target = args.length > 0 ? args[0] : null;
            if (!target) {
                return delegate.apply(self, args);
            }
            var targets = self[resizeObserverSymbol];
            if (targets) {
                for (var i = 0; i < targets.length; i++) {
                    if (targets[i] === target) {
                        targets.splice(i, 1);
                        break;
                    }
                }
            }
            target[resizeObserverSymbol] = undefined;
            return delegate.apply(self, args);
        }; });
        api.patchMethod(ResizeObserver.prototype, 'disconnect', function (delegate) { return function (self, args) {
            var targets = self[resizeObserverSymbol];
            if (targets) {
                targets.forEach(function (target) {
                    target[resizeObserverSymbol] = undefined;
                });
                self[resizeObserverSymbol] = undefined;
            }
            return delegate.apply(self, args);
        }; });
    });
})));
