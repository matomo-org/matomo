/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/output/output_jit_trusted_types", ["require", "exports", "tslib", "@angular/compiler/src/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.newTrustedFunctionForJIT = void 0;
    var tslib_1 = require("tslib");
    /**
     * @fileoverview
     * A module to facilitate use of a Trusted Types policy within the JIT
     * compiler. It lazily constructs the Trusted Types policy, providing helper
     * utilities for promoting strings to Trusted Types. When Trusted Types are not
     * available, strings are used as a fallback.
     * @security All use of this module is security-sensitive and should go through
     * security review.
     */
    var util_1 = require("@angular/compiler/src/util");
    /**
     * The Trusted Types policy, or null if Trusted Types are not
     * enabled/supported, or undefined if the policy has not been created yet.
     */
    var policy;
    /**
     * Returns the Trusted Types policy, or null if Trusted Types are not
     * enabled/supported. The first call to this function will create the policy.
     */
    function getPolicy() {
        if (policy === undefined) {
            policy = null;
            if (util_1.global.trustedTypes) {
                try {
                    policy =
                        util_1.global.trustedTypes.createPolicy('angular#unsafe-jit', {
                            createScript: function (s) { return s; },
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
     * Unsafely promote a string to a TrustedScript, falling back to strings when
     * Trusted Types are not available.
     * @security In particular, it must be assured that the provided string will
     * never cause an XSS vulnerability if used in a context that will be
     * interpreted and executed as a script by a browser, e.g. when calling eval.
     */
    function trustedScriptFromString(script) {
        var _a;
        return ((_a = getPolicy()) === null || _a === void 0 ? void 0 : _a.createScript(script)) || script;
    }
    /**
     * Unsafely call the Function constructor with the given string arguments.
     * @security This is a security-sensitive function; any use of this function
     * must go through security review. In particular, it must be assured that it
     * is only called from the JIT compiler, as use in other code can lead to XSS
     * vulnerabilities.
     */
    function newTrustedFunctionForJIT() {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        if (!util_1.global.trustedTypes) {
            // In environments that don't support Trusted Types, fall back to the most
            // straightforward implementation:
            return new (Function.bind.apply(Function, tslib_1.__spread([void 0], args)))();
        }
        // Chrome currently does not support passing TrustedScript to the Function
        // constructor. The following implements the workaround proposed on the page
        // below, where the Chromium bug is also referenced:
        // https://github.com/w3c/webappsec-trusted-types/wiki/Trusted-Types-for-function-constructor
        var fnArgs = args.slice(0, -1).join(',');
        var fnBody = args[args.length - 1];
        var body = "(function anonymous(" + fnArgs + "\n) { " + fnBody + "\n})";
        // Using eval directly confuses the compiler and prevents this module from
        // being stripped out of JS binaries even if not used. The global['eval']
        // indirection fixes that.
        var fn = util_1.global['eval'](trustedScriptFromString(body));
        if (fn.bind === undefined) {
            // Workaround for a browser bug that only exists in Chrome 83, where passing
            // a TrustedScript to eval just returns the TrustedScript back without
            // evaluating it. In that case, fall back to the most straightforward
            // implementation:
            return new (Function.bind.apply(Function, tslib_1.__spread([void 0], args)))();
        }
        // To completely mimic the behavior of calling "new Function", two more
        // things need to happen:
        // 1. Stringifying the resulting function should return its source code
        fn.toString = function () { return body; };
        // 2. When calling the resulting function, `this` should refer to `global`
        return fn.bind(util_1.global);
        // When Trusted Types support in Function constructors is widely available,
        // the implementation of this function can be simplified to:
        // return new Function(...args.map(a => trustedScriptFromString(a)));
    }
    exports.newTrustedFunctionForJIT = newTrustedFunctionForJIT;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoib3V0cHV0X2ppdF90cnVzdGVkX3R5cGVzLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL291dHB1dC9vdXRwdXRfaml0X3RydXN0ZWRfdHlwZXMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7OztJQUVIOzs7Ozs7OztPQVFHO0lBRUgsbURBQStCO0lBbUMvQjs7O09BR0c7SUFDSCxJQUFJLE1BQXdDLENBQUM7SUFFN0M7OztPQUdHO0lBQ0gsU0FBUyxTQUFTO1FBQ2hCLElBQUksTUFBTSxLQUFLLFNBQVMsRUFBRTtZQUN4QixNQUFNLEdBQUcsSUFBSSxDQUFDO1lBQ2QsSUFBSSxhQUFNLENBQUMsWUFBWSxFQUFFO2dCQUN2QixJQUFJO29CQUNGLE1BQU07d0JBQ0QsYUFBTSxDQUFDLFlBQXlDLENBQUMsWUFBWSxDQUFDLG9CQUFvQixFQUFFOzRCQUNuRixZQUFZLEVBQUUsVUFBQyxDQUFTLElBQUssT0FBQSxDQUFDLEVBQUQsQ0FBQzt5QkFDL0IsQ0FBQyxDQUFDO2lCQUNSO2dCQUFDLFdBQU07b0JBQ04saUVBQWlFO29CQUNqRSx1RUFBdUU7b0JBQ3ZFLHNFQUFzRTtvQkFDdEUsbURBQW1EO2lCQUNwRDthQUNGO1NBQ0Y7UUFDRCxPQUFPLE1BQU0sQ0FBQztJQUNoQixDQUFDO0lBRUQ7Ozs7OztPQU1HO0lBQ0gsU0FBUyx1QkFBdUIsQ0FBQyxNQUFjOztRQUM3QyxPQUFPLE9BQUEsU0FBUyxFQUFFLDBDQUFFLFlBQVksQ0FBQyxNQUFNLE1BQUssTUFBTSxDQUFDO0lBQ3JELENBQUM7SUFFRDs7Ozs7O09BTUc7SUFDSCxTQUFnQix3QkFBd0I7UUFBQyxjQUFpQjthQUFqQixVQUFpQixFQUFqQixxQkFBaUIsRUFBakIsSUFBaUI7WUFBakIseUJBQWlCOztRQUN4RCxJQUFJLENBQUMsYUFBTSxDQUFDLFlBQVksRUFBRTtZQUN4QiwwRUFBMEU7WUFDMUUsa0NBQWtDO1lBQ2xDLFlBQVcsUUFBUSxZQUFSLFFBQVEsNkJBQUksSUFBSSxNQUFFO1NBQzlCO1FBRUQsMEVBQTBFO1FBQzFFLDRFQUE0RTtRQUM1RSxvREFBb0Q7UUFDcEQsNkZBQTZGO1FBQzdGLElBQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDO1FBQzNDLElBQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxDQUFDO1FBQ3JDLElBQU0sSUFBSSxHQUFHLHlCQUF1QixNQUFNLGNBQ3RDLE1BQU0sU0FDVCxDQUFDO1FBRUYsMEVBQTBFO1FBQzFFLHlFQUF5RTtRQUN6RSwwQkFBMEI7UUFDMUIsSUFBTSxFQUFFLEdBQUcsYUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDLHVCQUF1QixDQUFDLElBQUksQ0FBVyxDQUFhLENBQUM7UUFDL0UsSUFBSSxFQUFFLENBQUMsSUFBSSxLQUFLLFNBQVMsRUFBRTtZQUN6Qiw0RUFBNEU7WUFDNUUsc0VBQXNFO1lBQ3RFLHFFQUFxRTtZQUNyRSxrQkFBa0I7WUFDbEIsWUFBVyxRQUFRLFlBQVIsUUFBUSw2QkFBSSxJQUFJLE1BQUU7U0FDOUI7UUFFRCx1RUFBdUU7UUFDdkUseUJBQXlCO1FBQ3pCLHVFQUF1RTtRQUN2RSxFQUFFLENBQUMsUUFBUSxHQUFHLGNBQU0sT0FBQSxJQUFJLEVBQUosQ0FBSSxDQUFDO1FBQ3pCLDBFQUEwRTtRQUMxRSxPQUFPLEVBQUUsQ0FBQyxJQUFJLENBQUMsYUFBTSxDQUFDLENBQUM7UUFFdkIsMkVBQTJFO1FBQzNFLDREQUE0RDtRQUM1RCxxRUFBcUU7SUFDdkUsQ0FBQztJQXZDRCw0REF1Q0MiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBAZmlsZW92ZXJ2aWV3XG4gKiBBIG1vZHVsZSB0byBmYWNpbGl0YXRlIHVzZSBvZiBhIFRydXN0ZWQgVHlwZXMgcG9saWN5IHdpdGhpbiB0aGUgSklUXG4gKiBjb21waWxlci4gSXQgbGF6aWx5IGNvbnN0cnVjdHMgdGhlIFRydXN0ZWQgVHlwZXMgcG9saWN5LCBwcm92aWRpbmcgaGVscGVyXG4gKiB1dGlsaXRpZXMgZm9yIHByb21vdGluZyBzdHJpbmdzIHRvIFRydXN0ZWQgVHlwZXMuIFdoZW4gVHJ1c3RlZCBUeXBlcyBhcmUgbm90XG4gKiBhdmFpbGFibGUsIHN0cmluZ3MgYXJlIHVzZWQgYXMgYSBmYWxsYmFjay5cbiAqIEBzZWN1cml0eSBBbGwgdXNlIG9mIHRoaXMgbW9kdWxlIGlzIHNlY3VyaXR5LXNlbnNpdGl2ZSBhbmQgc2hvdWxkIGdvIHRocm91Z2hcbiAqIHNlY3VyaXR5IHJldmlldy5cbiAqL1xuXG5pbXBvcnQge2dsb2JhbH0gZnJvbSAnLi4vdXRpbCc7XG5cbi8qKlxuICogV2hpbGUgQW5ndWxhciBvbmx5IHVzZXMgVHJ1c3RlZCBUeXBlcyBpbnRlcm5hbGx5IGZvciB0aGUgdGltZSBiZWluZyxcbiAqIHJlZmVyZW5jZXMgdG8gVHJ1c3RlZCBUeXBlcyBjb3VsZCBsZWFrIGludG8gb3VyIGNvcmUuZC50cywgd2hpY2ggd291bGQgZm9yY2VcbiAqIGFueW9uZSBjb21waWxpbmcgYWdhaW5zdCBAYW5ndWxhci9jb3JlIHRvIHByb3ZpZGUgdGhlIEB0eXBlcy90cnVzdGVkLXR5cGVzXG4gKiBwYWNrYWdlIGluIHRoZWlyIGNvbXBpbGF0aW9uIHVuaXQuXG4gKlxuICogVW50aWwgaHR0cHM6Ly9naXRodWIuY29tL21pY3Jvc29mdC9UeXBlU2NyaXB0L2lzc3Vlcy8zMDAyNCBpcyByZXNvbHZlZCwgd2VcbiAqIHdpbGwga2VlcCBBbmd1bGFyJ3MgcHVibGljIEFQSSBzdXJmYWNlIGZyZWUgb2YgcmVmZXJlbmNlcyB0byBUcnVzdGVkIFR5cGVzLlxuICogRm9yIGludGVybmFsIGFuZCBzZW1pLXByaXZhdGUgQVBJcyB0aGF0IG5lZWQgdG8gcmVmZXJlbmNlIFRydXN0ZWQgVHlwZXMsIHRoZVxuICogbWluaW1hbCB0eXBlIGRlZmluaXRpb25zIGZvciB0aGUgVHJ1c3RlZCBUeXBlcyBBUEkgcHJvdmlkZWQgYnkgdGhpcyBtb2R1bGVcbiAqIHNob3VsZCBiZSB1c2VkIGluc3RlYWQuIFRoZXkgYXJlIG1hcmtlZCBhcyBcImRlY2xhcmVcIiB0byBwcmV2ZW50IHRoZW0gZnJvbVxuICogYmVpbmcgcmVuYW1lZCBieSBjb21waWxlciBvcHRpbWl6YXRpb24uXG4gKlxuICogQWRhcHRlZCBmcm9tXG4gKiBodHRwczovL2dpdGh1Yi5jb20vRGVmaW5pdGVseVR5cGVkL0RlZmluaXRlbHlUeXBlZC9ibG9iL21hc3Rlci90eXBlcy90cnVzdGVkLXR5cGVzL2luZGV4LmQudHNcbiAqIGJ1dCByZXN0cmljdGVkIHRvIHRoZSBBUEkgc3VyZmFjZSB1c2VkIHdpdGhpbiBBbmd1bGFyLlxuICovXG5cbmV4cG9ydCBkZWNsYXJlIGludGVyZmFjZSBUcnVzdGVkU2NyaXB0IHtcbiAgX19icmFuZF9fOiAnVHJ1c3RlZFNjcmlwdCc7XG59XG5cbmV4cG9ydCBkZWNsYXJlIGludGVyZmFjZSBUcnVzdGVkVHlwZVBvbGljeUZhY3Rvcnkge1xuICBjcmVhdGVQb2xpY3kocG9saWN5TmFtZTogc3RyaW5nLCBwb2xpY3lPcHRpb25zOiB7XG4gICAgY3JlYXRlU2NyaXB0PzogKGlucHV0OiBzdHJpbmcpID0+IHN0cmluZyxcbiAgfSk6IFRydXN0ZWRUeXBlUG9saWN5O1xufVxuXG5leHBvcnQgZGVjbGFyZSBpbnRlcmZhY2UgVHJ1c3RlZFR5cGVQb2xpY3kge1xuICBjcmVhdGVTY3JpcHQoaW5wdXQ6IHN0cmluZyk6IFRydXN0ZWRTY3JpcHQ7XG59XG5cblxuLyoqXG4gKiBUaGUgVHJ1c3RlZCBUeXBlcyBwb2xpY3ksIG9yIG51bGwgaWYgVHJ1c3RlZCBUeXBlcyBhcmUgbm90XG4gKiBlbmFibGVkL3N1cHBvcnRlZCwgb3IgdW5kZWZpbmVkIGlmIHRoZSBwb2xpY3kgaGFzIG5vdCBiZWVuIGNyZWF0ZWQgeWV0LlxuICovXG5sZXQgcG9saWN5OiBUcnVzdGVkVHlwZVBvbGljeXxudWxsfHVuZGVmaW5lZDtcblxuLyoqXG4gKiBSZXR1cm5zIHRoZSBUcnVzdGVkIFR5cGVzIHBvbGljeSwgb3IgbnVsbCBpZiBUcnVzdGVkIFR5cGVzIGFyZSBub3RcbiAqIGVuYWJsZWQvc3VwcG9ydGVkLiBUaGUgZmlyc3QgY2FsbCB0byB0aGlzIGZ1bmN0aW9uIHdpbGwgY3JlYXRlIHRoZSBwb2xpY3kuXG4gKi9cbmZ1bmN0aW9uIGdldFBvbGljeSgpOiBUcnVzdGVkVHlwZVBvbGljeXxudWxsIHtcbiAgaWYgKHBvbGljeSA9PT0gdW5kZWZpbmVkKSB7XG4gICAgcG9saWN5ID0gbnVsbDtcbiAgICBpZiAoZ2xvYmFsLnRydXN0ZWRUeXBlcykge1xuICAgICAgdHJ5IHtcbiAgICAgICAgcG9saWN5ID1cbiAgICAgICAgICAgIChnbG9iYWwudHJ1c3RlZFR5cGVzIGFzIFRydXN0ZWRUeXBlUG9saWN5RmFjdG9yeSkuY3JlYXRlUG9saWN5KCdhbmd1bGFyI3Vuc2FmZS1qaXQnLCB7XG4gICAgICAgICAgICAgIGNyZWF0ZVNjcmlwdDogKHM6IHN0cmluZykgPT4gcyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgfSBjYXRjaCB7XG4gICAgICAgIC8vIHRydXN0ZWRUeXBlcy5jcmVhdGVQb2xpY3kgdGhyb3dzIGlmIGNhbGxlZCB3aXRoIGEgbmFtZSB0aGF0IGlzXG4gICAgICAgIC8vIGFscmVhZHkgcmVnaXN0ZXJlZCwgZXZlbiBpbiByZXBvcnQtb25seSBtb2RlLiBVbnRpbCB0aGUgQVBJIGNoYW5nZXMsXG4gICAgICAgIC8vIGNhdGNoIHRoZSBlcnJvciBub3QgdG8gYnJlYWsgdGhlIGFwcGxpY2F0aW9ucyBmdW5jdGlvbmFsbHkuIEluIHN1Y2hcbiAgICAgICAgLy8gY2FzZXMsIHRoZSBjb2RlIHdpbGwgZmFsbCBiYWNrIHRvIHVzaW5nIHN0cmluZ3MuXG4gICAgICB9XG4gICAgfVxuICB9XG4gIHJldHVybiBwb2xpY3k7XG59XG5cbi8qKlxuICogVW5zYWZlbHkgcHJvbW90ZSBhIHN0cmluZyB0byBhIFRydXN0ZWRTY3JpcHQsIGZhbGxpbmcgYmFjayB0byBzdHJpbmdzIHdoZW5cbiAqIFRydXN0ZWQgVHlwZXMgYXJlIG5vdCBhdmFpbGFibGUuXG4gKiBAc2VjdXJpdHkgSW4gcGFydGljdWxhciwgaXQgbXVzdCBiZSBhc3N1cmVkIHRoYXQgdGhlIHByb3ZpZGVkIHN0cmluZyB3aWxsXG4gKiBuZXZlciBjYXVzZSBhbiBYU1MgdnVsbmVyYWJpbGl0eSBpZiB1c2VkIGluIGEgY29udGV4dCB0aGF0IHdpbGwgYmVcbiAqIGludGVycHJldGVkIGFuZCBleGVjdXRlZCBhcyBhIHNjcmlwdCBieSBhIGJyb3dzZXIsIGUuZy4gd2hlbiBjYWxsaW5nIGV2YWwuXG4gKi9cbmZ1bmN0aW9uIHRydXN0ZWRTY3JpcHRGcm9tU3RyaW5nKHNjcmlwdDogc3RyaW5nKTogVHJ1c3RlZFNjcmlwdHxzdHJpbmcge1xuICByZXR1cm4gZ2V0UG9saWN5KCk/LmNyZWF0ZVNjcmlwdChzY3JpcHQpIHx8IHNjcmlwdDtcbn1cblxuLyoqXG4gKiBVbnNhZmVseSBjYWxsIHRoZSBGdW5jdGlvbiBjb25zdHJ1Y3RvciB3aXRoIHRoZSBnaXZlbiBzdHJpbmcgYXJndW1lbnRzLlxuICogQHNlY3VyaXR5IFRoaXMgaXMgYSBzZWN1cml0eS1zZW5zaXRpdmUgZnVuY3Rpb247IGFueSB1c2Ugb2YgdGhpcyBmdW5jdGlvblxuICogbXVzdCBnbyB0aHJvdWdoIHNlY3VyaXR5IHJldmlldy4gSW4gcGFydGljdWxhciwgaXQgbXVzdCBiZSBhc3N1cmVkIHRoYXQgaXRcbiAqIGlzIG9ubHkgY2FsbGVkIGZyb20gdGhlIEpJVCBjb21waWxlciwgYXMgdXNlIGluIG90aGVyIGNvZGUgY2FuIGxlYWQgdG8gWFNTXG4gKiB2dWxuZXJhYmlsaXRpZXMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBuZXdUcnVzdGVkRnVuY3Rpb25Gb3JKSVQoLi4uYXJnczogc3RyaW5nW10pOiBGdW5jdGlvbiB7XG4gIGlmICghZ2xvYmFsLnRydXN0ZWRUeXBlcykge1xuICAgIC8vIEluIGVudmlyb25tZW50cyB0aGF0IGRvbid0IHN1cHBvcnQgVHJ1c3RlZCBUeXBlcywgZmFsbCBiYWNrIHRvIHRoZSBtb3N0XG4gICAgLy8gc3RyYWlnaHRmb3J3YXJkIGltcGxlbWVudGF0aW9uOlxuICAgIHJldHVybiBuZXcgRnVuY3Rpb24oLi4uYXJncyk7XG4gIH1cblxuICAvLyBDaHJvbWUgY3VycmVudGx5IGRvZXMgbm90IHN1cHBvcnQgcGFzc2luZyBUcnVzdGVkU2NyaXB0IHRvIHRoZSBGdW5jdGlvblxuICAvLyBjb25zdHJ1Y3Rvci4gVGhlIGZvbGxvd2luZyBpbXBsZW1lbnRzIHRoZSB3b3JrYXJvdW5kIHByb3Bvc2VkIG9uIHRoZSBwYWdlXG4gIC8vIGJlbG93LCB3aGVyZSB0aGUgQ2hyb21pdW0gYnVnIGlzIGFsc28gcmVmZXJlbmNlZDpcbiAgLy8gaHR0cHM6Ly9naXRodWIuY29tL3czYy93ZWJhcHBzZWMtdHJ1c3RlZC10eXBlcy93aWtpL1RydXN0ZWQtVHlwZXMtZm9yLWZ1bmN0aW9uLWNvbnN0cnVjdG9yXG4gIGNvbnN0IGZuQXJncyA9IGFyZ3Muc2xpY2UoMCwgLTEpLmpvaW4oJywnKTtcbiAgY29uc3QgZm5Cb2R5ID0gYXJnc1thcmdzLmxlbmd0aCAtIDFdO1xuICBjb25zdCBib2R5ID0gYChmdW5jdGlvbiBhbm9ueW1vdXMoJHtmbkFyZ3N9XG4pIHsgJHtmbkJvZHl9XG59KWA7XG5cbiAgLy8gVXNpbmcgZXZhbCBkaXJlY3RseSBjb25mdXNlcyB0aGUgY29tcGlsZXIgYW5kIHByZXZlbnRzIHRoaXMgbW9kdWxlIGZyb21cbiAgLy8gYmVpbmcgc3RyaXBwZWQgb3V0IG9mIEpTIGJpbmFyaWVzIGV2ZW4gaWYgbm90IHVzZWQuIFRoZSBnbG9iYWxbJ2V2YWwnXVxuICAvLyBpbmRpcmVjdGlvbiBmaXhlcyB0aGF0LlxuICBjb25zdCBmbiA9IGdsb2JhbFsnZXZhbCddKHRydXN0ZWRTY3JpcHRGcm9tU3RyaW5nKGJvZHkpIGFzIHN0cmluZykgYXMgRnVuY3Rpb247XG4gIGlmIChmbi5iaW5kID09PSB1bmRlZmluZWQpIHtcbiAgICAvLyBXb3JrYXJvdW5kIGZvciBhIGJyb3dzZXIgYnVnIHRoYXQgb25seSBleGlzdHMgaW4gQ2hyb21lIDgzLCB3aGVyZSBwYXNzaW5nXG4gICAgLy8gYSBUcnVzdGVkU2NyaXB0IHRvIGV2YWwganVzdCByZXR1cm5zIHRoZSBUcnVzdGVkU2NyaXB0IGJhY2sgd2l0aG91dFxuICAgIC8vIGV2YWx1YXRpbmcgaXQuIEluIHRoYXQgY2FzZSwgZmFsbCBiYWNrIHRvIHRoZSBtb3N0IHN0cmFpZ2h0Zm9yd2FyZFxuICAgIC8vIGltcGxlbWVudGF0aW9uOlxuICAgIHJldHVybiBuZXcgRnVuY3Rpb24oLi4uYXJncyk7XG4gIH1cblxuICAvLyBUbyBjb21wbGV0ZWx5IG1pbWljIHRoZSBiZWhhdmlvciBvZiBjYWxsaW5nIFwibmV3IEZ1bmN0aW9uXCIsIHR3byBtb3JlXG4gIC8vIHRoaW5ncyBuZWVkIHRvIGhhcHBlbjpcbiAgLy8gMS4gU3RyaW5naWZ5aW5nIHRoZSByZXN1bHRpbmcgZnVuY3Rpb24gc2hvdWxkIHJldHVybiBpdHMgc291cmNlIGNvZGVcbiAgZm4udG9TdHJpbmcgPSAoKSA9PiBib2R5O1xuICAvLyAyLiBXaGVuIGNhbGxpbmcgdGhlIHJlc3VsdGluZyBmdW5jdGlvbiwgYHRoaXNgIHNob3VsZCByZWZlciB0byBgZ2xvYmFsYFxuICByZXR1cm4gZm4uYmluZChnbG9iYWwpO1xuXG4gIC8vIFdoZW4gVHJ1c3RlZCBUeXBlcyBzdXBwb3J0IGluIEZ1bmN0aW9uIGNvbnN0cnVjdG9ycyBpcyB3aWRlbHkgYXZhaWxhYmxlLFxuICAvLyB0aGUgaW1wbGVtZW50YXRpb24gb2YgdGhpcyBmdW5jdGlvbiBjYW4gYmUgc2ltcGxpZmllZCB0bzpcbiAgLy8gcmV0dXJuIG5ldyBGdW5jdGlvbiguLi5hcmdzLm1hcChhID0+IHRydXN0ZWRTY3JpcHRGcm9tU3RyaW5nKGEpKSk7XG59XG4iXX0=