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
        define("@angular/compiler", ["require", "exports", "tslib", "@angular/compiler/compiler"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    var tslib_1 = require("tslib");
    // This file is not used to build this module. It is only used during editing
    // by the TypeScript language service and during build for verification. `ngc`
    // replaces this file with production index.ts when it rewrites private symbol
    // names.
    tslib_1.__exportStar(require("@angular/compiler/compiler"), exports);
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9pbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCw2RUFBNkU7SUFDN0UsOEVBQThFO0lBQzlFLDhFQUE4RTtJQUM5RSxTQUFTO0lBRVQscUVBQTJCIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbi8vIFRoaXMgZmlsZSBpcyBub3QgdXNlZCB0byBidWlsZCB0aGlzIG1vZHVsZS4gSXQgaXMgb25seSB1c2VkIGR1cmluZyBlZGl0aW5nXG4vLyBieSB0aGUgVHlwZVNjcmlwdCBsYW5ndWFnZSBzZXJ2aWNlIGFuZCBkdXJpbmcgYnVpbGQgZm9yIHZlcmlmaWNhdGlvbi4gYG5nY2Bcbi8vIHJlcGxhY2VzIHRoaXMgZmlsZSB3aXRoIHByb2R1Y3Rpb24gaW5kZXgudHMgd2hlbiBpdCByZXdyaXRlcyBwcml2YXRlIHN5bWJvbFxuLy8gbmFtZXMuXG5cbmV4cG9ydCAqIGZyb20gJy4vY29tcGlsZXInO1xuIl19