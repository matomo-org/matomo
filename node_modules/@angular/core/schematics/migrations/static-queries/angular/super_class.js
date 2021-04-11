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
        define("@angular/core/schematics/migrations/static-queries/angular/super_class", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getSuperClassDeclarations = void 0;
    /**
     * Gets all chained super-class TypeScript declarations for the given class
     * by using the specified class metadata map.
     */
    function getSuperClassDeclarations(classDecl, classMetadataMap) {
        const declarations = [];
        let current = classMetadataMap.get(classDecl);
        while (current && current.superClass) {
            declarations.push(current.superClass);
            current = classMetadataMap.get(current.superClass);
        }
        return declarations;
    }
    exports.getSuperClassDeclarations = getSuperClassDeclarations;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3VwZXJfY2xhc3MuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy9zdGF0aWMtcXVlcmllcy9hbmd1bGFyL3N1cGVyX2NsYXNzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUtIOzs7T0FHRztJQUNILFNBQWdCLHlCQUF5QixDQUNyQyxTQUE4QixFQUFFLGdCQUFrQztRQUNwRSxNQUFNLFlBQVksR0FBMEIsRUFBRSxDQUFDO1FBRS9DLElBQUksT0FBTyxHQUFHLGdCQUFnQixDQUFDLEdBQUcsQ0FBQyxTQUFTLENBQUMsQ0FBQztRQUM5QyxPQUFPLE9BQU8sSUFBSSxPQUFPLENBQUMsVUFBVSxFQUFFO1lBQ3BDLFlBQVksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxDQUFDO1lBQ3RDLE9BQU8sR0FBRyxnQkFBZ0IsQ0FBQyxHQUFHLENBQUMsT0FBTyxDQUFDLFVBQVUsQ0FBQyxDQUFDO1NBQ3BEO1FBRUQsT0FBTyxZQUFZLENBQUM7SUFDdEIsQ0FBQztJQVhELDhEQVdDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuaW1wb3J0IHtDbGFzc01ldGFkYXRhTWFwfSBmcm9tICcuL25nX3F1ZXJ5X3Zpc2l0b3InO1xuXG4vKipcbiAqIEdldHMgYWxsIGNoYWluZWQgc3VwZXItY2xhc3MgVHlwZVNjcmlwdCBkZWNsYXJhdGlvbnMgZm9yIHRoZSBnaXZlbiBjbGFzc1xuICogYnkgdXNpbmcgdGhlIHNwZWNpZmllZCBjbGFzcyBtZXRhZGF0YSBtYXAuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRTdXBlckNsYXNzRGVjbGFyYXRpb25zKFxuICAgIGNsYXNzRGVjbDogdHMuQ2xhc3NEZWNsYXJhdGlvbiwgY2xhc3NNZXRhZGF0YU1hcDogQ2xhc3NNZXRhZGF0YU1hcCkge1xuICBjb25zdCBkZWNsYXJhdGlvbnM6IHRzLkNsYXNzRGVjbGFyYXRpb25bXSA9IFtdO1xuXG4gIGxldCBjdXJyZW50ID0gY2xhc3NNZXRhZGF0YU1hcC5nZXQoY2xhc3NEZWNsKTtcbiAgd2hpbGUgKGN1cnJlbnQgJiYgY3VycmVudC5zdXBlckNsYXNzKSB7XG4gICAgZGVjbGFyYXRpb25zLnB1c2goY3VycmVudC5zdXBlckNsYXNzKTtcbiAgICBjdXJyZW50ID0gY2xhc3NNZXRhZGF0YU1hcC5nZXQoY3VycmVudC5zdXBlckNsYXNzKTtcbiAgfVxuXG4gIHJldHVybiBkZWNsYXJhdGlvbnM7XG59XG4iXX0=