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
    (function (global) {
        // Detect and setup WTF.
        var wtfTrace = null;
        var wtfEvents = null;
        var wtfEnabled = (function () {
            var wtf = global['wtf'];
            if (wtf) {
                wtfTrace = wtf.trace;
                if (wtfTrace) {
                    wtfEvents = wtfTrace.events;
                    return true;
                }
            }
            return false;
        })();
        var WtfZoneSpec = /** @class */ (function () {
            function WtfZoneSpec() {
                this.name = 'WTF';
            }
            WtfZoneSpec.prototype.onFork = function (parentZoneDelegate, currentZone, targetZone, zoneSpec) {
                var retValue = parentZoneDelegate.fork(targetZone, zoneSpec);
                WtfZoneSpec.forkInstance(zonePathName(targetZone), retValue.name);
                return retValue;
            };
            WtfZoneSpec.prototype.onInvoke = function (parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source) {
                var src = source || 'unknown';
                var scope = WtfZoneSpec.invokeScope[src];
                if (!scope) {
                    scope = WtfZoneSpec.invokeScope[src] =
                        wtfEvents.createScope("Zone:invoke:" + source + "(ascii zone)");
                }
                return wtfTrace.leaveScope(scope(zonePathName(targetZone)), parentZoneDelegate.invoke(targetZone, delegate, applyThis, applyArgs, source));
            };
            WtfZoneSpec.prototype.onHandleError = function (parentZoneDelegate, currentZone, targetZone, error) {
                return parentZoneDelegate.handleError(targetZone, error);
            };
            WtfZoneSpec.prototype.onScheduleTask = function (parentZoneDelegate, currentZone, targetZone, task) {
                var key = task.type + ':' + task.source;
                var instance = WtfZoneSpec.scheduleInstance[key];
                if (!instance) {
                    instance = WtfZoneSpec.scheduleInstance[key] =
                        wtfEvents.createInstance("Zone:schedule:" + key + "(ascii zone, any data)");
                }
                var retValue = parentZoneDelegate.scheduleTask(targetZone, task);
                instance(zonePathName(targetZone), shallowObj(task.data, 2));
                return retValue;
            };
            WtfZoneSpec.prototype.onInvokeTask = function (parentZoneDelegate, currentZone, targetZone, task, applyThis, applyArgs) {
                var source = task.source;
                var scope = WtfZoneSpec.invokeTaskScope[source];
                if (!scope) {
                    scope = WtfZoneSpec.invokeTaskScope[source] =
                        wtfEvents.createScope("Zone:invokeTask:" + source + "(ascii zone)");
                }
                return wtfTrace.leaveScope(scope(zonePathName(targetZone)), parentZoneDelegate.invokeTask(targetZone, task, applyThis, applyArgs));
            };
            WtfZoneSpec.prototype.onCancelTask = function (parentZoneDelegate, currentZone, targetZone, task) {
                var key = task.source;
                var instance = WtfZoneSpec.cancelInstance[key];
                if (!instance) {
                    instance = WtfZoneSpec.cancelInstance[key] =
                        wtfEvents.createInstance("Zone:cancel:" + key + "(ascii zone, any options)");
                }
                var retValue = parentZoneDelegate.cancelTask(targetZone, task);
                instance(zonePathName(targetZone), shallowObj(task.data, 2));
                return retValue;
            };
            return WtfZoneSpec;
        }());
        WtfZoneSpec.forkInstance = wtfEnabled ? wtfEvents.createInstance('Zone:fork(ascii zone, ascii newZone)') : null;
        WtfZoneSpec.scheduleInstance = {};
        WtfZoneSpec.cancelInstance = {};
        WtfZoneSpec.invokeScope = {};
        WtfZoneSpec.invokeTaskScope = {};
        function shallowObj(obj, depth) {
            if (!obj || !depth)
                return null;
            var out = {};
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    // explicit : any due to https://github.com/microsoft/TypeScript/issues/33191
                    var value = obj[key];
                    switch (typeof value) {
                        case 'object':
                            var name_1 = value && value.constructor && value.constructor.name;
                            value = name_1 == Object.name ? shallowObj(value, depth - 1) : name_1;
                            break;
                        case 'function':
                            value = value.name || undefined;
                            break;
                    }
                    out[key] = value;
                }
            }
            return out;
        }
        function zonePathName(zone) {
            var name = zone.name;
            var localZone = zone.parent;
            while (localZone != null) {
                name = localZone.name + '::' + name;
                localZone = localZone.parent;
            }
            return name;
        }
        Zone['wtfZoneSpec'] = !wtfEnabled ? null : new WtfZoneSpec();
    })(typeof window === 'object' && window || typeof self === 'object' && self || global);
})));
