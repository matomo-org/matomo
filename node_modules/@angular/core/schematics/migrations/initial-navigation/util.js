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
        define("@angular/core/schematics/migrations/initial-navigation/util", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/imports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isExtraOptions = exports.isRouterModuleForRoot = void 0;
    const ts = require("typescript");
    const imports_1 = require("@angular/core/schematics/utils/typescript/imports");
    /** Determine whether a node is a ModuleWithProviders type reference node without a generic type */
    function isRouterModuleForRoot(typeChecker, node) {
        if (!ts.isCallExpression(node) || !ts.isPropertyAccessExpression(node.expression) ||
            !ts.isIdentifier(node.expression.expression) || node.expression.name.text !== 'forRoot') {
            return false;
        }
        const imp = imports_1.getImportOfIdentifier(typeChecker, node.expression.expression);
        return !!imp && imp.name === 'RouterModule' && imp.importModule === '@angular/router' &&
            !node.typeArguments;
    }
    exports.isRouterModuleForRoot = isRouterModuleForRoot;
    function isExtraOptions(typeChecker, node) {
        if (!ts.isTypeReferenceNode(node) || !ts.isIdentifier(node.typeName)) {
            return false;
        }
        const imp = imports_1.getImportOfIdentifier(typeChecker, node.typeName);
        return imp !== null && imp.name === 'ExtraOptions' && imp.importModule === '@angular/router' &&
            !node.typeArguments;
    }
    exports.isExtraOptions = isExtraOptions;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidXRpbC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL2luaXRpYWwtbmF2aWdhdGlvbi91dGlsLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUNqQywrRUFBcUU7SUFFckUsbUdBQW1HO0lBQ25HLFNBQWdCLHFCQUFxQixDQUNqQyxXQUEyQixFQUFFLElBQWE7UUFDNUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQywwQkFBMEIsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDO1lBQzdFLENBQUMsRUFBRSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLFVBQVUsQ0FBQyxJQUFJLElBQUksQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLElBQUksS0FBSyxTQUFTLEVBQUU7WUFDM0YsT0FBTyxLQUFLLENBQUM7U0FDZDtRQUNELE1BQU0sR0FBRyxHQUFHLCtCQUFxQixDQUFDLFdBQVcsRUFBRSxJQUFJLENBQUMsVUFBVSxDQUFDLFVBQVUsQ0FBQyxDQUFDO1FBQzNFLE9BQU8sQ0FBQyxDQUFDLEdBQUcsSUFBSSxHQUFHLENBQUMsSUFBSSxLQUFLLGNBQWMsSUFBSSxHQUFHLENBQUMsWUFBWSxLQUFLLGlCQUFpQjtZQUNqRixDQUFDLElBQUksQ0FBQyxhQUFhLENBQUM7SUFDMUIsQ0FBQztJQVRELHNEQVNDO0lBRUQsU0FBZ0IsY0FBYyxDQUMxQixXQUEyQixFQUFFLElBQWE7UUFDNUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxtQkFBbUIsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLFFBQVEsQ0FBQyxFQUFFO1lBQ3BFLE9BQU8sS0FBSyxDQUFDO1NBQ2Q7UUFFRCxNQUFNLEdBQUcsR0FBRywrQkFBcUIsQ0FBQyxXQUFXLEVBQUUsSUFBSSxDQUFDLFFBQVEsQ0FBQyxDQUFDO1FBQzlELE9BQU8sR0FBRyxLQUFLLElBQUksSUFBSSxHQUFHLENBQUMsSUFBSSxLQUFLLGNBQWMsSUFBSSxHQUFHLENBQUMsWUFBWSxLQUFLLGlCQUFpQjtZQUN4RixDQUFDLElBQUksQ0FBQyxhQUFhLENBQUM7SUFDMUIsQ0FBQztJQVRELHdDQVNDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuaW1wb3J0IHtnZXRJbXBvcnRPZklkZW50aWZpZXJ9IGZyb20gJy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvaW1wb3J0cyc7XG5cbi8qKiBEZXRlcm1pbmUgd2hldGhlciBhIG5vZGUgaXMgYSBNb2R1bGVXaXRoUHJvdmlkZXJzIHR5cGUgcmVmZXJlbmNlIG5vZGUgd2l0aG91dCBhIGdlbmVyaWMgdHlwZSAqL1xuZXhwb3J0IGZ1bmN0aW9uIGlzUm91dGVyTW9kdWxlRm9yUm9vdChcbiAgICB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsIG5vZGU6IHRzLk5vZGUpOiBub2RlIGlzIHRzLkNhbGxFeHByZXNzaW9uIHtcbiAgaWYgKCF0cy5pc0NhbGxFeHByZXNzaW9uKG5vZGUpIHx8ICF0cy5pc1Byb3BlcnR5QWNjZXNzRXhwcmVzc2lvbihub2RlLmV4cHJlc3Npb24pIHx8XG4gICAgICAhdHMuaXNJZGVudGlmaWVyKG5vZGUuZXhwcmVzc2lvbi5leHByZXNzaW9uKSB8fCBub2RlLmV4cHJlc3Npb24ubmFtZS50ZXh0ICE9PSAnZm9yUm9vdCcpIHtcbiAgICByZXR1cm4gZmFsc2U7XG4gIH1cbiAgY29uc3QgaW1wID0gZ2V0SW1wb3J0T2ZJZGVudGlmaWVyKHR5cGVDaGVja2VyLCBub2RlLmV4cHJlc3Npb24uZXhwcmVzc2lvbik7XG4gIHJldHVybiAhIWltcCAmJiBpbXAubmFtZSA9PT0gJ1JvdXRlck1vZHVsZScgJiYgaW1wLmltcG9ydE1vZHVsZSA9PT0gJ0Bhbmd1bGFyL3JvdXRlcicgJiZcbiAgICAgICFub2RlLnR5cGVBcmd1bWVudHM7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBpc0V4dHJhT3B0aW9ucyhcbiAgICB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsIG5vZGU6IHRzLk5vZGUpOiBub2RlIGlzIHRzLlR5cGVSZWZlcmVuY2VOb2RlIHtcbiAgaWYgKCF0cy5pc1R5cGVSZWZlcmVuY2VOb2RlKG5vZGUpIHx8ICF0cy5pc0lkZW50aWZpZXIobm9kZS50eXBlTmFtZSkpIHtcbiAgICByZXR1cm4gZmFsc2U7XG4gIH1cblxuICBjb25zdCBpbXAgPSBnZXRJbXBvcnRPZklkZW50aWZpZXIodHlwZUNoZWNrZXIsIG5vZGUudHlwZU5hbWUpO1xuICByZXR1cm4gaW1wICE9PSBudWxsICYmIGltcC5uYW1lID09PSAnRXh0cmFPcHRpb25zJyAmJiBpbXAuaW1wb3J0TW9kdWxlID09PSAnQGFuZ3VsYXIvcm91dGVyJyAmJlxuICAgICAgIW5vZGUudHlwZUFyZ3VtZW50cztcbn1cbiJdfQ==