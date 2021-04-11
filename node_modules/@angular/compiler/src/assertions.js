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
        define("@angular/compiler/src/assertions", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.assertInterpolationSymbols = exports.assertArrayOfStrings = void 0;
    function assertArrayOfStrings(identifier, value) {
        if (value == null) {
            return;
        }
        if (!Array.isArray(value)) {
            throw new Error("Expected '" + identifier + "' to be an array of strings.");
        }
        for (var i = 0; i < value.length; i += 1) {
            if (typeof value[i] !== 'string') {
                throw new Error("Expected '" + identifier + "' to be an array of strings.");
            }
        }
    }
    exports.assertArrayOfStrings = assertArrayOfStrings;
    var UNUSABLE_INTERPOLATION_REGEXPS = [
        /^\s*$/,
        /[<>]/,
        /^[{}]$/,
        /&(#|[a-z])/i,
        /^\/\//,
    ];
    function assertInterpolationSymbols(identifier, value) {
        if (value != null && !(Array.isArray(value) && value.length == 2)) {
            throw new Error("Expected '" + identifier + "' to be an array, [start, end].");
        }
        else if (value != null) {
            var start_1 = value[0];
            var end_1 = value[1];
            // Check for unusable interpolation symbols
            UNUSABLE_INTERPOLATION_REGEXPS.forEach(function (regexp) {
                if (regexp.test(start_1) || regexp.test(end_1)) {
                    throw new Error("['" + start_1 + "', '" + end_1 + "'] contains unusable interpolation symbol.");
                }
            });
        }
    }
    exports.assertInterpolationSymbols = assertInterpolationSymbols;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXNzZXJ0aW9ucy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9hc3NlcnRpb25zLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILFNBQWdCLG9CQUFvQixDQUFDLFVBQWtCLEVBQUUsS0FBVTtRQUNqRSxJQUFJLEtBQUssSUFBSSxJQUFJLEVBQUU7WUFDakIsT0FBTztTQUNSO1FBQ0QsSUFBSSxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEVBQUU7WUFDekIsTUFBTSxJQUFJLEtBQUssQ0FBQyxlQUFhLFVBQVUsaUNBQThCLENBQUMsQ0FBQztTQUN4RTtRQUNELEtBQUssSUFBSSxDQUFDLEdBQUcsQ0FBQyxFQUFFLENBQUMsR0FBRyxLQUFLLENBQUMsTUFBTSxFQUFFLENBQUMsSUFBSSxDQUFDLEVBQUU7WUFDeEMsSUFBSSxPQUFPLEtBQUssQ0FBQyxDQUFDLENBQUMsS0FBSyxRQUFRLEVBQUU7Z0JBQ2hDLE1BQU0sSUFBSSxLQUFLLENBQUMsZUFBYSxVQUFVLGlDQUE4QixDQUFDLENBQUM7YUFDeEU7U0FDRjtJQUNILENBQUM7SUFaRCxvREFZQztJQUVELElBQU0sOEJBQThCLEdBQUc7UUFDckMsT0FBTztRQUNQLE1BQU07UUFDTixRQUFRO1FBQ1IsYUFBYTtRQUNiLE9BQU87S0FDUixDQUFDO0lBRUYsU0FBZ0IsMEJBQTBCLENBQUMsVUFBa0IsRUFBRSxLQUFVO1FBQ3ZFLElBQUksS0FBSyxJQUFJLElBQUksSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsSUFBSSxLQUFLLENBQUMsTUFBTSxJQUFJLENBQUMsQ0FBQyxFQUFFO1lBQ2pFLE1BQU0sSUFBSSxLQUFLLENBQUMsZUFBYSxVQUFVLG9DQUFpQyxDQUFDLENBQUM7U0FDM0U7YUFBTSxJQUFJLEtBQUssSUFBSSxJQUFJLEVBQUU7WUFDeEIsSUFBTSxPQUFLLEdBQUcsS0FBSyxDQUFDLENBQUMsQ0FBVyxDQUFDO1lBQ2pDLElBQU0sS0FBRyxHQUFHLEtBQUssQ0FBQyxDQUFDLENBQVcsQ0FBQztZQUMvQiwyQ0FBMkM7WUFDM0MsOEJBQThCLENBQUMsT0FBTyxDQUFDLFVBQUEsTUFBTTtnQkFDM0MsSUFBSSxNQUFNLENBQUMsSUFBSSxDQUFDLE9BQUssQ0FBQyxJQUFJLE1BQU0sQ0FBQyxJQUFJLENBQUMsS0FBRyxDQUFDLEVBQUU7b0JBQzFDLE1BQU0sSUFBSSxLQUFLLENBQUMsT0FBSyxPQUFLLFlBQU8sS0FBRywrQ0FBNEMsQ0FBQyxDQUFDO2lCQUNuRjtZQUNILENBQUMsQ0FBQyxDQUFDO1NBQ0o7SUFDSCxDQUFDO0lBYkQsZ0VBYUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuZXhwb3J0IGZ1bmN0aW9uIGFzc2VydEFycmF5T2ZTdHJpbmdzKGlkZW50aWZpZXI6IHN0cmluZywgdmFsdWU6IGFueSkge1xuICBpZiAodmFsdWUgPT0gbnVsbCkge1xuICAgIHJldHVybjtcbiAgfVxuICBpZiAoIUFycmF5LmlzQXJyYXkodmFsdWUpKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKGBFeHBlY3RlZCAnJHtpZGVudGlmaWVyfScgdG8gYmUgYW4gYXJyYXkgb2Ygc3RyaW5ncy5gKTtcbiAgfVxuICBmb3IgKGxldCBpID0gMDsgaSA8IHZhbHVlLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgaWYgKHR5cGVvZiB2YWx1ZVtpXSAhPT0gJ3N0cmluZycpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcihgRXhwZWN0ZWQgJyR7aWRlbnRpZmllcn0nIHRvIGJlIGFuIGFycmF5IG9mIHN0cmluZ3MuYCk7XG4gICAgfVxuICB9XG59XG5cbmNvbnN0IFVOVVNBQkxFX0lOVEVSUE9MQVRJT05fUkVHRVhQUyA9IFtcbiAgL15cXHMqJC8sICAgICAgICAvLyBlbXB0eVxuICAvWzw+XS8sICAgICAgICAgLy8gaHRtbCB0YWdcbiAgL15be31dJC8sICAgICAgIC8vIGkxOG4gZXhwYW5zaW9uXG4gIC8mKCN8W2Etel0pL2ksICAvLyBjaGFyYWN0ZXIgcmVmZXJlbmNlLFxuICAvXlxcL1xcLy8sICAgICAgICAvLyBjb21tZW50XG5dO1xuXG5leHBvcnQgZnVuY3Rpb24gYXNzZXJ0SW50ZXJwb2xhdGlvblN5bWJvbHMoaWRlbnRpZmllcjogc3RyaW5nLCB2YWx1ZTogYW55KTogdm9pZCB7XG4gIGlmICh2YWx1ZSAhPSBudWxsICYmICEoQXJyYXkuaXNBcnJheSh2YWx1ZSkgJiYgdmFsdWUubGVuZ3RoID09IDIpKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKGBFeHBlY3RlZCAnJHtpZGVudGlmaWVyfScgdG8gYmUgYW4gYXJyYXksIFtzdGFydCwgZW5kXS5gKTtcbiAgfSBlbHNlIGlmICh2YWx1ZSAhPSBudWxsKSB7XG4gICAgY29uc3Qgc3RhcnQgPSB2YWx1ZVswXSBhcyBzdHJpbmc7XG4gICAgY29uc3QgZW5kID0gdmFsdWVbMV0gYXMgc3RyaW5nO1xuICAgIC8vIENoZWNrIGZvciB1bnVzYWJsZSBpbnRlcnBvbGF0aW9uIHN5bWJvbHNcbiAgICBVTlVTQUJMRV9JTlRFUlBPTEFUSU9OX1JFR0VYUFMuZm9yRWFjaChyZWdleHAgPT4ge1xuICAgICAgaWYgKHJlZ2V4cC50ZXN0KHN0YXJ0KSB8fCByZWdleHAudGVzdChlbmQpKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcihgWycke3N0YXJ0fScsICcke2VuZH0nXSBjb250YWlucyB1bnVzYWJsZSBpbnRlcnBvbGF0aW9uIHN5bWJvbC5gKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxufVxuIl19