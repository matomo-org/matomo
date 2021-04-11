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
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/path_format", ["require", "exports", "path"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getPosixPath = void 0;
    const path_1 = require("path");
    /** Normalizes the specified path to conform with the posix path format. */
    function getPosixPath(pathString) {
        const normalized = path_1.normalize(pathString).replace(/\\/g, '/');
        if (!normalized.startsWith('.')) {
            return `./${normalized}`;
        }
        return normalized;
    }
    exports.getPosixPath = getPosixPath;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicGF0aF9mb3JtYXQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy91bmRlY29yYXRlZC1jbGFzc2VzLXdpdGgtZGkvZGVjb3JhdG9yX3Jld3JpdGUvcGF0aF9mb3JtYXQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsK0JBQStCO0lBRS9CLDJFQUEyRTtJQUMzRSxTQUFnQixZQUFZLENBQUMsVUFBa0I7UUFDN0MsTUFBTSxVQUFVLEdBQUcsZ0JBQVMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxPQUFPLENBQUMsS0FBSyxFQUFFLEdBQUcsQ0FBQyxDQUFDO1FBQzdELElBQUksQ0FBQyxVQUFVLENBQUMsVUFBVSxDQUFDLEdBQUcsQ0FBQyxFQUFFO1lBQy9CLE9BQU8sS0FBSyxVQUFVLEVBQUUsQ0FBQztTQUMxQjtRQUNELE9BQU8sVUFBVSxDQUFDO0lBQ3BCLENBQUM7SUFORCxvQ0FNQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQge25vcm1hbGl6ZX0gZnJvbSAncGF0aCc7XG5cbi8qKiBOb3JtYWxpemVzIHRoZSBzcGVjaWZpZWQgcGF0aCB0byBjb25mb3JtIHdpdGggdGhlIHBvc2l4IHBhdGggZm9ybWF0LiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldFBvc2l4UGF0aChwYXRoU3RyaW5nOiBzdHJpbmcpIHtcbiAgY29uc3Qgbm9ybWFsaXplZCA9IG5vcm1hbGl6ZShwYXRoU3RyaW5nKS5yZXBsYWNlKC9cXFxcL2csICcvJyk7XG4gIGlmICghbm9ybWFsaXplZC5zdGFydHNXaXRoKCcuJykpIHtcbiAgICByZXR1cm4gYC4vJHtub3JtYWxpemVkfWA7XG4gIH1cbiAgcmV0dXJuIG5vcm1hbGl6ZWQ7XG59XG4iXX0=