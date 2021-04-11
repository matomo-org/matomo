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
        define("@angular/core/schematics/utils/typescript/symbol", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isReferenceToImport = exports.getValueSymbolOfDeclaration = void 0;
    const ts = require("typescript");
    function getValueSymbolOfDeclaration(node, typeChecker) {
        let symbol = typeChecker.getSymbolAtLocation(node);
        while (symbol && symbol.flags & ts.SymbolFlags.Alias) {
            symbol = typeChecker.getAliasedSymbol(symbol);
        }
        return symbol;
    }
    exports.getValueSymbolOfDeclaration = getValueSymbolOfDeclaration;
    /** Checks whether a node is referring to a specific import specifier. */
    function isReferenceToImport(typeChecker, node, importSpecifier) {
        const nodeSymbol = typeChecker.getTypeAtLocation(node).getSymbol();
        const importSymbol = typeChecker.getTypeAtLocation(importSpecifier).getSymbol();
        return !!(nodeSymbol && importSymbol) &&
            nodeSymbol.valueDeclaration === importSymbol.valueDeclaration;
    }
    exports.isReferenceToImport = isReferenceToImport;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic3ltYm9sLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL3V0aWxzL3R5cGVzY3JpcHQvc3ltYm9sLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUVqQyxTQUFnQiwyQkFBMkIsQ0FBQyxJQUFhLEVBQUUsV0FBMkI7UUFFcEYsSUFBSSxNQUFNLEdBQUcsV0FBVyxDQUFDLG1CQUFtQixDQUFDLElBQUksQ0FBQyxDQUFDO1FBRW5ELE9BQU8sTUFBTSxJQUFJLE1BQU0sQ0FBQyxLQUFLLEdBQUcsRUFBRSxDQUFDLFdBQVcsQ0FBQyxLQUFLLEVBQUU7WUFDcEQsTUFBTSxHQUFHLFdBQVcsQ0FBQyxnQkFBZ0IsQ0FBQyxNQUFNLENBQUMsQ0FBQztTQUMvQztRQUVELE9BQU8sTUFBTSxDQUFDO0lBQ2hCLENBQUM7SUFURCxrRUFTQztJQUVELHlFQUF5RTtJQUN6RSxTQUFnQixtQkFBbUIsQ0FDL0IsV0FBMkIsRUFBRSxJQUFhLEVBQUUsZUFBbUM7UUFDakYsTUFBTSxVQUFVLEdBQUcsV0FBVyxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxDQUFDLFNBQVMsRUFBRSxDQUFDO1FBQ25FLE1BQU0sWUFBWSxHQUFHLFdBQVcsQ0FBQyxpQkFBaUIsQ0FBQyxlQUFlLENBQUMsQ0FBQyxTQUFTLEVBQUUsQ0FBQztRQUNoRixPQUFPLENBQUMsQ0FBQyxDQUFDLFVBQVUsSUFBSSxZQUFZLENBQUM7WUFDakMsVUFBVSxDQUFDLGdCQUFnQixLQUFLLFlBQVksQ0FBQyxnQkFBZ0IsQ0FBQztJQUNwRSxDQUFDO0lBTkQsa0RBTUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmV4cG9ydCBmdW5jdGlvbiBnZXRWYWx1ZVN5bWJvbE9mRGVjbGFyYXRpb24obm9kZTogdHMuTm9kZSwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyKTogdHMuU3ltYm9sfFxuICAgIHVuZGVmaW5lZCB7XG4gIGxldCBzeW1ib2wgPSB0eXBlQ2hlY2tlci5nZXRTeW1ib2xBdExvY2F0aW9uKG5vZGUpO1xuXG4gIHdoaWxlIChzeW1ib2wgJiYgc3ltYm9sLmZsYWdzICYgdHMuU3ltYm9sRmxhZ3MuQWxpYXMpIHtcbiAgICBzeW1ib2wgPSB0eXBlQ2hlY2tlci5nZXRBbGlhc2VkU3ltYm9sKHN5bWJvbCk7XG4gIH1cblxuICByZXR1cm4gc3ltYm9sO1xufVxuXG4vKiogQ2hlY2tzIHdoZXRoZXIgYSBub2RlIGlzIHJlZmVycmluZyB0byBhIHNwZWNpZmljIGltcG9ydCBzcGVjaWZpZXIuICovXG5leHBvcnQgZnVuY3Rpb24gaXNSZWZlcmVuY2VUb0ltcG9ydChcbiAgICB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsIG5vZGU6IHRzLk5vZGUsIGltcG9ydFNwZWNpZmllcjogdHMuSW1wb3J0U3BlY2lmaWVyKTogYm9vbGVhbiB7XG4gIGNvbnN0IG5vZGVTeW1ib2wgPSB0eXBlQ2hlY2tlci5nZXRUeXBlQXRMb2NhdGlvbihub2RlKS5nZXRTeW1ib2woKTtcbiAgY29uc3QgaW1wb3J0U3ltYm9sID0gdHlwZUNoZWNrZXIuZ2V0VHlwZUF0TG9jYXRpb24oaW1wb3J0U3BlY2lmaWVyKS5nZXRTeW1ib2woKTtcbiAgcmV0dXJuICEhKG5vZGVTeW1ib2wgJiYgaW1wb3J0U3ltYm9sKSAmJlxuICAgICAgbm9kZVN5bWJvbC52YWx1ZURlY2xhcmF0aW9uID09PSBpbXBvcnRTeW1ib2wudmFsdWVEZWNsYXJhdGlvbjtcbn1cbiJdfQ==