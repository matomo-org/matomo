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
        define("@angular/core/schematics/utils/typescript/decorators", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/imports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getCallDecoratorImport = void 0;
    const ts = require("typescript");
    const imports_1 = require("@angular/core/schematics/utils/typescript/imports");
    function getCallDecoratorImport(typeChecker, decorator) {
        // Note that this does not cover the edge case where decorators are called from
        // a namespace import: e.g. "@core.Component()". This is not handled by Ngtsc either.
        if (!ts.isCallExpression(decorator.expression) ||
            !ts.isIdentifier(decorator.expression.expression)) {
            return null;
        }
        const identifier = decorator.expression.expression;
        return imports_1.getImportOfIdentifier(typeChecker, identifier);
    }
    exports.getCallDecoratorImport = getCallDecoratorImport;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGVjb3JhdG9ycy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy90eXBlc2NyaXB0L2RlY29yYXRvcnMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsaUNBQWlDO0lBRWpDLCtFQUF3RDtJQUV4RCxTQUFnQixzQkFBc0IsQ0FDbEMsV0FBMkIsRUFBRSxTQUF1QjtRQUN0RCwrRUFBK0U7UUFDL0UscUZBQXFGO1FBQ3JGLElBQUksQ0FBQyxFQUFFLENBQUMsZ0JBQWdCLENBQUMsU0FBUyxDQUFDLFVBQVUsQ0FBQztZQUMxQyxDQUFDLEVBQUUsQ0FBQyxZQUFZLENBQUMsU0FBUyxDQUFDLFVBQVUsQ0FBQyxVQUFVLENBQUMsRUFBRTtZQUNyRCxPQUFPLElBQUksQ0FBQztTQUNiO1FBRUQsTUFBTSxVQUFVLEdBQUcsU0FBUyxDQUFDLFVBQVUsQ0FBQyxVQUFVLENBQUM7UUFDbkQsT0FBTywrQkFBcUIsQ0FBQyxXQUFXLEVBQUUsVUFBVSxDQUFDLENBQUM7SUFDeEQsQ0FBQztJQVhELHdEQVdDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge2dldEltcG9ydE9mSWRlbnRpZmllciwgSW1wb3J0fSBmcm9tICcuL2ltcG9ydHMnO1xuXG5leHBvcnQgZnVuY3Rpb24gZ2V0Q2FsbERlY29yYXRvckltcG9ydChcbiAgICB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsIGRlY29yYXRvcjogdHMuRGVjb3JhdG9yKTogSW1wb3J0fG51bGwge1xuICAvLyBOb3RlIHRoYXQgdGhpcyBkb2VzIG5vdCBjb3ZlciB0aGUgZWRnZSBjYXNlIHdoZXJlIGRlY29yYXRvcnMgYXJlIGNhbGxlZCBmcm9tXG4gIC8vIGEgbmFtZXNwYWNlIGltcG9ydDogZS5nLiBcIkBjb3JlLkNvbXBvbmVudCgpXCIuIFRoaXMgaXMgbm90IGhhbmRsZWQgYnkgTmd0c2MgZWl0aGVyLlxuICBpZiAoIXRzLmlzQ2FsbEV4cHJlc3Npb24oZGVjb3JhdG9yLmV4cHJlc3Npb24pIHx8XG4gICAgICAhdHMuaXNJZGVudGlmaWVyKGRlY29yYXRvci5leHByZXNzaW9uLmV4cHJlc3Npb24pKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICBjb25zdCBpZGVudGlmaWVyID0gZGVjb3JhdG9yLmV4cHJlc3Npb24uZXhwcmVzc2lvbjtcbiAgcmV0dXJuIGdldEltcG9ydE9mSWRlbnRpZmllcih0eXBlQ2hlY2tlciwgaWRlbnRpZmllcik7XG59XG4iXX0=