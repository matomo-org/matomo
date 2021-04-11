(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(require, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/compiler/src/render3/partial/util", ["require", "exports", "@angular/compiler/src/output/output_ast"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.toOptionalLiteralMap = exports.toOptionalLiteralArray = void 0;
    /**
     * @license
     * Copyright Google LLC All Rights Reserved.
     *
     * Use of this source code is governed by an MIT-style license that can be
     * found in the LICENSE file at https://angular.io/license
     */
    var o = require("@angular/compiler/src/output/output_ast");
    /**
     * Creates an array literal expression from the given array, mapping all values to an expression
     * using the provided mapping function. If the array is empty or null, then null is returned.
     *
     * @param values The array to transfer into literal array expression.
     * @param mapper The logic to use for creating an expression for the array's values.
     * @returns An array literal expression representing `values`, or null if `values` is empty or
     * is itself null.
     */
    function toOptionalLiteralArray(values, mapper) {
        if (values === null || values.length === 0) {
            return null;
        }
        return o.literalArr(values.map(function (value) { return mapper(value); }));
    }
    exports.toOptionalLiteralArray = toOptionalLiteralArray;
    /**
     * Creates an object literal expression from the given object, mapping all values to an expression
     * using the provided mapping function. If the object has no keys, then null is returned.
     *
     * @param object The object to transfer into an object literal expression.
     * @param mapper The logic to use for creating an expression for the object's values.
     * @returns An object literal expression representing `object`, or null if `object` does not have
     * any keys.
     */
    function toOptionalLiteralMap(object, mapper) {
        var entries = Object.keys(object).map(function (key) {
            var value = object[key];
            return { key: key, value: mapper(value), quoted: true };
        });
        if (entries.length > 0) {
            return o.literalMap(entries);
        }
        else {
            return null;
        }
    }
    exports.toOptionalLiteralMap = toOptionalLiteralMap;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9yZW5kZXIzL3BhcnRpYWwvdXRpbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7SUFBQTs7Ozs7O09BTUc7SUFDSCwyREFBNkM7SUFFN0M7Ozs7Ozs7O09BUUc7SUFDSCxTQUFnQixzQkFBc0IsQ0FDbEMsTUFBZ0IsRUFBRSxNQUFrQztRQUN0RCxJQUFJLE1BQU0sS0FBSyxJQUFJLElBQUksTUFBTSxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7WUFDMUMsT0FBTyxJQUFJLENBQUM7U0FDYjtRQUNELE9BQU8sQ0FBQyxDQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFVBQUEsS0FBSyxJQUFJLE9BQUEsTUFBTSxDQUFDLEtBQUssQ0FBQyxFQUFiLENBQWEsQ0FBQyxDQUFDLENBQUM7SUFDMUQsQ0FBQztJQU5ELHdEQU1DO0lBRUQ7Ozs7Ozs7O09BUUc7SUFDSCxTQUFnQixvQkFBb0IsQ0FDaEMsTUFBMEIsRUFBRSxNQUFrQztRQUNoRSxJQUFNLE9BQU8sR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLEdBQUcsQ0FBQyxVQUFBLEdBQUc7WUFDekMsSUFBTSxLQUFLLEdBQUcsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDO1lBQzFCLE9BQU8sRUFBQyxHQUFHLEtBQUEsRUFBRSxLQUFLLEVBQUUsTUFBTSxDQUFDLEtBQUssQ0FBQyxFQUFFLE1BQU0sRUFBRSxJQUFJLEVBQUMsQ0FBQztRQUNuRCxDQUFDLENBQUMsQ0FBQztRQUVILElBQUksT0FBTyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7WUFDdEIsT0FBTyxDQUFDLENBQUMsVUFBVSxDQUFDLE9BQU8sQ0FBQyxDQUFDO1NBQzlCO2FBQU07WUFDTCxPQUFPLElBQUksQ0FBQztTQUNiO0lBQ0gsQ0FBQztJQVpELG9EQVlDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5pbXBvcnQgKiBhcyBvIGZyb20gJy4uLy4uL291dHB1dC9vdXRwdXRfYXN0JztcblxuLyoqXG4gKiBDcmVhdGVzIGFuIGFycmF5IGxpdGVyYWwgZXhwcmVzc2lvbiBmcm9tIHRoZSBnaXZlbiBhcnJheSwgbWFwcGluZyBhbGwgdmFsdWVzIHRvIGFuIGV4cHJlc3Npb25cbiAqIHVzaW5nIHRoZSBwcm92aWRlZCBtYXBwaW5nIGZ1bmN0aW9uLiBJZiB0aGUgYXJyYXkgaXMgZW1wdHkgb3IgbnVsbCwgdGhlbiBudWxsIGlzIHJldHVybmVkLlxuICpcbiAqIEBwYXJhbSB2YWx1ZXMgVGhlIGFycmF5IHRvIHRyYW5zZmVyIGludG8gbGl0ZXJhbCBhcnJheSBleHByZXNzaW9uLlxuICogQHBhcmFtIG1hcHBlciBUaGUgbG9naWMgdG8gdXNlIGZvciBjcmVhdGluZyBhbiBleHByZXNzaW9uIGZvciB0aGUgYXJyYXkncyB2YWx1ZXMuXG4gKiBAcmV0dXJucyBBbiBhcnJheSBsaXRlcmFsIGV4cHJlc3Npb24gcmVwcmVzZW50aW5nIGB2YWx1ZXNgLCBvciBudWxsIGlmIGB2YWx1ZXNgIGlzIGVtcHR5IG9yXG4gKiBpcyBpdHNlbGYgbnVsbC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHRvT3B0aW9uYWxMaXRlcmFsQXJyYXk8VD4oXG4gICAgdmFsdWVzOiBUW118bnVsbCwgbWFwcGVyOiAodmFsdWU6IFQpID0+IG8uRXhwcmVzc2lvbik6IG8uTGl0ZXJhbEFycmF5RXhwcnxudWxsIHtcbiAgaWYgKHZhbHVlcyA9PT0gbnVsbCB8fCB2YWx1ZXMubGVuZ3RoID09PSAwKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cbiAgcmV0dXJuIG8ubGl0ZXJhbEFycih2YWx1ZXMubWFwKHZhbHVlID0+IG1hcHBlcih2YWx1ZSkpKTtcbn1cblxuLyoqXG4gKiBDcmVhdGVzIGFuIG9iamVjdCBsaXRlcmFsIGV4cHJlc3Npb24gZnJvbSB0aGUgZ2l2ZW4gb2JqZWN0LCBtYXBwaW5nIGFsbCB2YWx1ZXMgdG8gYW4gZXhwcmVzc2lvblxuICogdXNpbmcgdGhlIHByb3ZpZGVkIG1hcHBpbmcgZnVuY3Rpb24uIElmIHRoZSBvYmplY3QgaGFzIG5vIGtleXMsIHRoZW4gbnVsbCBpcyByZXR1cm5lZC5cbiAqXG4gKiBAcGFyYW0gb2JqZWN0IFRoZSBvYmplY3QgdG8gdHJhbnNmZXIgaW50byBhbiBvYmplY3QgbGl0ZXJhbCBleHByZXNzaW9uLlxuICogQHBhcmFtIG1hcHBlciBUaGUgbG9naWMgdG8gdXNlIGZvciBjcmVhdGluZyBhbiBleHByZXNzaW9uIGZvciB0aGUgb2JqZWN0J3MgdmFsdWVzLlxuICogQHJldHVybnMgQW4gb2JqZWN0IGxpdGVyYWwgZXhwcmVzc2lvbiByZXByZXNlbnRpbmcgYG9iamVjdGAsIG9yIG51bGwgaWYgYG9iamVjdGAgZG9lcyBub3QgaGF2ZVxuICogYW55IGtleXMuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiB0b09wdGlvbmFsTGl0ZXJhbE1hcDxUPihcbiAgICBvYmplY3Q6IHtba2V5OiBzdHJpbmddOiBUfSwgbWFwcGVyOiAodmFsdWU6IFQpID0+IG8uRXhwcmVzc2lvbik6IG8uTGl0ZXJhbE1hcEV4cHJ8bnVsbCB7XG4gIGNvbnN0IGVudHJpZXMgPSBPYmplY3Qua2V5cyhvYmplY3QpLm1hcChrZXkgPT4ge1xuICAgIGNvbnN0IHZhbHVlID0gb2JqZWN0W2tleV07XG4gICAgcmV0dXJuIHtrZXksIHZhbHVlOiBtYXBwZXIodmFsdWUpLCBxdW90ZWQ6IHRydWV9O1xuICB9KTtcblxuICBpZiAoZW50cmllcy5sZW5ndGggPiAwKSB7XG4gICAgcmV0dXJuIG8ubGl0ZXJhbE1hcChlbnRyaWVzKTtcbiAgfSBlbHNlIHtcbiAgICByZXR1cm4gbnVsbDtcbiAgfVxufVxuIl19