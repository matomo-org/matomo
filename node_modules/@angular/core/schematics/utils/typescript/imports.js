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
        define("@angular/core/schematics/utils/typescript/imports", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.replaceImport = exports.getImportSpecifier = exports.getImportOfIdentifier = void 0;
    const ts = require("typescript");
    /** Gets import information about the specified identifier by using the Type checker. */
    function getImportOfIdentifier(typeChecker, node) {
        const symbol = typeChecker.getSymbolAtLocation(node);
        if (!symbol || !symbol.declarations.length) {
            return null;
        }
        const decl = symbol.declarations[0];
        if (!ts.isImportSpecifier(decl)) {
            return null;
        }
        const importDecl = decl.parent.parent.parent;
        if (!ts.isStringLiteral(importDecl.moduleSpecifier)) {
            return null;
        }
        return {
            // Handles aliased imports: e.g. "import {Component as myComp} from ...";
            name: decl.propertyName ? decl.propertyName.text : decl.name.text,
            importModule: importDecl.moduleSpecifier.text,
            node: importDecl
        };
    }
    exports.getImportOfIdentifier = getImportOfIdentifier;
    /**
     * Gets a top-level import specifier with a specific name that is imported from a particular module.
     * E.g. given a file that looks like:
     *
     * ```
     * import { Component, Directive } from '@angular/core';
     * import { Foo } from './foo';
     * ```
     *
     * Calling `getImportSpecifier(sourceFile, '@angular/core', 'Directive')` will yield the node
     * referring to `Directive` in the top import.
     *
     * @param sourceFile File in which to look for imports.
     * @param moduleName Name of the import's module.
     * @param specifierName Original name of the specifier to look for. Aliases will be resolved to
     *    their original name.
     */
    function getImportSpecifier(sourceFile, moduleName, specifierName) {
        for (const node of sourceFile.statements) {
            if (ts.isImportDeclaration(node) && ts.isStringLiteral(node.moduleSpecifier) &&
                node.moduleSpecifier.text === moduleName) {
                const namedBindings = node.importClause && node.importClause.namedBindings;
                if (namedBindings && ts.isNamedImports(namedBindings)) {
                    const match = findImportSpecifier(namedBindings.elements, specifierName);
                    if (match) {
                        return match;
                    }
                }
            }
        }
        return null;
    }
    exports.getImportSpecifier = getImportSpecifier;
    /**
     * Replaces an import inside a named imports node with a different one.
     * @param node Node that contains the imports.
     * @param existingImport Import that should be replaced.
     * @param newImportName Import that should be inserted.
     */
    function replaceImport(node, existingImport, newImportName) {
        const isAlreadyImported = findImportSpecifier(node.elements, newImportName);
        if (isAlreadyImported) {
            return node;
        }
        const existingImportNode = findImportSpecifier(node.elements, existingImport);
        if (!existingImportNode) {
            return node;
        }
        return ts.updateNamedImports(node, [
            ...node.elements.filter(current => current !== existingImportNode),
            // Create a new import while trying to preserve the alias of the old one.
            ts.createImportSpecifier(existingImportNode.propertyName ? ts.createIdentifier(newImportName) : undefined, existingImportNode.propertyName ? existingImportNode.name :
                ts.createIdentifier(newImportName))
        ]);
    }
    exports.replaceImport = replaceImport;
    /** Finds an import specifier with a particular name. */
    function findImportSpecifier(nodes, specifierName) {
        return nodes.find(element => {
            const { name, propertyName } = element;
            return propertyName ? propertyName.text === specifierName : name.text === specifierName;
        });
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW1wb3J0cy5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy91dGlscy90eXBlc2NyaXB0L2ltcG9ydHMudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HOzs7Ozs7Ozs7Ozs7O0lBRUgsaUNBQWlDO0lBUWpDLHdGQUF3RjtJQUN4RixTQUFnQixxQkFBcUIsQ0FBQyxXQUEyQixFQUFFLElBQW1CO1FBRXBGLE1BQU0sTUFBTSxHQUFHLFdBQVcsQ0FBQyxtQkFBbUIsQ0FBQyxJQUFJLENBQUMsQ0FBQztRQUVyRCxJQUFJLENBQUMsTUFBTSxJQUFJLENBQUMsTUFBTSxDQUFDLFlBQVksQ0FBQyxNQUFNLEVBQUU7WUFDMUMsT0FBTyxJQUFJLENBQUM7U0FDYjtRQUVELE1BQU0sSUFBSSxHQUFHLE1BQU0sQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFFcEMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxpQkFBaUIsQ0FBQyxJQUFJLENBQUMsRUFBRTtZQUMvQixPQUFPLElBQUksQ0FBQztTQUNiO1FBRUQsTUFBTSxVQUFVLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDO1FBRTdDLElBQUksQ0FBQyxFQUFFLENBQUMsZUFBZSxDQUFDLFVBQVUsQ0FBQyxlQUFlLENBQUMsRUFBRTtZQUNuRCxPQUFPLElBQUksQ0FBQztTQUNiO1FBRUQsT0FBTztZQUNMLHlFQUF5RTtZQUN6RSxJQUFJLEVBQUUsSUFBSSxDQUFDLFlBQVksQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSTtZQUNqRSxZQUFZLEVBQUUsVUFBVSxDQUFDLGVBQWUsQ0FBQyxJQUFJO1lBQzdDLElBQUksRUFBRSxVQUFVO1NBQ2pCLENBQUM7SUFDSixDQUFDO0lBMUJELHNEQTBCQztJQUdEOzs7Ozs7Ozs7Ozs7Ozs7O09BZ0JHO0lBQ0gsU0FBZ0Isa0JBQWtCLENBQzlCLFVBQXlCLEVBQUUsVUFBa0IsRUFBRSxhQUFxQjtRQUN0RSxLQUFLLE1BQU0sSUFBSSxJQUFJLFVBQVUsQ0FBQyxVQUFVLEVBQUU7WUFDeEMsSUFBSSxFQUFFLENBQUMsbUJBQW1CLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDLGVBQWUsQ0FBQyxJQUFJLENBQUMsZUFBZSxDQUFDO2dCQUN4RSxJQUFJLENBQUMsZUFBZSxDQUFDLElBQUksS0FBSyxVQUFVLEVBQUU7Z0JBQzVDLE1BQU0sYUFBYSxHQUFHLElBQUksQ0FBQyxZQUFZLElBQUksSUFBSSxDQUFDLFlBQVksQ0FBQyxhQUFhLENBQUM7Z0JBQzNFLElBQUksYUFBYSxJQUFJLEVBQUUsQ0FBQyxjQUFjLENBQUMsYUFBYSxDQUFDLEVBQUU7b0JBQ3JELE1BQU0sS0FBSyxHQUFHLG1CQUFtQixDQUFDLGFBQWEsQ0FBQyxRQUFRLEVBQUUsYUFBYSxDQUFDLENBQUM7b0JBQ3pFLElBQUksS0FBSyxFQUFFO3dCQUNULE9BQU8sS0FBSyxDQUFDO3FCQUNkO2lCQUNGO2FBQ0Y7U0FDRjtRQUVELE9BQU8sSUFBSSxDQUFDO0lBQ2QsQ0FBQztJQWhCRCxnREFnQkM7SUFHRDs7Ozs7T0FLRztJQUNILFNBQWdCLGFBQWEsQ0FDekIsSUFBcUIsRUFBRSxjQUFzQixFQUFFLGFBQXFCO1FBQ3RFLE1BQU0saUJBQWlCLEdBQUcsbUJBQW1CLENBQUMsSUFBSSxDQUFDLFFBQVEsRUFBRSxhQUFhLENBQUMsQ0FBQztRQUM1RSxJQUFJLGlCQUFpQixFQUFFO1lBQ3JCLE9BQU8sSUFBSSxDQUFDO1NBQ2I7UUFFRCxNQUFNLGtCQUFrQixHQUFHLG1CQUFtQixDQUFDLElBQUksQ0FBQyxRQUFRLEVBQUUsY0FBYyxDQUFDLENBQUM7UUFDOUUsSUFBSSxDQUFDLGtCQUFrQixFQUFFO1lBQ3ZCLE9BQU8sSUFBSSxDQUFDO1NBQ2I7UUFFRCxPQUFPLEVBQUUsQ0FBQyxrQkFBa0IsQ0FBQyxJQUFJLEVBQUU7WUFDakMsR0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sQ0FBQyxPQUFPLENBQUMsRUFBRSxDQUFDLE9BQU8sS0FBSyxrQkFBa0IsQ0FBQztZQUNsRSx5RUFBeUU7WUFDekUsRUFBRSxDQUFDLHFCQUFxQixDQUNwQixrQkFBa0IsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsQ0FBQyxhQUFhLENBQUMsQ0FBQyxDQUFDLENBQUMsU0FBUyxFQUNoRixrQkFBa0IsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxDQUFDO2dCQUN6QixFQUFFLENBQUMsZ0JBQWdCLENBQUMsYUFBYSxDQUFDLENBQUM7U0FDMUUsQ0FBQyxDQUFDO0lBQ0wsQ0FBQztJQXBCRCxzQ0FvQkM7SUFHRCx3REFBd0Q7SUFDeEQsU0FBUyxtQkFBbUIsQ0FDeEIsS0FBdUMsRUFBRSxhQUFxQjtRQUNoRSxPQUFPLEtBQUssQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEVBQUU7WUFDMUIsTUFBTSxFQUFDLElBQUksRUFBRSxZQUFZLEVBQUMsR0FBRyxPQUFPLENBQUM7WUFDckMsT0FBTyxZQUFZLENBQUMsQ0FBQyxDQUFDLFlBQVksQ0FBQyxJQUFJLEtBQUssYUFBYSxDQUFDLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxLQUFLLGFBQWEsQ0FBQztRQUMxRixDQUFDLENBQUMsQ0FBQztJQUNMLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmV4cG9ydCB0eXBlIEltcG9ydCA9IHtcbiAgbmFtZTogc3RyaW5nLFxuICBpbXBvcnRNb2R1bGU6IHN0cmluZyxcbiAgbm9kZTogdHMuSW1wb3J0RGVjbGFyYXRpb25cbn07XG5cbi8qKiBHZXRzIGltcG9ydCBpbmZvcm1hdGlvbiBhYm91dCB0aGUgc3BlY2lmaWVkIGlkZW50aWZpZXIgYnkgdXNpbmcgdGhlIFR5cGUgY2hlY2tlci4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRJbXBvcnRPZklkZW50aWZpZXIodHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBub2RlOiB0cy5JZGVudGlmaWVyKTogSW1wb3J0fFxuICAgIG51bGwge1xuICBjb25zdCBzeW1ib2wgPSB0eXBlQ2hlY2tlci5nZXRTeW1ib2xBdExvY2F0aW9uKG5vZGUpO1xuXG4gIGlmICghc3ltYm9sIHx8ICFzeW1ib2wuZGVjbGFyYXRpb25zLmxlbmd0aCkge1xuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgY29uc3QgZGVjbCA9IHN5bWJvbC5kZWNsYXJhdGlvbnNbMF07XG5cbiAgaWYgKCF0cy5pc0ltcG9ydFNwZWNpZmllcihkZWNsKSkge1xuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgY29uc3QgaW1wb3J0RGVjbCA9IGRlY2wucGFyZW50LnBhcmVudC5wYXJlbnQ7XG5cbiAgaWYgKCF0cy5pc1N0cmluZ0xpdGVyYWwoaW1wb3J0RGVjbC5tb2R1bGVTcGVjaWZpZXIpKSB7XG4gICAgcmV0dXJuIG51bGw7XG4gIH1cblxuICByZXR1cm4ge1xuICAgIC8vIEhhbmRsZXMgYWxpYXNlZCBpbXBvcnRzOiBlLmcuIFwiaW1wb3J0IHtDb21wb25lbnQgYXMgbXlDb21wfSBmcm9tIC4uLlwiO1xuICAgIG5hbWU6IGRlY2wucHJvcGVydHlOYW1lID8gZGVjbC5wcm9wZXJ0eU5hbWUudGV4dCA6IGRlY2wubmFtZS50ZXh0LFxuICAgIGltcG9ydE1vZHVsZTogaW1wb3J0RGVjbC5tb2R1bGVTcGVjaWZpZXIudGV4dCxcbiAgICBub2RlOiBpbXBvcnREZWNsXG4gIH07XG59XG5cblxuLyoqXG4gKiBHZXRzIGEgdG9wLWxldmVsIGltcG9ydCBzcGVjaWZpZXIgd2l0aCBhIHNwZWNpZmljIG5hbWUgdGhhdCBpcyBpbXBvcnRlZCBmcm9tIGEgcGFydGljdWxhciBtb2R1bGUuXG4gKiBFLmcuIGdpdmVuIGEgZmlsZSB0aGF0IGxvb2tzIGxpa2U6XG4gKlxuICogYGBgXG4gKiBpbXBvcnQgeyBDb21wb25lbnQsIERpcmVjdGl2ZSB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuICogaW1wb3J0IHsgRm9vIH0gZnJvbSAnLi9mb28nO1xuICogYGBgXG4gKlxuICogQ2FsbGluZyBgZ2V0SW1wb3J0U3BlY2lmaWVyKHNvdXJjZUZpbGUsICdAYW5ndWxhci9jb3JlJywgJ0RpcmVjdGl2ZScpYCB3aWxsIHlpZWxkIHRoZSBub2RlXG4gKiByZWZlcnJpbmcgdG8gYERpcmVjdGl2ZWAgaW4gdGhlIHRvcCBpbXBvcnQuXG4gKlxuICogQHBhcmFtIHNvdXJjZUZpbGUgRmlsZSBpbiB3aGljaCB0byBsb29rIGZvciBpbXBvcnRzLlxuICogQHBhcmFtIG1vZHVsZU5hbWUgTmFtZSBvZiB0aGUgaW1wb3J0J3MgbW9kdWxlLlxuICogQHBhcmFtIHNwZWNpZmllck5hbWUgT3JpZ2luYWwgbmFtZSBvZiB0aGUgc3BlY2lmaWVyIHRvIGxvb2sgZm9yLiBBbGlhc2VzIHdpbGwgYmUgcmVzb2x2ZWQgdG9cbiAqICAgIHRoZWlyIG9yaWdpbmFsIG5hbWUuXG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBnZXRJbXBvcnRTcGVjaWZpZXIoXG4gICAgc291cmNlRmlsZTogdHMuU291cmNlRmlsZSwgbW9kdWxlTmFtZTogc3RyaW5nLCBzcGVjaWZpZXJOYW1lOiBzdHJpbmcpOiB0cy5JbXBvcnRTcGVjaWZpZXJ8bnVsbCB7XG4gIGZvciAoY29uc3Qgbm9kZSBvZiBzb3VyY2VGaWxlLnN0YXRlbWVudHMpIHtcbiAgICBpZiAodHMuaXNJbXBvcnREZWNsYXJhdGlvbihub2RlKSAmJiB0cy5pc1N0cmluZ0xpdGVyYWwobm9kZS5tb2R1bGVTcGVjaWZpZXIpICYmXG4gICAgICAgIG5vZGUubW9kdWxlU3BlY2lmaWVyLnRleHQgPT09IG1vZHVsZU5hbWUpIHtcbiAgICAgIGNvbnN0IG5hbWVkQmluZGluZ3MgPSBub2RlLmltcG9ydENsYXVzZSAmJiBub2RlLmltcG9ydENsYXVzZS5uYW1lZEJpbmRpbmdzO1xuICAgICAgaWYgKG5hbWVkQmluZGluZ3MgJiYgdHMuaXNOYW1lZEltcG9ydHMobmFtZWRCaW5kaW5ncykpIHtcbiAgICAgICAgY29uc3QgbWF0Y2ggPSBmaW5kSW1wb3J0U3BlY2lmaWVyKG5hbWVkQmluZGluZ3MuZWxlbWVudHMsIHNwZWNpZmllck5hbWUpO1xuICAgICAgICBpZiAobWF0Y2gpIHtcbiAgICAgICAgICByZXR1cm4gbWF0Y2g7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICByZXR1cm4gbnVsbDtcbn1cblxuXG4vKipcbiAqIFJlcGxhY2VzIGFuIGltcG9ydCBpbnNpZGUgYSBuYW1lZCBpbXBvcnRzIG5vZGUgd2l0aCBhIGRpZmZlcmVudCBvbmUuXG4gKiBAcGFyYW0gbm9kZSBOb2RlIHRoYXQgY29udGFpbnMgdGhlIGltcG9ydHMuXG4gKiBAcGFyYW0gZXhpc3RpbmdJbXBvcnQgSW1wb3J0IHRoYXQgc2hvdWxkIGJlIHJlcGxhY2VkLlxuICogQHBhcmFtIG5ld0ltcG9ydE5hbWUgSW1wb3J0IHRoYXQgc2hvdWxkIGJlIGluc2VydGVkLlxuICovXG5leHBvcnQgZnVuY3Rpb24gcmVwbGFjZUltcG9ydChcbiAgICBub2RlOiB0cy5OYW1lZEltcG9ydHMsIGV4aXN0aW5nSW1wb3J0OiBzdHJpbmcsIG5ld0ltcG9ydE5hbWU6IHN0cmluZykge1xuICBjb25zdCBpc0FscmVhZHlJbXBvcnRlZCA9IGZpbmRJbXBvcnRTcGVjaWZpZXIobm9kZS5lbGVtZW50cywgbmV3SW1wb3J0TmFtZSk7XG4gIGlmIChpc0FscmVhZHlJbXBvcnRlZCkge1xuICAgIHJldHVybiBub2RlO1xuICB9XG5cbiAgY29uc3QgZXhpc3RpbmdJbXBvcnROb2RlID0gZmluZEltcG9ydFNwZWNpZmllcihub2RlLmVsZW1lbnRzLCBleGlzdGluZ0ltcG9ydCk7XG4gIGlmICghZXhpc3RpbmdJbXBvcnROb2RlKSB7XG4gICAgcmV0dXJuIG5vZGU7XG4gIH1cblxuICByZXR1cm4gdHMudXBkYXRlTmFtZWRJbXBvcnRzKG5vZGUsIFtcbiAgICAuLi5ub2RlLmVsZW1lbnRzLmZpbHRlcihjdXJyZW50ID0+IGN1cnJlbnQgIT09IGV4aXN0aW5nSW1wb3J0Tm9kZSksXG4gICAgLy8gQ3JlYXRlIGEgbmV3IGltcG9ydCB3aGlsZSB0cnlpbmcgdG8gcHJlc2VydmUgdGhlIGFsaWFzIG9mIHRoZSBvbGQgb25lLlxuICAgIHRzLmNyZWF0ZUltcG9ydFNwZWNpZmllcihcbiAgICAgICAgZXhpc3RpbmdJbXBvcnROb2RlLnByb3BlcnR5TmFtZSA/IHRzLmNyZWF0ZUlkZW50aWZpZXIobmV3SW1wb3J0TmFtZSkgOiB1bmRlZmluZWQsXG4gICAgICAgIGV4aXN0aW5nSW1wb3J0Tm9kZS5wcm9wZXJ0eU5hbWUgPyBleGlzdGluZ0ltcG9ydE5vZGUubmFtZSA6XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0cy5jcmVhdGVJZGVudGlmaWVyKG5ld0ltcG9ydE5hbWUpKVxuICBdKTtcbn1cblxuXG4vKiogRmluZHMgYW4gaW1wb3J0IHNwZWNpZmllciB3aXRoIGEgcGFydGljdWxhciBuYW1lLiAqL1xuZnVuY3Rpb24gZmluZEltcG9ydFNwZWNpZmllcihcbiAgICBub2RlczogdHMuTm9kZUFycmF5PHRzLkltcG9ydFNwZWNpZmllcj4sIHNwZWNpZmllck5hbWU6IHN0cmluZyk6IHRzLkltcG9ydFNwZWNpZmllcnx1bmRlZmluZWQge1xuICByZXR1cm4gbm9kZXMuZmluZChlbGVtZW50ID0+IHtcbiAgICBjb25zdCB7bmFtZSwgcHJvcGVydHlOYW1lfSA9IGVsZW1lbnQ7XG4gICAgcmV0dXJuIHByb3BlcnR5TmFtZSA/IHByb3BlcnR5TmFtZS50ZXh0ID09PSBzcGVjaWZpZXJOYW1lIDogbmFtZS50ZXh0ID09PSBzcGVjaWZpZXJOYW1lO1xuICB9KTtcbn1cbiJdfQ==