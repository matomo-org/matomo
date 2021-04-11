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
        define("@angular/core/schematics/utils/typescript/find_base_classes", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/class_declaration"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.findBaseClassDeclarations = void 0;
    const ts = require("typescript");
    const class_declaration_1 = require("@angular/core/schematics/utils/typescript/class_declaration");
    /** Gets all base class declarations of the specified class declaration. */
    function findBaseClassDeclarations(node, typeChecker) {
        const result = [];
        let currentClass = node;
        while (currentClass) {
            const baseTypes = class_declaration_1.getBaseTypeIdentifiers(currentClass);
            if (!baseTypes || baseTypes.length !== 1) {
                break;
            }
            const symbol = typeChecker.getTypeAtLocation(baseTypes[0]).getSymbol();
            // Note: `ts.Symbol#valueDeclaration` can be undefined. TypeScript has an incorrect type
            // for this: https://github.com/microsoft/TypeScript/issues/24706.
            if (!symbol || !symbol.valueDeclaration || !ts.isClassDeclaration(symbol.valueDeclaration)) {
                break;
            }
            result.push({ identifier: baseTypes[0], node: symbol.valueDeclaration });
            currentClass = symbol.valueDeclaration;
        }
        return result;
    }
    exports.findBaseClassDeclarations = findBaseClassDeclarations;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmluZF9iYXNlX2NsYXNzZXMuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvdXRpbHMvdHlwZXNjcmlwdC9maW5kX2Jhc2VfY2xhc3Nlcy50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFDakMsbUdBQTJEO0lBRTNELDJFQUEyRTtJQUMzRSxTQUFnQix5QkFBeUIsQ0FBQyxJQUF5QixFQUFFLFdBQTJCO1FBQzlGLE1BQU0sTUFBTSxHQUE2RCxFQUFFLENBQUM7UUFDNUUsSUFBSSxZQUFZLEdBQUcsSUFBSSxDQUFDO1FBRXhCLE9BQU8sWUFBWSxFQUFFO1lBQ25CLE1BQU0sU0FBUyxHQUFHLDBDQUFzQixDQUFDLFlBQVksQ0FBQyxDQUFDO1lBQ3ZELElBQUksQ0FBQyxTQUFTLElBQUksU0FBUyxDQUFDLE1BQU0sS0FBSyxDQUFDLEVBQUU7Z0JBQ3hDLE1BQU07YUFDUDtZQUNELE1BQU0sTUFBTSxHQUFHLFdBQVcsQ0FBQyxpQkFBaUIsQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLEVBQUUsQ0FBQztZQUN2RSx3RkFBd0Y7WUFDeEYsa0VBQWtFO1lBQ2xFLElBQUksQ0FBQyxNQUFNLElBQUksQ0FBQyxNQUFNLENBQUMsZ0JBQWdCLElBQUksQ0FBQyxFQUFFLENBQUMsa0JBQWtCLENBQUMsTUFBTSxDQUFDLGdCQUFnQixDQUFDLEVBQUU7Z0JBQzFGLE1BQU07YUFDUDtZQUNELE1BQU0sQ0FBQyxJQUFJLENBQUMsRUFBQyxVQUFVLEVBQUUsU0FBUyxDQUFDLENBQUMsQ0FBQyxFQUFFLElBQUksRUFBRSxNQUFNLENBQUMsZ0JBQWdCLEVBQUMsQ0FBQyxDQUFDO1lBQ3ZFLFlBQVksR0FBRyxNQUFNLENBQUMsZ0JBQWdCLENBQUM7U0FDeEM7UUFDRCxPQUFPLE1BQU0sQ0FBQztJQUNoQixDQUFDO0lBbkJELDhEQW1CQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcbmltcG9ydCB7Z2V0QmFzZVR5cGVJZGVudGlmaWVyc30gZnJvbSAnLi9jbGFzc19kZWNsYXJhdGlvbic7XG5cbi8qKiBHZXRzIGFsbCBiYXNlIGNsYXNzIGRlY2xhcmF0aW9ucyBvZiB0aGUgc3BlY2lmaWVkIGNsYXNzIGRlY2xhcmF0aW9uLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGZpbmRCYXNlQ2xhc3NEZWNsYXJhdGlvbnMobm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbiwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyKSB7XG4gIGNvbnN0IHJlc3VsdDoge2lkZW50aWZpZXI6IHRzLklkZW50aWZpZXIsIG5vZGU6IHRzLkNsYXNzRGVjbGFyYXRpb259W10gPSBbXTtcbiAgbGV0IGN1cnJlbnRDbGFzcyA9IG5vZGU7XG5cbiAgd2hpbGUgKGN1cnJlbnRDbGFzcykge1xuICAgIGNvbnN0IGJhc2VUeXBlcyA9IGdldEJhc2VUeXBlSWRlbnRpZmllcnMoY3VycmVudENsYXNzKTtcbiAgICBpZiAoIWJhc2VUeXBlcyB8fCBiYXNlVHlwZXMubGVuZ3RoICE9PSAxKSB7XG4gICAgICBicmVhaztcbiAgICB9XG4gICAgY29uc3Qgc3ltYm9sID0gdHlwZUNoZWNrZXIuZ2V0VHlwZUF0TG9jYXRpb24oYmFzZVR5cGVzWzBdKS5nZXRTeW1ib2woKTtcbiAgICAvLyBOb3RlOiBgdHMuU3ltYm9sI3ZhbHVlRGVjbGFyYXRpb25gIGNhbiBiZSB1bmRlZmluZWQuIFR5cGVTY3JpcHQgaGFzIGFuIGluY29ycmVjdCB0eXBlXG4gICAgLy8gZm9yIHRoaXM6IGh0dHBzOi8vZ2l0aHViLmNvbS9taWNyb3NvZnQvVHlwZVNjcmlwdC9pc3N1ZXMvMjQ3MDYuXG4gICAgaWYgKCFzeW1ib2wgfHwgIXN5bWJvbC52YWx1ZURlY2xhcmF0aW9uIHx8ICF0cy5pc0NsYXNzRGVjbGFyYXRpb24oc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pKSB7XG4gICAgICBicmVhaztcbiAgICB9XG4gICAgcmVzdWx0LnB1c2goe2lkZW50aWZpZXI6IGJhc2VUeXBlc1swXSwgbm9kZTogc3ltYm9sLnZhbHVlRGVjbGFyYXRpb259KTtcbiAgICBjdXJyZW50Q2xhc3MgPSBzeW1ib2wudmFsdWVEZWNsYXJhdGlvbjtcbiAgfVxuICByZXR1cm4gcmVzdWx0O1xufVxuIl19