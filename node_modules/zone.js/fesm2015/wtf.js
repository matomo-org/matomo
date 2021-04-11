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
/**
 * @fileoverview
 * @suppress {missingRequire}
 */
(function (global) {
    // Detect and setup WTF.
    let wtfTrace = null;
    let wtfEvents = null;
    const wtfEnabled = (function () {
        const wtf = global['wtf'];
        if (wtf) {
            wtfTrace = wtf.trace;
            if (wtfTrace) {
                wtfEvents = wtfTrace.events;
                return true;
            }
        }
        return false;
    })();
    class WtfZoneSpec {
        constructor() {
            this.name = 'WTF';
        }
        onFork(parentZoneDelegate, currentZone, targetZone, zoneSpec) {
            const retValue = parentZoneDelegate.fork(targetZone, zoneSpec);
            WtfZoneSpec.forkInstance(zonePathName(targetZone), retValue.name);
            return retValue;
        }
        onInvoke(parentZoneDelegate, currentZone, targetZone, delegate, applyThis, applyArgs, source) {
            const src = source || 'unknown';
            let scope = WtfZoneSpec.invokeScope[src];
            if (!scope) {
                scope = WtfZoneSpec.invokeScope[src] =
                    wtfEvents.createScope(`Zone:invoke:${source}(ascii zone)`);
            }
            return wtfTrace.leaveScope(scope(zonePathName(targetZone)), parentZoneDelegate.invoke(targetZone, delegate, applyThis, applyArgs, source));
        }
        onHandleError(parentZoneDelegate, currentZone, targetZone, error) {
            return parentZoneDelegate.handleError(targetZone, error);
        }
        onScheduleTask(parentZoneDelegate, currentZone, targetZone, task) {
            const key = task.type + ':' + task.source;
            let instance = WtfZoneSpec.scheduleInstance[key];
            if (!instance) {
                instance = WtfZoneSpec.scheduleInstance[key] =
                    wtfEvents.createInstance(`Zone:schedule:${key}(ascii zone, any data)`);
            }
            const retValue = parentZoneDelegate.scheduleTask(targetZone, task);
            instance(zonePathName(targetZone), shallowObj(task.data, 2));
            return retValue;
        }
        onInvokeTask(parentZoneDelegate, currentZone, targetZone, task, applyThis, applyArgs) {
            const source = task.source;
            let scope = WtfZoneSpec.invokeTaskScope[source];
            if (!scope) {
                scope = WtfZoneSpec.invokeTaskScope[source] =
                    wtfEvents.createScope(`Zone:invokeTask:${source}(ascii zone)`);
            }
            return wtfTrace.leaveScope(scope(zonePathName(targetZone)), parentZoneDelegate.invokeTask(targetZone, task, applyThis, applyArgs));
        }
        onCancelTask(parentZoneDelegate, currentZone, targetZone, task) {
            const key = task.source;
            let instance = WtfZoneSpec.cancelInstance[key];
            if (!instance) {
                instance = WtfZoneSpec.cancelInstance[key] =
                    wtfEvents.createInstance(`Zone:cancel:${key}(ascii zone, any options)`);
            }
            const retValue = parentZoneDelegate.cancelTask(targetZone, task);
            instance(zonePathName(targetZone), shallowObj(task.data, 2));
            return retValue;
        }
    }
    WtfZoneSpec.forkInstance = wtfEnabled ? wtfEvents.createInstance('Zone:fork(ascii zone, ascii newZone)') : null;
    WtfZoneSpec.scheduleInstance = {};
    WtfZoneSpec.cancelInstance = {};
    WtfZoneSpec.invokeScope = {};
    WtfZoneSpec.invokeTaskScope = {};
    function shallowObj(obj, depth) {
        if (!obj || !depth)
            return null;
        const out = {};
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                // explicit : any due to https://github.com/microsoft/TypeScript/issues/33191
                let value = obj[key];
                switch (typeof value) {
                    case 'object':
                        const name = value && value.constructor && value.constructor.name;
                        value = name == Object.name ? shallowObj(value, depth - 1) : name;
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
        let name = zone.name;
        let localZone = zone.parent;
        while (localZone != null) {
            name = localZone.name + '::' + name;
            localZone = localZone.parent;
        }
        return name;
    }
    Zone['wtfZoneSpec'] = !wtfEnabled ? null : new WtfZoneSpec();
})(typeof window === 'object' && window || typeof self === 'object' && self || global);
