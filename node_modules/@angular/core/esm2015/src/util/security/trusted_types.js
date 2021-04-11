/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * @fileoverview
 * A module to facilitate use of a Trusted Types policy internally within
 * Angular. It lazily constructs the Trusted Types policy, providing helper
 * utilities for promoting strings to Trusted Types. When Trusted Types are not
 * available, strings are used as a fallback.
 * @security All use of this module is security-sensitive and should go through
 * security review.
 */
import { global } from '../global';
/**
 * The Trusted Types policy, or null if Trusted Types are not
 * enabled/supported, or undefined if the policy has not been created yet.
 */
let policy;
/**
 * Returns the Trusted Types policy, or null if Trusted Types are not
 * enabled/supported. The first call to this function will create the policy.
 */
function getPolicy() {
    if (policy === undefined) {
        policy = null;
        if (global.trustedTypes) {
            try {
                policy = global.trustedTypes.createPolicy('angular', {
                    createHTML: (s) => s,
                    createScript: (s) => s,
                    createScriptURL: (s) => s,
                });
            }
            catch (_a) {
                // trustedTypes.createPolicy throws if called with a name that is
                // already registered, even in report-only mode. Until the API changes,
                // catch the error not to break the applications functionally. In such
                // cases, the code will fall back to using strings.
            }
        }
    }
    return policy;
}
/**
 * Unsafely promote a string to a TrustedHTML, falling back to strings when
 * Trusted Types are not available.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that the
 * provided string will never cause an XSS vulnerability if used in a context
 * that will be interpreted as HTML by a browser, e.g. when assigning to
 * element.innerHTML.
 */
export function trustedHTMLFromString(html) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createHTML(html)) || html;
}
/**
 * Unsafely promote a string to a TrustedScript, falling back to strings when
 * Trusted Types are not available.
 * @security In particular, it must be assured that the provided string will
 * never cause an XSS vulnerability if used in a context that will be
 * interpreted and executed as a script by a browser, e.g. when calling eval.
 */
export function trustedScriptFromString(script) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createScript(script)) || script;
}
/**
 * Unsafely promote a string to a TrustedScriptURL, falling back to strings
 * when Trusted Types are not available.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that the
 * provided string will never cause an XSS vulnerability if used in a context
 * that will cause a browser to load and execute a resource, e.g. when
 * assigning to script.src.
 */
export function trustedScriptURLFromString(url) {
    var _a;
    return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createScriptURL(url)) || url;
}
/**
 * Unsafely call the Function constructor with the given string arguments. It
 * is only available in development mode, and should be stripped out of
 * production code.
 * @security This is a security-sensitive function; any use of this function
 * must go through security review. In particular, it must be assured that it
 * is only called from development code, as use in production code can lead to
 * XSS vulnerabilities.
 */
