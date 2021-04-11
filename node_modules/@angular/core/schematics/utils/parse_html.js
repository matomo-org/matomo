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
        define("@angular/core/schematics/utils/parse_html", ["require", "exports", "@angular/compiler"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.parseHtmlGracefully = void 0;
    const compiler_1 = require("@angular/compiler");
    /**
     * Parses the given HTML content using the Angular compiler. In case the parsing
     * fails, null is being returned.
     */
    function parseHtmlGracefully(htmlContent, filePath) {
        try {
            return compiler_1.parseTemplate(htmlContent, filePath).nodes;
        }
        catch (_a) {
            // Do nothing if the template couldn't be parsed. We don't want to throw any
            // exception if a template is syntactically not valid. e.g. template could be
            // using preprocessor syntax.
            return null;
        }
    }
    exports.parseHtmlGracefully = parseHtmlGracefully;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGFyc2VfaHRtbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy9wYXJzZV9odG1sLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGdEQUFnRDtJQUdoRDs7O09BR0c7SUFDSCxTQUFnQixtQkFBbUIsQ0FBQyxXQUFtQixFQUFFLFFBQWdCO1FBQ3ZFLElBQUk7WUFDRixPQUFPLHdCQUFhLENBQUMsV0FBVyxFQUFFLFFBQVEsQ0FBQyxDQUFDLEtBQUssQ0FBQztTQUNuRDtRQUFDLFdBQU07WUFDTiw0RUFBNEU7WUFDNUUsNkVBQTZFO1lBQzdFLDZCQUE2QjtZQUM3QixPQUFPLElBQUksQ0FBQztTQUNiO0lBQ0gsQ0FBQztJQVRELGtEQVNDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7cGFyc2VUZW1wbGF0ZX0gZnJvbSAnQGFuZ3VsYXIvY29tcGlsZXInO1xuaW1wb3J0IHtOb2RlfSBmcm9tICdAYW5ndWxhci9jb21waWxlci9zcmMvcmVuZGVyMy9yM19hc3QnO1xuXG4vKipcbiAqIFBhcnNlcyB0aGUgZ2l2ZW4gSFRNTCBjb250ZW50IHVzaW5nIHRoZSBBbmd1bGFyIGNvbXBpbGVyLiBJbiBjYXNlIHRoZSBwYXJzaW5nXG4gKiBmYWlscywgbnVsbCBpcyBiZWluZyByZXR1cm5lZC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHBhcnNlSHRtbEdyYWNlZnVsbHkoaHRtbENvbnRlbnQ6IHN0cmluZywgZmlsZVBhdGg6IHN0cmluZyk6IE5vZGVbXXxudWxsIHtcbiAgdHJ5IHtcbiAgICByZXR1cm4gcGFyc2VUZW1wbGF0ZShodG1sQ29udGVudCwgZmlsZVBhdGgpLm5vZGVzO1xuICB9IGNhdGNoIHtcbiAgICAvLyBEbyBub3RoaW5nIGlmIHRoZSB0ZW1wbGF0ZSBjb3VsZG4ndCBiZSBwYXJzZWQuIFdlIGRvbid0IHdhbnQgdG8gdGhyb3cgYW55XG4gICAgLy8gZXhjZXB0aW9uIGlmIGEgdGVtcGxhdGUgaXMgc3ludGFjdGljYWxseSBub3QgdmFsaWQuIGUuZy4gdGVtcGxhdGUgY291bGQgYmVcbiAgICAvLyB1c2luZyBwcmVwcm9jZXNzb3Igc3ludGF4LlxuICAgIHJldHVybiBudWxsO1xuICB9XG59XG4iXX0=