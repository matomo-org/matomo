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
        define("@angular/core/schematics/migrations/static-queries/angular/query-definition", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.QueryType = exports.QueryTiming = void 0;
    /** Timing of a given query. Either static or dynamic. */
    var QueryTiming;
    (function (QueryTiming) {
        QueryTiming[QueryTiming["STATIC"] = 0] = "STATIC";
        QueryTiming[QueryTiming["DYNAMIC"] = 1] = "DYNAMIC";
    })(QueryTiming = exports.QueryTiming || (exports.QueryTiming = {}));
    /** Type of a given query. */
    var QueryType;
    (function (QueryType) {
        QueryType[QueryType["ViewChild"] = 0] = "ViewChild";
        QueryType[QueryType["ContentChild"] = 1] = "ContentChild";
    })(QueryType = exports.QueryType || (exports.QueryType = {}));
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicXVlcnktZGVmaW5pdGlvbi5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3N0YXRpYy1xdWVyaWVzL2FuZ3VsYXIvcXVlcnktZGVmaW5pdGlvbi50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFLSCx5REFBeUQ7SUFDekQsSUFBWSxXQUdYO0lBSEQsV0FBWSxXQUFXO1FBQ3JCLGlEQUFNLENBQUE7UUFDTixtREFBTyxDQUFBO0lBQ1QsQ0FBQyxFQUhXLFdBQVcsR0FBWCxtQkFBVyxLQUFYLG1CQUFXLFFBR3RCO0lBRUQsNkJBQTZCO0lBQzdCLElBQVksU0FHWDtJQUhELFdBQVksU0FBUztRQUNuQixtREFBUyxDQUFBO1FBQ1QseURBQVksQ0FBQTtJQUNkLENBQUMsRUFIVyxTQUFTLEdBQVQsaUJBQVMsS0FBVCxpQkFBUyxRQUdwQiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcbmltcG9ydCB7TmdEZWNvcmF0b3J9IGZyb20gJy4uLy4uLy4uL3V0aWxzL25nX2RlY29yYXRvcnMnO1xuXG4vKiogVGltaW5nIG9mIGEgZ2l2ZW4gcXVlcnkuIEVpdGhlciBzdGF0aWMgb3IgZHluYW1pYy4gKi9cbmV4cG9ydCBlbnVtIFF1ZXJ5VGltaW5nIHtcbiAgU1RBVElDLFxuICBEWU5BTUlDLFxufVxuXG4vKiogVHlwZSBvZiBhIGdpdmVuIHF1ZXJ5LiAqL1xuZXhwb3J0IGVudW0gUXVlcnlUeXBlIHtcbiAgVmlld0NoaWxkLFxuICBDb250ZW50Q2hpbGRcbn1cblxuZXhwb3J0IGludGVyZmFjZSBOZ1F1ZXJ5RGVmaW5pdGlvbiB7XG4gIC8qKiBOYW1lIG9mIHRoZSBxdWVyeS4gU2V0IHRvIFwibnVsbFwiIGluIGNhc2UgdGhlIHF1ZXJ5IG5hbWUgaXMgbm90IHN0YXRpY2FsbHkgYW5hbHl6YWJsZS4gKi9cbiAgbmFtZTogc3RyaW5nfG51bGw7XG4gIC8qKiBUeXBlIG9mIHRoZSBxdWVyeSBkZWZpbml0aW9uLiAqL1xuICB0eXBlOiBRdWVyeVR5cGU7XG4gIC8qKiBOb2RlIHRoYXQgZGVjbGFyZXMgdGhpcyBxdWVyeS4gKi9cbiAgbm9kZTogdHMuTm9kZTtcbiAgLyoqXG4gICAqIFByb3BlcnR5IGRlY2xhcmF0aW9uIHRoYXQgcmVmZXJzIHRvIHRoZSBxdWVyeSB2YWx1ZS4gRm9yIGFjY2Vzc29ycyB0aGVyZVxuICAgKiBpcyBubyBwcm9wZXJ0eSB0aGF0IGlzIGd1YXJhbnRlZWQgdG8gYWNjZXNzIHRoZSBxdWVyeSB2YWx1ZS5cbiAgICovXG4gIHByb3BlcnR5OiB0cy5Qcm9wZXJ0eURlY2xhcmF0aW9ufG51bGw7XG4gIC8qKiBEZWNvcmF0b3IgdGhhdCBkZWNsYXJlcyB0aGlzIGFzIGEgcXVlcnkuICovXG4gIGRlY29yYXRvcjogTmdEZWNvcmF0b3I7XG4gIC8qKiBDbGFzcyBkZWNsYXJhdGlvbiB0aGF0IGhvbGRzIHRoaXMgcXVlcnkuICovXG4gIGNvbnRhaW5lcjogdHMuQ2xhc3NEZWNsYXJhdGlvbjtcbn1cbiJdfQ==