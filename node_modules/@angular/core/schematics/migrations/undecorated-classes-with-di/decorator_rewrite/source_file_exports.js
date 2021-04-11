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
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/source_file_exports", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/symbol"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getExportSymbolsOfFile = void 0;
    const ts = require("typescript");
    const symbol_1 = require("@angular/core/schematics/utils/typescript/symbol");
    /** Computes the resolved exports of a given source file. */
    function getExportSymbolsOfFile(sf, typeChecker) {
        const exports = [];
        const resolvedExports = [];
        ts.forEachChild(sf, function visitNode(node) {
            if (ts.isClassDeclaration(node) || ts.isFunctionDeclaration(node) ||
                ts.isInterfaceDeclaration(node) &&
                    (ts.getCombinedModifierFlags(node) & ts.ModifierFlags.Export) !== 0) {
                if (node.name) {
                    exports.push({ exportName: node.name.text, identifier: node.name });
                }
            }
            else if (ts.isVariableStatement(node)) {
                for (const decl of node.declarationList.declarations) {
                    visitNode(decl);
                }
            }
            else if (ts.isVariableDeclaration(node)) {
                if ((ts.getCombinedModifierFlags(node) & ts.ModifierFlags.Export) != 0 &&
                    ts.isIdentifier(node.name)) {
                    exports.push({ exportName: node.name.text, identifier: node.name });
                }
            }
            else if (ts.isExportDeclaration(node)) {
                const { moduleSpecifier, exportClause } = node;
                if (!moduleSpecifier && exportClause && ts.isNamedExports(exportClause)) {
                    exportClause.elements.forEach(el => exports.push({
                        exportName: el.name.text,
                        identifier: el.propertyName ? el.propertyName : el.name
                    }));
                }
            }
        });
        exports.forEach(({ identifier, exportName }) => {
            const symbol = symbol_1.getValueSymbolOfDeclaration(identifier, typeChecker);
            if (symbol) {
                resolvedExports.push({ symbol, identifier, exportName });
            }
        });
        return resolvedExports;
    }
    exports.getExportSymbolsOfFile = getExportSymbolsOfFile;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic291cmNlX2ZpbGVfZXhwb3J0cy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3VuZGVjb3JhdGVkLWNsYXNzZXMtd2l0aC1kaS9kZWNvcmF0b3JfcmV3cml0ZS9zb3VyY2VfZmlsZV9leHBvcnRzLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUNqQyw2RUFBNkU7SUFRN0UsNERBQTREO0lBQzVELFNBQWdCLHNCQUFzQixDQUNsQyxFQUFpQixFQUFFLFdBQTJCO1FBQ2hELE1BQU0sT0FBTyxHQUFzRCxFQUFFLENBQUM7UUFDdEUsTUFBTSxlQUFlLEdBQXFCLEVBQUUsQ0FBQztRQUU3QyxFQUFFLENBQUMsWUFBWSxDQUFDLEVBQUUsRUFBRSxTQUFTLFNBQVMsQ0FBQyxJQUFJO1lBQ3pDLElBQUksRUFBRSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQyxxQkFBcUIsQ0FBQyxJQUFJLENBQUM7Z0JBQzdELEVBQUUsQ0FBQyxzQkFBc0IsQ0FBQyxJQUFJLENBQUM7b0JBQzNCLENBQUMsRUFBRSxDQUFDLHdCQUF3QixDQUFDLElBQXNCLENBQUMsR0FBRyxFQUFFLENBQUMsYUFBYSxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsRUFBRTtnQkFDN0YsSUFBSSxJQUFJLENBQUMsSUFBSSxFQUFFO29CQUNiLE9BQU8sQ0FBQyxJQUFJLENBQUMsRUFBQyxVQUFVLEVBQUUsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLEVBQUUsVUFBVSxFQUFFLElBQUksQ0FBQyxJQUFJLEVBQUMsQ0FBQyxDQUFDO2lCQUNuRTthQUNGO2lCQUFNLElBQUksRUFBRSxDQUFDLG1CQUFtQixDQUFDLElBQUksQ0FBQyxFQUFFO2dCQUN2QyxLQUFLLE1BQU0sSUFBSSxJQUFJLElBQUksQ0FBQyxlQUFlLENBQUMsWUFBWSxFQUFFO29CQUNwRCxTQUFTLENBQUMsSUFBSSxDQUFDLENBQUM7aUJBQ2pCO2FBQ0Y7aUJBQU0sSUFBSSxFQUFFLENBQUMscUJBQXFCLENBQUMsSUFBSSxDQUFDLEVBQUU7Z0JBQ3pDLElBQUksQ0FBQyxFQUFFLENBQUMsd0JBQXdCLENBQUMsSUFBSSxDQUFDLEdBQUcsRUFBRSxDQUFDLGFBQWEsQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDO29CQUNsRSxFQUFFLENBQUMsWUFBWSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsRUFBRTtvQkFDOUIsT0FBTyxDQUFDLElBQUksQ0FBQyxFQUFDLFVBQVUsRUFBRSxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxVQUFVLEVBQUUsSUFBSSxDQUFDLElBQUksRUFBQyxDQUFDLENBQUM7aUJBQ25FO2FBQ0Y7aUJBQU0sSUFBSSxFQUFFLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDLEVBQUU7Z0JBQ3ZDLE1BQU0sRUFBQyxlQUFlLEVBQUUsWUFBWSxFQUFDLEdBQUcsSUFBSSxDQUFDO2dCQUM3QyxJQUFJLENBQUMsZUFBZSxJQUFJLFlBQVksSUFBSSxFQUFFLENBQUMsY0FBYyxDQUFDLFlBQVksQ0FBQyxFQUFFO29CQUN2RSxZQUFZLENBQUMsUUFBUSxDQUFDLE9BQU8sQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUM7d0JBQy9DLFVBQVUsRUFBRSxFQUFFLENBQUMsSUFBSSxDQUFDLElBQUk7d0JBQ3hCLFVBQVUsRUFBRSxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsWUFBWSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSTtxQkFDeEQsQ0FBQyxDQUFDLENBQUM7aUJBQ0w7YUFDRjtRQUNILENBQUMsQ0FBQyxDQUFDO1FBRUgsT0FBTyxDQUFDLE9BQU8sQ0FBQyxDQUFDLEVBQUMsVUFBVSxFQUFFLFVBQVUsRUFBQyxFQUFFLEVBQUU7WUFDM0MsTUFBTSxNQUFNLEdBQUcsb0NBQTJCLENBQUMsVUFBVSxFQUFFLFdBQVcsQ0FBQyxDQUFDO1lBQ3BFLElBQUksTUFBTSxFQUFFO2dCQUNWLGVBQWUsQ0FBQyxJQUFJLENBQUMsRUFBQyxNQUFNLEVBQUUsVUFBVSxFQUFFLFVBQVUsRUFBQyxDQUFDLENBQUM7YUFDeEQ7UUFDSCxDQUFDLENBQUMsQ0FBQztRQUVILE9BQU8sZUFBZSxDQUFDO0lBQ3pCLENBQUM7SUF4Q0Qsd0RBd0NDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuaW1wb3J0IHtnZXRWYWx1ZVN5bWJvbE9mRGVjbGFyYXRpb259IGZyb20gJy4uLy4uLy4uL3V0aWxzL3R5cGVzY3JpcHQvc3ltYm9sJztcblxuZXhwb3J0IGludGVyZmFjZSBSZXNvbHZlZEV4cG9ydCB7XG4gIHN5bWJvbDogdHMuU3ltYm9sO1xuICBleHBvcnROYW1lOiBzdHJpbmc7XG4gIGlkZW50aWZpZXI6IHRzLklkZW50aWZpZXI7XG59XG5cbi8qKiBDb21wdXRlcyB0aGUgcmVzb2x2ZWQgZXhwb3J0cyBvZiBhIGdpdmVuIHNvdXJjZSBmaWxlLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldEV4cG9ydFN5bWJvbHNPZkZpbGUoXG4gICAgc2Y6IHRzLlNvdXJjZUZpbGUsIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlcik6IFJlc29sdmVkRXhwb3J0W10ge1xuICBjb25zdCBleHBvcnRzOiB7ZXhwb3J0TmFtZTogc3RyaW5nLCBpZGVudGlmaWVyOiB0cy5JZGVudGlmaWVyfVtdID0gW107XG4gIGNvbnN0IHJlc29sdmVkRXhwb3J0czogUmVzb2x2ZWRFeHBvcnRbXSA9IFtdO1xuXG4gIHRzLmZvckVhY2hDaGlsZChzZiwgZnVuY3Rpb24gdmlzaXROb2RlKG5vZGUpIHtcbiAgICBpZiAodHMuaXNDbGFzc0RlY2xhcmF0aW9uKG5vZGUpIHx8IHRzLmlzRnVuY3Rpb25EZWNsYXJhdGlvbihub2RlKSB8fFxuICAgICAgICB0cy5pc0ludGVyZmFjZURlY2xhcmF0aW9uKG5vZGUpICYmXG4gICAgICAgICAgICAodHMuZ2V0Q29tYmluZWRNb2RpZmllckZsYWdzKG5vZGUgYXMgdHMuRGVjbGFyYXRpb24pICYgdHMuTW9kaWZpZXJGbGFncy5FeHBvcnQpICE9PSAwKSB7XG4gICAgICBpZiAobm9kZS5uYW1lKSB7XG4gICAgICAgIGV4cG9ydHMucHVzaCh7ZXhwb3J0TmFtZTogbm9kZS5uYW1lLnRleHQsIGlkZW50aWZpZXI6IG5vZGUubmFtZX0pO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAodHMuaXNWYXJpYWJsZVN0YXRlbWVudChub2RlKSkge1xuICAgICAgZm9yIChjb25zdCBkZWNsIG9mIG5vZGUuZGVjbGFyYXRpb25MaXN0LmRlY2xhcmF0aW9ucykge1xuICAgICAgICB2aXNpdE5vZGUoZGVjbCk7XG4gICAgICB9XG4gICAgfSBlbHNlIGlmICh0cy5pc1ZhcmlhYmxlRGVjbGFyYXRpb24obm9kZSkpIHtcbiAgICAgIGlmICgodHMuZ2V0Q29tYmluZWRNb2RpZmllckZsYWdzKG5vZGUpICYgdHMuTW9kaWZpZXJGbGFncy5FeHBvcnQpICE9IDAgJiZcbiAgICAgICAgICB0cy5pc0lkZW50aWZpZXIobm9kZS5uYW1lKSkge1xuICAgICAgICBleHBvcnRzLnB1c2goe2V4cG9ydE5hbWU6IG5vZGUubmFtZS50ZXh0LCBpZGVudGlmaWVyOiBub2RlLm5hbWV9KTtcbiAgICAgIH1cbiAgICB9IGVsc2UgaWYgKHRzLmlzRXhwb3J0RGVjbGFyYXRpb24obm9kZSkpIHtcbiAgICAgIGNvbnN0IHttb2R1bGVTcGVjaWZpZXIsIGV4cG9ydENsYXVzZX0gPSBub2RlO1xuICAgICAgaWYgKCFtb2R1bGVTcGVjaWZpZXIgJiYgZXhwb3J0Q2xhdXNlICYmIHRzLmlzTmFtZWRFeHBvcnRzKGV4cG9ydENsYXVzZSkpIHtcbiAgICAgICAgZXhwb3J0Q2xhdXNlLmVsZW1lbnRzLmZvckVhY2goZWwgPT4gZXhwb3J0cy5wdXNoKHtcbiAgICAgICAgICBleHBvcnROYW1lOiBlbC5uYW1lLnRleHQsXG4gICAgICAgICAgaWRlbnRpZmllcjogZWwucHJvcGVydHlOYW1lID8gZWwucHJvcGVydHlOYW1lIDogZWwubmFtZVxuICAgICAgICB9KSk7XG4gICAgICB9XG4gICAgfVxuICB9KTtcblxuICBleHBvcnRzLmZvckVhY2goKHtpZGVudGlmaWVyLCBleHBvcnROYW1lfSkgPT4ge1xuICAgIGNvbnN0IHN5bWJvbCA9IGdldFZhbHVlU3ltYm9sT2ZEZWNsYXJhdGlvbihpZGVudGlmaWVyLCB0eXBlQ2hlY2tlcik7XG4gICAgaWYgKHN5bWJvbCkge1xuICAgICAgcmVzb2x2ZWRFeHBvcnRzLnB1c2goe3N5bWJvbCwgaWRlbnRpZmllciwgZXhwb3J0TmFtZX0pO1xuICAgIH1cbiAgfSk7XG5cbiAgcmV0dXJuIHJlc29sdmVkRXhwb3J0cztcbn1cbiJdfQ==