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
        define("@angular/compiler/src/resource_loader", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ResourceLoader = void 0;
    /**
     * An interface for retrieving documents by URL that the compiler uses
     * to load templates.
     */
    var ResourceLoader = /** @class */ (function () {
        function ResourceLoader() {
        }
        ResourceLoader.prototype.get = function (url) {
            return '';
        };
        return ResourceLoader;
    }());
    exports.ResourceLoader = ResourceLoader;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicmVzb3VyY2VfbG9hZGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29tcGlsZXIvc3JjL3Jlc291cmNlX2xvYWRlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSDs7O09BR0c7SUFDSDtRQUFBO1FBSUEsQ0FBQztRQUhDLDRCQUFHLEdBQUgsVUFBSSxHQUFXO1lBQ2IsT0FBTyxFQUFFLENBQUM7UUFDWixDQUFDO1FBQ0gscUJBQUM7SUFBRCxDQUFDLEFBSkQsSUFJQztJQUpZLHdDQUFjIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogQW4gaW50ZXJmYWNlIGZvciByZXRyaWV2aW5nIGRvY3VtZW50cyBieSBVUkwgdGhhdCB0aGUgY29tcGlsZXIgdXNlc1xuICogdG8gbG9hZCB0ZW1wbGF0ZXMuXG4gKi9cbmV4cG9ydCBjbGFzcyBSZXNvdXJjZUxvYWRlciB7XG4gIGdldCh1cmw6IHN0cmluZyk6IFByb21pc2U8c3RyaW5nPnxzdHJpbmcge1xuICAgIHJldHVybiAnJztcbiAgfVxufVxuIl19