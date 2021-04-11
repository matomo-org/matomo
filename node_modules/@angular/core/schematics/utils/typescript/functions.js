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
        define("@angular/core/schematics/utils/typescript/functions", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.unwrapExpression = exports.isFunctionLikeDeclaration = void 0;
    const ts = require("typescript");
    /** Checks whether a given node is a function like declaration. */
    function isFunctionLikeDeclaration(node) {
        return ts.isFunctionDeclaration(node) || ts.isMethodDeclaration(node) ||
            ts.isArrowFunction(node) || ts.isFunctionExpression(node) ||
            ts.isGetAccessorDeclaration(node) || ts.isSetAccessorDeclaration(node);
    }
    exports.isFunctionLikeDeclaration = isFunctionLikeDeclaration;
    /**
     * Unwraps a given expression TypeScript node. Expressions can be wrapped within multiple
     * parentheses or as expression. e.g. "(((({exp}))))()". The function should return the
     * TypeScript node referring to the inner expression. e.g "exp".
     */
    function unwrapExpression(node) {
        if (ts.isParenthesizedExpression(node) || ts.isAsExpression(node)) {
            return unwrapExpression(node.expression);
        }
        else {
            return node;
        }
    }
    exports.unwrapExpression = unwrapExpression;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZnVuY3Rpb25zLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL3V0aWxzL3R5cGVzY3JpcHQvZnVuY3Rpb25zLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUVqQyxrRUFBa0U7SUFDbEUsU0FBZ0IseUJBQXlCLENBQUMsSUFBYTtRQUNyRCxPQUFPLEVBQUUsQ0FBQyxxQkFBcUIsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDO1lBQ2pFLEVBQUUsQ0FBQyxlQUFlLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDLG9CQUFvQixDQUFDLElBQUksQ0FBQztZQUN6RCxFQUFFLENBQUMsd0JBQXdCLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDLHdCQUF3QixDQUFDLElBQUksQ0FBQyxDQUFDO0lBQzdFLENBQUM7SUFKRCw4REFJQztJQUVEOzs7O09BSUc7SUFDSCxTQUFnQixnQkFBZ0IsQ0FBQyxJQUE4QztRQUM3RSxJQUFJLEVBQUUsQ0FBQyx5QkFBeUIsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxFQUFFO1lBQ2pFLE9BQU8sZ0JBQWdCLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDO1NBQzFDO2FBQU07WUFDTCxPQUFPLElBQUksQ0FBQztTQUNiO0lBQ0gsQ0FBQztJQU5ELDRDQU1DIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG4vKiogQ2hlY2tzIHdoZXRoZXIgYSBnaXZlbiBub2RlIGlzIGEgZnVuY3Rpb24gbGlrZSBkZWNsYXJhdGlvbi4gKi9cbmV4cG9ydCBmdW5jdGlvbiBpc0Z1bmN0aW9uTGlrZURlY2xhcmF0aW9uKG5vZGU6IHRzLk5vZGUpOiBub2RlIGlzIHRzLkZ1bmN0aW9uTGlrZURlY2xhcmF0aW9uIHtcbiAgcmV0dXJuIHRzLmlzRnVuY3Rpb25EZWNsYXJhdGlvbihub2RlKSB8fCB0cy5pc01ldGhvZERlY2xhcmF0aW9uKG5vZGUpIHx8XG4gICAgICB0cy5pc0Fycm93RnVuY3Rpb24obm9kZSkgfHwgdHMuaXNGdW5jdGlvbkV4cHJlc3Npb24obm9kZSkgfHxcbiAgICAgIHRzLmlzR2V0QWNjZXNzb3JEZWNsYXJhdGlvbihub2RlKSB8fCB0cy5pc1NldEFjY2Vzc29yRGVjbGFyYXRpb24obm9kZSk7XG59XG5cbi8qKlxuICogVW53cmFwcyBhIGdpdmVuIGV4cHJlc3Npb24gVHlwZVNjcmlwdCBub2RlLiBFeHByZXNzaW9ucyBjYW4gYmUgd3JhcHBlZCB3aXRoaW4gbXVsdGlwbGVcbiAqIHBhcmVudGhlc2VzIG9yIGFzIGV4cHJlc3Npb24uIGUuZy4gXCIoKCgoe2V4cH0pKSkpKClcIi4gVGhlIGZ1bmN0aW9uIHNob3VsZCByZXR1cm4gdGhlXG4gKiBUeXBlU2NyaXB0IG5vZGUgcmVmZXJyaW5nIHRvIHRoZSBpbm5lciBleHByZXNzaW9uLiBlLmcgXCJleHBcIi5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIHVud3JhcEV4cHJlc3Npb24obm9kZTogdHMuRXhwcmVzc2lvbnx0cy5QYXJlbnRoZXNpemVkRXhwcmVzc2lvbik6IHRzLkV4cHJlc3Npb24ge1xuICBpZiAodHMuaXNQYXJlbnRoZXNpemVkRXhwcmVzc2lvbihub2RlKSB8fCB0cy5pc0FzRXhwcmVzc2lvbihub2RlKSkge1xuICAgIHJldHVybiB1bndyYXBFeHByZXNzaW9uKG5vZGUuZXhwcmVzc2lvbik7XG4gIH0gZWxzZSB7XG4gICAgcmV0dXJuIG5vZGU7XG4gIH1cbn1cbiJdfQ==