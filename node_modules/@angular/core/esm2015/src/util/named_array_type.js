/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import './ng_dev_mode';
import { newTrustedFunctionForDev } from './security/trusted_types';
/**
 * THIS FILE CONTAINS CODE WHICH SHOULD BE TREE SHAKEN AND NEVER CALLED FROM PRODUCTION CODE!!!
 */
/**
 * Creates an `Array` construction with a given name. This is useful when
 * looking for memory consumption to see what time of array it is.
 *
 *
 * @param name Name to give to the constructor
 * @returns A subclass of `Array` if possible. This can only be done in
 *          environments which support `class` construct.
 */
export function createNamedArrayType(name) {
    // This should never be called in prod mode, so let's verify that is the case.
    if (ngDevMode) {
        try {
            // If this function were compromised the following could lead to arbitrary
            // script execution. We bless it with Trusted Types anyway since this
            // function is stripped out of production binaries.
            return (newTrustedFunctionForDev('Array', `return class ${name} extends Array{}`))(Array);
        }
        catch (e) {
            // If it does not work just give up and fall back to regular Array.
            return Array;
        }
    }
    else {
        throw new Error('Looks like we are in \'prod mode\', but we are creating a named Array type, which is wrong! Check your code');
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmFtZWRfYXJyYXlfdHlwZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3V0aWwvbmFtZWRfYXJyYXlfdHlwZS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFDQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLGVBQWUsQ0FBQztBQUN2QixPQUFPLEVBQUMsd0JBQXdCLEVBQUMsTUFBTSwwQkFBMEIsQ0FBQztBQUVsRTs7R0FFRztBQUdIOzs7Ozs7OztHQVFHO0FBQ0gsTUFBTSxVQUFVLG9CQUFvQixDQUFDLElBQVk7SUFDL0MsOEVBQThFO0lBQzlFLElBQUksU0FBUyxFQUFFO1FBQ2IsSUFBSTtZQUNGLDBFQUEwRTtZQUMxRSxxRUFBcUU7WUFDckUsbURBQW1EO1lBQ25ELE9BQU8sQ0FBQyx3QkFBd0IsQ0FBQyxPQUFPLEVBQUUsZ0JBQWdCLElBQUksa0JBQWtCLENBQUMsQ0FBQyxDQUFDLEtBQUssQ0FBQyxDQUFDO1NBQzNGO1FBQUMsT0FBTyxDQUFDLEVBQUU7WUFDVixtRUFBbUU7WUFDbkUsT0FBTyxLQUFLLENBQUM7U0FDZDtLQUNGO1NBQU07UUFDTCxNQUFNLElBQUksS0FBSyxDQUNYLDZHQUE2RyxDQUFDLENBQUM7S0FDcEg7QUFDSCxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiXG4vKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICcuL25nX2Rldl9tb2RlJztcbmltcG9ydCB7bmV3VHJ1c3RlZEZ1bmN0aW9uRm9yRGV2fSBmcm9tICcuL3NlY3VyaXR5L3RydXN0ZWRfdHlwZXMnO1xuXG4vKipcbiAqIFRISVMgRklMRSBDT05UQUlOUyBDT0RFIFdISUNIIFNIT1VMRCBCRSBUUkVFIFNIQUtFTiBBTkQgTkVWRVIgQ0FMTEVEIEZST00gUFJPRFVDVElPTiBDT0RFISEhXG4gKi9cblxuXG4vKipcbiAqIENyZWF0ZXMgYW4gYEFycmF5YCBjb25zdHJ1Y3Rpb24gd2l0aCBhIGdpdmVuIG5hbWUuIFRoaXMgaXMgdXNlZnVsIHdoZW5cbiAqIGxvb2tpbmcgZm9yIG1lbW9yeSBjb25zdW1wdGlvbiB0byBzZWUgd2hhdCB0aW1lIG9mIGFycmF5IGl0IGlzLlxuICpcbiAqXG4gKiBAcGFyYW0gbmFtZSBOYW1lIHRvIGdpdmUgdG8gdGhlIGNvbnN0cnVjdG9yXG4gKiBAcmV0dXJucyBBIHN1YmNsYXNzIG9mIGBBcnJheWAgaWYgcG9zc2libGUuIFRoaXMgY2FuIG9ubHkgYmUgZG9uZSBpblxuICogICAgICAgICAgZW52aXJvbm1lbnRzIHdoaWNoIHN1cHBvcnQgYGNsYXNzYCBjb25zdHJ1Y3QuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBjcmVhdGVOYW1lZEFycmF5VHlwZShuYW1lOiBzdHJpbmcpOiB0eXBlb2YgQXJyYXkge1xuICAvLyBUaGlzIHNob3VsZCBuZXZlciBiZSBjYWxsZWQgaW4gcHJvZCBtb2RlLCBzbyBsZXQncyB2ZXJpZnkgdGhhdCBpcyB0aGUgY2FzZS5cbiAgaWYgKG5nRGV2TW9kZSkge1xuICAgIHRyeSB7XG4gICAgICAvLyBJZiB0aGlzIGZ1bmN0aW9uIHdlcmUgY29tcHJvbWlzZWQgdGhlIGZvbGxvd2luZyBjb3VsZCBsZWFkIHRvIGFyYml0cmFyeVxuICAgICAgLy8gc2NyaXB0IGV4ZWN1dGlvbi4gV2UgYmxlc3MgaXQgd2l0aCBUcnVzdGVkIFR5cGVzIGFueXdheSBzaW5jZSB0aGlzXG4gICAgICAvLyBmdW5jdGlvbiBpcyBzdHJpcHBlZCBvdXQgb2YgcHJvZHVjdGlvbiBiaW5hcmllcy5cbiAgICAgIHJldHVybiAobmV3VHJ1c3RlZEZ1bmN0aW9uRm9yRGV2KCdBcnJheScsIGByZXR1cm4gY2xhc3MgJHtuYW1lfSBleHRlbmRzIEFycmF5e31gKSkoQXJyYXkpO1xuICAgIH0gY2F0Y2ggKGUpIHtcbiAgICAgIC8vIElmIGl0IGRvZXMgbm90IHdvcmsganVzdCBnaXZlIHVwIGFuZCBmYWxsIGJhY2sgdG8gcmVndWxhciBBcnJheS5cbiAgICAgIHJldHVybiBBcnJheTtcbiAgICB9XG4gIH0gZWxzZSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAnTG9va3MgbGlrZSB3ZSBhcmUgaW4gXFwncHJvZCBtb2RlXFwnLCBidXQgd2UgYXJlIGNyZWF0aW5nIGEgbmFtZWQgQXJyYXkgdHlwZSwgd2hpY2ggaXMgd3JvbmchIENoZWNrIHlvdXIgY29kZScpO1xuICB9XG59XG4iXX0=