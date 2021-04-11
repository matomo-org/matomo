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
        define("@angular/compiler/testing/src/testing", ["require", "exports", "tslib", "@angular/compiler/testing/src/resource_loader_mock", "@angular/compiler/testing/src/schema_registry_mock", "@angular/compiler/testing/src/directive_resolver_mock", "@angular/compiler/testing/src/ng_module_resolver_mock", "@angular/compiler/testing/src/pipe_resolver_mock"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var tslib_1 = require("tslib");
    /**
     * @module
     * @description
     * Entry point for all APIs of the compiler package.
     *
     * <div class="callout is-critical">
     *   <header>Unstable APIs</header>
     *   <p>
     *     All compiler apis are currently considered experimental and private!
     *   </p>
     *   <p>
     *     We expect the APIs in this package to keep on changing. Do not rely on them.
     *   </p>
     * </div>
     */
    tslib_1.__exportStar(require("@angular/compiler/testing/src/resource_loader_mock"), exports);
    tslib_1.__exportStar(require("@angular/compiler/testing/src/schema_registry_mock"), exports);
    tslib_1.__exportStar(require("@angular/compiler/testing/src/directive_resolver_mock"), exports);
    tslib_1.__exportStar(require("@angular/compiler/testing/src/ng_module_resolver_mock"), exports);
    tslib_1.__exportStar(require("@angular/compiler/testing/src/pipe_resolver_mock"), exports);
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidGVzdGluZy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvbXBpbGVyL3Rlc3Rpbmcvc3JjL3Rlc3RpbmcudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUg7Ozs7Ozs7Ozs7Ozs7O09BY0c7SUFDSCw2RkFBdUM7SUFDdkMsNkZBQXVDO0lBQ3ZDLGdHQUEwQztJQUMxQyxnR0FBMEM7SUFDMUMsMkZBQXFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8qKlxuICogQG1vZHVsZVxuICogQGRlc2NyaXB0aW9uXG4gKiBFbnRyeSBwb2ludCBmb3IgYWxsIEFQSXMgb2YgdGhlIGNvbXBpbGVyIHBhY2thZ2UuXG4gKlxuICogPGRpdiBjbGFzcz1cImNhbGxvdXQgaXMtY3JpdGljYWxcIj5cbiAqICAgPGhlYWRlcj5VbnN0YWJsZSBBUElzPC9oZWFkZXI+XG4gKiAgIDxwPlxuICogICAgIEFsbCBjb21waWxlciBhcGlzIGFyZSBjdXJyZW50bHkgY29uc2lkZXJlZCBleHBlcmltZW50YWwgYW5kIHByaXZhdGUhXG4gKiAgIDwvcD5cbiAqICAgPHA+XG4gKiAgICAgV2UgZXhwZWN0IHRoZSBBUElzIGluIHRoaXMgcGFja2FnZSB0byBrZWVwIG9uIGNoYW5naW5nLiBEbyBub3QgcmVseSBvbiB0aGVtLlxuICogICA8L3A+XG4gKiA8L2Rpdj5cbiAqL1xuZXhwb3J0ICogZnJvbSAnLi9yZXNvdXJjZV9sb2FkZXJfbW9jayc7XG5leHBvcnQgKiBmcm9tICcuL3NjaGVtYV9yZWdpc3RyeV9tb2NrJztcbmV4cG9ydCAqIGZyb20gJy4vZGlyZWN0aXZlX3Jlc29sdmVyX21vY2snO1xuZXhwb3J0ICogZnJvbSAnLi9uZ19tb2R1bGVfcmVzb2x2ZXJfbW9jayc7XG5leHBvcnQgKiBmcm9tICcuL3BpcGVfcmVzb2x2ZXJfbW9jayc7XG4iXX0=