export function newTrustedFunctionForDev(...args) {
    if (typeof ngDevMode === 'undefined') {
        throw new Error('newTrustedFunctionForDev should never be called in production');
    }
    if (!global.trustedTypes) {
        // In environments that don't support Trusted Types, fall back to the most
        // straightforward implementation:
        return new Function(...args);
    }
    // Chrome currently does not support passing TrustedScript to the Function
    // constructor. The following implements the workaround proposed on the page
    // below, where the Chromium bug is also referenced:
    // https://github.com/w3c/webappsec-trusted-types/wiki/Trusted-Types-for-function-constructor
    const fnArgs = args.slice(0, -1).join(',');
    const fnBody = args[args.length - 1];
    const body = `(function anonymous(${fnArgs}
) { ${fnBody}
})`;
    // Using eval directly confuses the compiler and prevents this module from
    // being stripped out of JS binaries even if not used. The global['eval']
    // indirection fixes that.
    const fn = global['eval'](trustedScriptFromString(body));
    if (fn.bind === undefined) {
        // Workaround for a browser bug that only exists in Chrome 83, where passing
        // a TrustedScript to eval just returns the TrustedScript back without
        // evaluating it. In that case, fall back to the most straightforward
        // implementation:
        return new Function(...args);
    }
    // To completely mimic the behavior of calling "new Function", two more
    // things need to happen:
    // 1. Stringifying the resulting function should return its source code
    fn.toString = () => body;
    // 2. When calling the resulting function, `this` should refer to `global`
    return fn.bind(global);
    // When Trusted Types support in Function constructors is widely available,
    // the implementation of this function can be simplified to:
    // return new Function(...args.map(a => trustedScriptFromString(a)));
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJ1c3RlZF90eXBlcy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL3V0aWwvc2VjdXJpdHkvdHJ1c3RlZF90eXBlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSDs7Ozs7Ozs7R0FRRztBQUVILE9BQU8sRUFBQyxNQUFNLEVBQUMsTUFBTSxXQUFXLENBQUM7QUFHakM7OztHQUdHO0FBQ0gsSUFBSSxNQUF3QyxDQUFDO0FBRTdDOzs7R0FHRztBQUNILFNBQVMsU0FBUztJQUNoQixJQUFJLE1BQU0sS0FBSyxTQUFTLEVBQUU7UUFDeEIsTUFBTSxHQUFHLElBQUksQ0FBQztRQUNkLElBQUksTUFBTSxDQUFDLFlBQVksRUFBRTtZQUN2QixJQUFJO2dCQUNGLE1BQU0sR0FBSSxNQUFNLENBQUMsWUFBeUMsQ0FBQyxZQUFZLENBQUMsU0FBUyxFQUFFO29CQUNqRixVQUFVLEVBQUUsQ0FBQyxDQUFTLEVBQUUsRUFBRSxDQUFDLENBQUM7b0JBQzVCLFlBQVksRUFBRSxDQUFDLENBQVMsRUFBRSxFQUFFLENBQUMsQ0FBQztvQkFDOUIsZUFBZSxFQUFFLENBQUMsQ0FBUyxFQUFFLEVBQUUsQ0FBQyxDQUFDO2lCQUNsQyxDQUFDLENBQUM7YUFDSjtZQUFDLFdBQU07Z0JBQ04saUVBQWlFO2dCQUNqRSx1RUFBdUU7Z0JBQ3ZFLHNFQUFzRTtnQkFDdEUsbURBQW1EO2FBQ3BEO1NBQ0Y7S0FDRjtJQUNELE9BQU8sTUFBTSxDQUFDO0FBQ2hCLENBQUM7QUFFRDs7Ozs7Ozs7R0FRRztBQUNILE1BQU0sVUFBVSxxQkFBcUIsQ0FBQyxJQUFZOztJQUNoRCxPQUFPLE9BQUEsU0FBUyxFQUFFLDBDQUFFLFVBQVUsQ0FBQyxJQUFJLE1BQUssSUFBSSxDQUFDO0FBQy9DLENBQUM7QUFFRDs7Ozs7O0dBTUc7QUFDSCxNQUFNLFVBQVUsdUJBQXVCLENBQUMsTUFBYzs7SUFDcEQsT0FBTyxPQUFBLFNBQVMsRUFBRSwwQ0FBRSxZQUFZLENBQUMsTUFBTSxNQUFLLE1BQU0sQ0FBQztBQUNyRCxDQUFDO0FBRUQ7Ozs7Ozs7O0dBUUc7QUFDSCxNQUFNLFVBQVUsMEJBQTBCLENBQUMsR0FBVzs7SUFDcEQsT0FBTyxPQUFBLFNBQVMsRUFBRSwwQ0FBRSxlQUFlLENBQUMsR0FBRyxNQUFLLEdBQUcsQ0FBQztBQUNsRCxDQUFDO0FBRUQ7Ozs7Ozs7O0dBUUc7QUFDSCxNQUFNLFVBQVUsd0JBQXdCLENBQUMsR0FBRyxJQUFjO0lBQ3hELElBQUksT0FBTyxTQUFTLEtBQUssV0FBVyxFQUFFO1FBQ3BDLE1BQU0sSUFBSSxLQUFLLENBQUMsK0RBQStELENBQUMsQ0FBQztLQUNsRjtJQUNELElBQUksQ0FBQyxNQUFNLENBQUMsWUFBWSxFQUFFO1FBQ3hCLDBFQUEwRTtRQUMxRSxrQ0FBa0M7UUFDbEMsT0FBTyxJQUFJLFFBQVEsQ0FBQyxHQUFHLElBQUksQ0FBQyxDQUFDO0tBQzlCO0lBRUQsMEVBQTBFO0lBQzFFLDRFQUE0RTtJQUM1RSxvREFBb0Q7SUFDcEQsNkZBQTZGO0lBQzdGLE1BQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO0lBQzNDLE1BQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxDQUFDO0lBQ3JDLE1BQU0sSUFBSSxHQUFHLHVCQUF1QixNQUFNO01BQ3RDLE1BQU07R0FDVCxDQUFDO0lBRUYsMEVBQTBFO0lBQzFFLHlFQUF5RTtJQUN6RSwwQkFBMEI7SUFDMUIsTUFBTSxFQUFFLEdBQUcsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDLHVCQUF1QixDQUFDLElBQUksQ0FBVyxDQUFhLENBQUM7SUFDL0UsSUFBSSxFQUFFLENBQUMsSUFBSSxLQUFLLFNBQVMsRUFBRTtRQUN6Qiw0RUFBNEU7UUFDNUUsc0VBQXNFO1FBQ3RFLHFFQUFxRTtRQUNyRSxrQkFBa0I7UUFDbEIsT0FBTyxJQUFJLFFBQVEsQ0FBQyxHQUFHLElBQUksQ0FBQyxDQUFDO0tBQzlCO0lBRUQsdUVBQXVFO0lBQ3ZFLHlCQUF5QjtJQUN6Qix1RUFBdUU7SUFDdkUsRUFBRSxDQUFDLFFBQVEsR0FBRyxHQUFHLEVBQUUsQ0FBQyxJQUFJLENBQUM7SUFDekIsMEVBQTBFO0lBQzFFLE9BQU8sRUFBRSxDQUFDLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztJQUV2QiwyRUFBMkU7SUFDM0UsNERBQTREO0lBQzVELHFFQUFxRTtBQUN2RSxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogQGZpbGVvdmVydmlld1xuICogQSBtb2R1bGUgdG8gZmFjaWxpdGF0ZSB1c2Ugb2YgYSBUcnVzdGVkIFR5cGVzIHBvbGljeSBpbnRlcm5hbGx5IHdpdGhpblxuICogQW5ndWxhci4gSXQgbGF6aWx5IGNvbnN0cnVjdHMgdGhlIFRydXN0ZWQgVHlwZXMgcG9saWN5LCBwcm92aWRpbmcgaGVscGVyXG4gKiB1dGlsaXRpZXMgZm9yIHByb21vdGluZyBzdHJpbmdzIHRvIFRydXN0ZWQgVHlwZXMuIFdoZW4gVHJ1c3RlZCBUeXBlcyBhcmUgbm90XG4gKiBhdmFpbGFibGUsIHN0cmluZ3MgYXJlIHVzZWQgYXMgYSBmYWxsYmFjay5cbiAqIEBzZWN1cml0eSBBbGwgdXNlIG9mIHRoaXMgbW9kdWxlIGlzIHNlY3VyaXR5LXNlbnNpdGl2ZSBhbmQgc2hvdWxkIGdvIHRocm91Z2hcbiAqIHNlY3VyaXR5IHJldmlldy5cbiAqL1xuXG5pbXBvcnQge2dsb2JhbH0gZnJvbSAnLi4vZ2xvYmFsJztcbmltcG9ydCB7VHJ1c3RlZEhUTUwsIFRydXN0ZWRTY3JpcHQsIFRydXN0ZWRTY3JpcHRVUkwsIFRydXN0ZWRUeXBlUG9saWN5LCBUcnVzdGVkVHlwZVBvbGljeUZhY3Rvcnl9IGZyb20gJy4vdHJ1c3RlZF90eXBlX2RlZnMnO1xuXG4vKipcbiAqIFRoZSBUcnVzdGVkIFR5cGVzIHBvbGljeSwgb3IgbnVsbCBpZiBUcnVzdGVkIFR5cGVzIGFyZSBub3RcbiAqIGVuYWJsZWQvc3VwcG9ydGVkLCBvciB1bmRlZmluZWQgaWYgdGhlIHBvbGljeSBoYXMgbm90IGJlZW4gY3JlYXRlZCB5ZXQuXG4gKi9cbmxldCBwb2xpY3k6IFRydXN0ZWRUeXBlUG9saWN5fG51bGx8dW5kZWZpbmVkO1xuXG4vKipcbiAqIFJldHVybnMgdGhlIFRydXN0ZWQgVHlwZXMgcG9saWN5LCBvciBudWxsIGlmIFRydXN0ZWQgVHlwZXMgYXJlIG5vdFxuICogZW5hYmxlZC9zdXBwb3J0ZWQuIFRoZSBmaXJzdCBjYWxsIHRvIHRoaXMgZnVuY3Rpb24gd2lsbCBjcmVhdGUgdGhlIHBvbGljeS5cbiAqL1xuZnVuY3Rpb24gZ2V0UG9saWN5KCk6IFRydXN0ZWRUeXBlUG9saWN5fG51bGwge1xuICBpZiAocG9saWN5ID09PSB1bmRlZmluZWQpIHtcbiAgICBwb2xpY3kgPSBudWxsO1xuICAgIGlmIChnbG9iYWwudHJ1c3RlZFR5cGVzKSB7XG4gICAgICB0cnkge1xuICAgICAgICBwb2xpY3kgPSAoZ2xvYmFsLnRydXN0ZWRUeXBlcyBhcyBUcnVzdGVkVHlwZVBvbGljeUZhY3RvcnkpLmNyZWF0ZVBvbGljeSgnYW5ndWxhcicsIHtcbiAgICAgICAgICBjcmVhdGVIVE1MOiAoczogc3RyaW5nKSA9PiBzLFxuICAgICAgICAgIGNyZWF0ZVNjcmlwdDogKHM6IHN0cmluZykgPT4gcyxcbiAgICAgICAgICBjcmVhdGVTY3JpcHRVUkw6IChzOiBzdHJpbmcpID0+IHMsXG4gICAgICAgIH0pO1xuICAgICAgfSBjYXRjaCB7XG4gICAgICAgIC8vIHRydXN0ZWRUeXBlcy5jcmVhdGVQb2xpY3kgdGhyb3dzIGlmIGNhbGxlZCB3aXRoIGEgbmFtZSB0aGF0IGlzXG4gICAgICAgIC8vIGFscmVhZHkgcmVnaXN0ZXJlZCwgZXZlbiBpbiByZXBvcnQtb25seSBtb2RlLiBVbnRpbCB0aGUgQVBJIGNoYW5nZXMsXG4gICAgICAgIC8vIGNhdGNoIHRoZSBlcnJvciBub3QgdG8gYnJlYWsgdGhlIGFwcGxpY2F0aW9ucyBmdW5jdGlvbmFsbHkuIEluIHN1Y2hcbiAgICAgICAgLy8gY2FzZXMsIHRoZSBjb2RlIHdpbGwgZmFsbCBiYWNrIHRvIHVzaW5nIHN0cmluZ3MuXG4gICAgICB9XG4gICAgfVxuICB9XG4gIHJldHVybiBwb2xpY3k7XG59XG5cbi8qKlxuICogVW5zYWZlbHkgcHJvbW90ZSBhIHN0cmluZyB0byBhIFRydXN0ZWRIVE1MLCBmYWxsaW5nIGJhY2sgdG8gc3RyaW5ncyB3aGVuXG4gKiBUcnVzdGVkIFR5cGVzIGFyZSBub3QgYXZhaWxhYmxlLlxuICogQHNlY3VyaXR5IFRoaXMgaXMgYSBzZWN1cml0eS1zZW5zaXRpdmUgZnVuY3Rpb247IGFueSB1c2Ugb2YgdGhpcyBmdW5jdGlvblxuICogbXVzdCBnbyB0aHJvdWdoIHNlY3VyaXR5IHJldmlldy4gSW4gcGFydGljdWxhciwgaXQgbXVzdCBiZSBhc3N1cmVkIHRoYXQgdGhlXG4gKiBwcm92aWRlZCBzdHJpbmcgd2lsbCBuZXZlciBjYXVzZSBhbiBYU1MgdnVsbmVyYWJpbGl0eSBpZiB1c2VkIGluIGEgY29udGV4dFxuICogdGhhdCB3aWxsIGJlIGludGVycHJldGVkIGFzIEhUTUwgYnkgYSBicm93c2VyLCBlLmcuIHdoZW4gYXNzaWduaW5nIHRvXG4gKiBlbGVtZW50LmlubmVySFRNTC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRydXN0ZWRIVE1MRnJvbVN0cmluZyhodG1sOiBzdHJpbmcpOiBUcnVzdGVkSFRNTHxzdHJpbmcge1xuICByZXR1cm4gZ2V0UG9saWN5KCk/LmNyZWF0ZUhUTUwoaHRtbCkgfHwgaHRtbDtcbn1cblxuLyoqXG4gKiBVbnNhZmVseSBwcm9tb3RlIGEgc3RyaW5nIHRvIGEgVHJ1c3RlZFNjcmlwdCwgZmFsbGluZyBiYWNrIHRvIHN0cmluZ3Mgd2hlblxuICogVHJ1c3RlZCBUeXBlcyBhcmUgbm90IGF2YWlsYWJsZS5cbiAqIEBzZWN1cml0eSBJbiBwYXJ0aWN1bGFyLCBpdCBtdXN0IGJlIGFzc3VyZWQgdGhhdCB0aGUgcHJvdmlkZWQgc3RyaW5nIHdpbGxcbiAqIG5ldmVyIGNhdXNlIGFuIFhTUyB2dWxuZXJhYmlsaXR5IGlmIHVzZWQgaW4gYSBjb250ZXh0IHRoYXQgd2lsbCBiZVxuICogaW50ZXJwcmV0ZWQgYW5kIGV4ZWN1dGVkIGFzIGEgc2NyaXB0IGJ5IGEgYnJvd3NlciwgZS5nLiB3aGVuIGNhbGxpbmcgZXZhbC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRydXN0ZWRTY3JpcHRGcm9tU3RyaW5nKHNjcmlwdDogc3RyaW5nKTogVHJ1c3RlZFNjcmlwdHxzdHJpbmcge1xuICByZXR1cm4gZ2V0UG9saWN5KCk/LmNyZWF0ZVNjcmlwdChzY3JpcHQpIHx8IHNjcmlwdDtcbn1cblxuLyoqXG4gKiBVbnNhZmVseSBwcm9tb3RlIGEgc3RyaW5nIHRvIGEgVHJ1c3RlZFNjcmlwdFVSTCwgZmFsbGluZyBiYWNrIHRvIHN0cmluZ3NcbiAqIHdoZW4gVHJ1c3RlZCBUeXBlcyBhcmUgbm90IGF2YWlsYWJsZS5cbiAqIEBzZWN1cml0eSBUaGlzIGlzIGEgc2VjdXJpdHktc2Vuc2l0aXZlIGZ1bmN0aW9uOyBhbnkgdXNlIG9mIHRoaXMgZnVuY3Rpb25cbiAqIG11c3QgZ28gdGhyb3VnaCBzZWN1cml0eSByZXZpZXcuIEluIHBhcnRpY3VsYXIsIGl0IG11c3QgYmUgYXNzdXJlZCB0aGF0IHRoZVxuICogcHJvdmlkZWQgc3RyaW5nIHdpbGwgbmV2ZXIgY2F1c2UgYW4gWFNTIHZ1bG5lcmFiaWxpdHkgaWYgdXNlZCBpbiBhIGNvbnRleHRcbiAqIHRoYXQgd2lsbCBjYXVzZSBhIGJyb3dzZXIgdG8gbG9hZCBhbmQgZXhlY3V0ZSBhIHJlc291cmNlLCBlLmcuIHdoZW5cbiAqIGFzc2lnbmluZyB0byBzY3JpcHQuc3JjLlxuICovXG5leHBvcnQgZnVuY3Rpb24gdHJ1c3RlZFNjcmlwdFVSTEZyb21TdHJpbmcodXJsOiBzdHJpbmcpOiBUcnVzdGVkU2NyaXB0VVJMfHN0cmluZyB7XG4gIHJldHVybiBnZXRQb2xpY3koKT8uY3JlYXRlU2NyaXB0VVJMKHVybCkgfHwgdXJsO1xufVxuXG4vKipcbiAqIFVuc2FmZWx5IGNhbGwgdGhlIEZ1bmN0aW9uIGNvbnN0cnVjdG9yIHdpdGggdGhlIGdpdmVuIHN0cmluZyBhcmd1bWVudHMuIEl0XG4gKiBpcyBvbmx5IGF2YWlsYWJsZSBpbiBkZXZlbG9wbWVudCBtb2RlLCBhbmQgc2hvdWxkIGJlIHN0cmlwcGVkIG91dCBvZlxuICogcHJvZHVjdGlvbiBjb2RlLlxuICogQHNlY3VyaXR5IFRoaXMgaXMgYSBzZWN1cml0eS1zZW5zaXRpdmUgZnVuY3Rpb247IGFueSB1c2Ugb2YgdGhpcyBmdW5jdGlvblxuICogbXVzdCBnbyB0aHJvdWdoIHNlY3VyaXR5IHJldmlldy4gSW4gcGFydGljdWxhciwgaXQgbXVzdCBiZSBhc3N1cmVkIHRoYXQgaXRcbiAqIGlzIG9ubHkgY2FsbGVkIGZyb20gZGV2ZWxvcG1lbnQgY29kZSwgYXMgdXNlIGluIHByb2R1Y3Rpb24gY29kZSBjYW4gbGVhZCB0b1xuICogWFNTIHZ1bG5lcmFiaWxpdGllcy5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIG5ld1RydXN0ZWRGdW5jdGlvbkZvckRldiguLi5hcmdzOiBzdHJpbmdbXSk6IEZ1bmN0aW9uIHtcbiAgaWYgKHR5cGVvZiBuZ0Rldk1vZGUgPT09ICd1bmRlZmluZWQnKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCduZXdUcnVzdGVkRnVuY3Rpb25Gb3JEZXYgc2hvdWxkIG5ldmVyIGJlIGNhbGxlZCBpbiBwcm9kdWN0aW9uJyk7XG4gIH1cbiAgaWYgKCFnbG9iYWwudHJ1c3RlZFR5cGVzKSB7XG4gICAgLy8gSW4gZW52aXJvbm1lbnRzIHRoYXQgZG9uJ3Qgc3VwcG9ydCBUcnVzdGVkIFR5cGVzLCBmYWxsIGJhY2sgdG8gdGhlIG1vc3RcbiAgICAvLyBzdHJhaWdodGZvcndhcmQgaW1wbGVtZW50YXRpb246XG4gICAgcmV0dXJuIG5ldyBGdW5jdGlvbiguLi5hcmdzKTtcbiAgfVxuXG4gIC8vIENocm9tZSBjdXJyZW50bHkgZG9lcyBub3Qgc3VwcG9ydCBwYXNzaW5nIFRydXN0ZWRTY3JpcHQgdG8gdGhlIEZ1bmN0aW9uXG4gIC8vIGNvbnN0cnVjdG9yLiBUaGUgZm9sbG93aW5nIGltcGxlbWVudHMgdGhlIHdvcmthcm91bmQgcHJvcG9zZWQgb24gdGhlIHBhZ2VcbiAgLy8gYmVsb3csIHdoZXJlIHRoZSBDaHJvbWl1bSBidWcgaXMgYWxzbyByZWZlcmVuY2VkOlxuICAvLyBodHRwczovL2dpdGh1Yi5jb20vdzNjL3dlYmFwcHNlYy10cnVzdGVkLXR5cGVzL3dpa2kvVHJ1c3RlZC1UeXBlcy1mb3ItZnVuY3Rpb24tY29uc3RydWN0b3JcbiAgY29uc3QgZm5BcmdzID0gYXJncy5zbGljZSgwLCAtMSkuam9pbignLCcpO1xuICBjb25zdCBmbkJvZHkgPSBhcmdzW2FyZ3MubGVuZ3RoIC0gMV07XG4gIGNvbnN0IGJvZHkgPSBgKGZ1bmN0aW9uIGFub255bW91cygke2ZuQXJnc31cbikgeyAke2ZuQm9keX1cbn0pYDtcblxuICAvLyBVc2luZyBldmFsIGRpcmVjdGx5IGNvbmZ1c2VzIHRoZSBjb21waWxlciBhbmQgcHJldmVudHMgdGhpcyBtb2R1bGUgZnJvbVxuICAvLyBiZWluZyBzdHJpcHBlZCBvdXQgb2YgSlMgYmluYXJpZXMgZXZlbiBpZiBub3QgdXNlZC4gVGhlIGdsb2JhbFsnZXZhbCddXG4gIC8vIGluZGlyZWN0aW9uIGZpeGVzIHRoYXQuXG4gIGNvbnN0IGZuID0gZ2xvYmFsWydldmFsJ10odHJ1c3RlZFNjcmlwdEZyb21TdHJpbmcoYm9keSkgYXMgc3RyaW5nKSBhcyBGdW5jdGlvbjtcbiAgaWYgKGZuLmJpbmQgPT09IHVuZGVmaW5lZCkge1xuICAgIC8vIFdvcmthcm91bmQgZm9yIGEgYnJvd3NlciBidWcgdGhhdCBvbmx5IGV4aXN0cyBpbiBDaHJvbWUgODMsIHdoZXJlIHBhc3NpbmdcbiAgICAvLyBhIFRydXN0ZWRTY3JpcHQgdG8gZXZhbCBqdXN0IHJldHVybnMgdGhlIFRydXN0ZWRTY3JpcHQgYmFjayB3aXRob3V0XG4gICAgLy8gZXZhbHVhdGluZyBpdC4gSW4gdGhhdCBjYXNlLCBmYWxsIGJhY2sgdG8gdGhlIG1vc3Qgc3RyYWlnaHRmb3J3YXJkXG4gICAgLy8gaW1wbGVtZW50YXRpb246XG4gICAgcmV0dXJuIG5ldyBGdW5jdGlvbiguLi5hcmdzKTtcbiAgfVxuXG4gIC8vIFRvIGNvbXBsZXRlbHkgbWltaWMgdGhlIGJlaGF2aW9yIG9mIGNhbGxpbmcgXCJuZXcgRnVuY3Rpb25cIiwgdHdvIG1vcmVcbiAgLy8gdGhpbmdzIG5lZWQgdG8gaGFwcGVuOlxuICAvLyAxLiBTdHJpbmdpZnlpbmcgdGhlIHJlc3VsdGluZyBmdW5jdGlvbiBzaG91bGQgcmV0dXJuIGl0cyBzb3VyY2UgY29kZVxuICBmbi50b1N0cmluZyA9ICgpID0+IGJvZHk7XG4gIC8vIDIuIFdoZW4gY2FsbGluZyB0aGUgcmVzdWx0aW5nIGZ1bmN0aW9uLCBgdGhpc2Agc2hvdWxkIHJlZmVyIHRvIGBnbG9iYWxgXG4gIHJldHVybiBmbi5iaW5kKGdsb2JhbCk7XG5cbiAgLy8gV2hlbiBUcnVzdGVkIFR5cGVzIHN1cHBvcnQgaW4gRnVuY3Rpb24gY29uc3RydWN0b3JzIGlzIHdpZGVseSBhdmFpbGFibGUsXG4gIC8vIHRoZSBpbXBsZW1lbnRhdGlvbiBvZiB0aGlzIGZ1bmN0aW9uIGNhbiBiZSBzaW1wbGlmaWVkIHRvOlxuICAvLyByZXR1cm4gbmV3IEZ1bmN0aW9uKC4uLmFyZ3MubWFwKGEgPT4gdHJ1c3RlZFNjcmlwdEZyb21TdHJpbmcoYSkpKTtcbn1cbiJdfQ==