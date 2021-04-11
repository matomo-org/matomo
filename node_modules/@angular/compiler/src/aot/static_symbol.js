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
        define("@angular/compiler/src/aot/static_symbol", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.StaticSymbolCache = exports.StaticSymbol = void 0;
    /**
     * A token representing the a reference to a static type.
     *
     * This token is unique for a filePath and name and can be used as a hash table key.
     */
    var StaticSymbol = /** @class */ (function () {
        function StaticSymbol(filePath, name, members) {
            this.filePath = filePath;
            this.name = name;
            this.members = members;
        }
        StaticSymbol.prototype.assertNoMembers = function () {
            if (this.members.length) {
                throw new Error("Illegal state: symbol without members expected, but got " + JSON.stringify(this) + ".");
            }
        };
        return StaticSymbol;
    }());
    exports.StaticSymbol = StaticSymbol;
    /**
     * A cache of static symbol used by the StaticReflector to return the same symbol for the
     * same symbol values.
     */
    var StaticSymbolCache = /** @class */ (function () {
        function StaticSymbolCache() {
            this.cache = new Map();
        }
        StaticSymbolCache.prototype.get = function (declarationFile, name, members) {
            members = members || [];
            var memberSuffix = members.length ? "." + members.join('.') : '';
            var key = "\"" + declarationFile + "\"." + name + memberSuffix;
            var result = this.cache.get(key);
            if (!result) {
                result = new StaticSymbol(declarationFile, name, members);
                this.cache.set(key, result);
            }
            return result;
        };
        return StaticSymbolCache;
    }());
    exports.StaticSymbolCache = StaticSymbolCache;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3RhdGljX3N5bWJvbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3NyYy9hb3Qvc3RhdGljX3N5bWJvbC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSDs7OztPQUlHO0lBQ0g7UUFDRSxzQkFBbUIsUUFBZ0IsRUFBUyxJQUFZLEVBQVMsT0FBaUI7WUFBL0QsYUFBUSxHQUFSLFFBQVEsQ0FBUTtZQUFTLFNBQUksR0FBSixJQUFJLENBQVE7WUFBUyxZQUFPLEdBQVAsT0FBTyxDQUFVO1FBQUcsQ0FBQztRQUV0RixzQ0FBZSxHQUFmO1lBQ0UsSUFBSSxJQUFJLENBQUMsT0FBTyxDQUFDLE1BQU0sRUFBRTtnQkFDdkIsTUFBTSxJQUFJLEtBQUssQ0FDWCw2REFBMkQsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsTUFBRyxDQUFDLENBQUM7YUFDekY7UUFDSCxDQUFDO1FBQ0gsbUJBQUM7SUFBRCxDQUFDLEFBVEQsSUFTQztJQVRZLG9DQUFZO0lBV3pCOzs7T0FHRztJQUNIO1FBQUE7WUFDVSxVQUFLLEdBQUcsSUFBSSxHQUFHLEVBQXdCLENBQUM7UUFhbEQsQ0FBQztRQVhDLCtCQUFHLEdBQUgsVUFBSSxlQUF1QixFQUFFLElBQVksRUFBRSxPQUFrQjtZQUMzRCxPQUFPLEdBQUcsT0FBTyxJQUFJLEVBQUUsQ0FBQztZQUN4QixJQUFNLFlBQVksR0FBRyxPQUFPLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFJLE9BQU8sQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFHLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQztZQUNuRSxJQUFNLEdBQUcsR0FBRyxPQUFJLGVBQWUsV0FBSyxJQUFJLEdBQUcsWUFBYyxDQUFDO1lBQzFELElBQUksTUFBTSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQyxDQUFDO1lBQ2pDLElBQUksQ0FBQyxNQUFNLEVBQUU7Z0JBQ1gsTUFBTSxHQUFHLElBQUksWUFBWSxDQUFDLGVBQWUsRUFBRSxJQUFJLEVBQUUsT0FBTyxDQUFDLENBQUM7Z0JBQzFELElBQUksQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLEdBQUcsRUFBRSxNQUFNLENBQUMsQ0FBQzthQUM3QjtZQUNELE9BQU8sTUFBTSxDQUFDO1FBQ2hCLENBQUM7UUFDSCx3QkFBQztJQUFELENBQUMsQUFkRCxJQWNDO0lBZFksOENBQWlCIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogQSB0b2tlbiByZXByZXNlbnRpbmcgdGhlIGEgcmVmZXJlbmNlIHRvIGEgc3RhdGljIHR5cGUuXG4gKlxuICogVGhpcyB0b2tlbiBpcyB1bmlxdWUgZm9yIGEgZmlsZVBhdGggYW5kIG5hbWUgYW5kIGNhbiBiZSB1c2VkIGFzIGEgaGFzaCB0YWJsZSBrZXkuXG4gKi9cbmV4cG9ydCBjbGFzcyBTdGF0aWNTeW1ib2wge1xuICBjb25zdHJ1Y3RvcihwdWJsaWMgZmlsZVBhdGg6IHN0cmluZywgcHVibGljIG5hbWU6IHN0cmluZywgcHVibGljIG1lbWJlcnM6IHN0cmluZ1tdKSB7fVxuXG4gIGFzc2VydE5vTWVtYmVycygpIHtcbiAgICBpZiAodGhpcy5tZW1iZXJzLmxlbmd0aCkge1xuICAgICAgdGhyb3cgbmV3IEVycm9yKFxuICAgICAgICAgIGBJbGxlZ2FsIHN0YXRlOiBzeW1ib2wgd2l0aG91dCBtZW1iZXJzIGV4cGVjdGVkLCBidXQgZ290ICR7SlNPTi5zdHJpbmdpZnkodGhpcyl9LmApO1xuICAgIH1cbiAgfVxufVxuXG4vKipcbiAqIEEgY2FjaGUgb2Ygc3RhdGljIHN5bWJvbCB1c2VkIGJ5IHRoZSBTdGF0aWNSZWZsZWN0b3IgdG8gcmV0dXJuIHRoZSBzYW1lIHN5bWJvbCBmb3IgdGhlXG4gKiBzYW1lIHN5bWJvbCB2YWx1ZXMuXG4gKi9cbmV4cG9ydCBjbGFzcyBTdGF0aWNTeW1ib2xDYWNoZSB7XG4gIHByaXZhdGUgY2FjaGUgPSBuZXcgTWFwPHN0cmluZywgU3RhdGljU3ltYm9sPigpO1xuXG4gIGdldChkZWNsYXJhdGlvbkZpbGU6IHN0cmluZywgbmFtZTogc3RyaW5nLCBtZW1iZXJzPzogc3RyaW5nW10pOiBTdGF0aWNTeW1ib2wge1xuICAgIG1lbWJlcnMgPSBtZW1iZXJzIHx8IFtdO1xuICAgIGNvbnN0IG1lbWJlclN1ZmZpeCA9IG1lbWJlcnMubGVuZ3RoID8gYC4ke21lbWJlcnMuam9pbignLicpfWAgOiAnJztcbiAgICBjb25zdCBrZXkgPSBgXCIke2RlY2xhcmF0aW9uRmlsZX1cIi4ke25hbWV9JHttZW1iZXJTdWZmaXh9YDtcbiAgICBsZXQgcmVzdWx0ID0gdGhpcy5jYWNoZS5nZXQoa2V5KTtcbiAgICBpZiAoIXJlc3VsdCkge1xuICAgICAgcmVzdWx0ID0gbmV3IFN0YXRpY1N5bWJvbChkZWNsYXJhdGlvbkZpbGUsIG5hbWUsIG1lbWJlcnMpO1xuICAgICAgdGhpcy5jYWNoZS5zZXQoa2V5LCByZXN1bHQpO1xuICAgIH1cbiAgICByZXR1cm4gcmVzdWx0O1xuICB9XG59XG4iXX0=