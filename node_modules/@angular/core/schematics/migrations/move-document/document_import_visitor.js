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
        define("@angular/core/schematics/migrations/move-document/document_import_visitor", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DocumentImportVisitor = exports.DOCUMENT_TOKEN_NAME = exports.PLATFORM_BROWSER_IMPORT = exports.COMMON_IMPORT = void 0;
    const ts = require("typescript");
    exports.COMMON_IMPORT = '@angular/common';
    exports.PLATFORM_BROWSER_IMPORT = '@angular/platform-browser';
    exports.DOCUMENT_TOKEN_NAME = 'DOCUMENT';
    /** Visitor that can be used to find a set of imports in a TypeScript file. */
    class DocumentImportVisitor {
        constructor(typeChecker) {
            this.typeChecker = typeChecker;
            this.importsMap = new Map();
        }
        visitNode(node) {
            if (ts.isNamedImports(node)) {
                this.visitNamedImport(node);
            }
            ts.forEachChild(node, node => this.visitNode(node));
        }
        visitNamedImport(node) {
            if (!node.elements || !node.elements.length) {
                return;
            }
            const importDeclaration = node.parent.parent;
            // If this is not a StringLiteral it will be a grammar error
            const moduleSpecifier = importDeclaration.moduleSpecifier;
            const sourceFile = node.getSourceFile();
            let imports = this.importsMap.get(sourceFile);
            if (!imports) {
                imports = {
                    platformBrowserImport: null,
                    commonImport: null,
                    documentElement: null,
                };
            }
            if (moduleSpecifier.text === exports.PLATFORM_BROWSER_IMPORT) {
                const documentElement = this.getDocumentElement(node);
                if (documentElement) {
                    imports.platformBrowserImport = node;
                    imports.documentElement = documentElement;
                }
            }
            else if (moduleSpecifier.text === exports.COMMON_IMPORT) {
                imports.commonImport = node;
            }
            else {
                return;
            }
            this.importsMap.set(sourceFile, imports);
        }
        getDocumentElement(node) {
            const elements = node.elements;
            return elements.find(el => (el.propertyName || el.name).escapedText === exports.DOCUMENT_TOKEN_NAME);
        }
    }
    exports.DocumentImportVisitor = DocumentImportVisitor;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZG9jdW1lbnRfaW1wb3J0X3Zpc2l0b3IuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb3JlL3NjaGVtYXRpY3MvbWlncmF0aW9ucy9tb3ZlLWRvY3VtZW50L2RvY3VtZW50X2ltcG9ydF92aXNpdG9yLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILGlDQUFpQztJQUVwQixRQUFBLGFBQWEsR0FBRyxpQkFBaUIsQ0FBQztJQUNsQyxRQUFBLHVCQUF1QixHQUFHLDJCQUEyQixDQUFDO0lBQ3RELFFBQUEsbUJBQW1CLEdBQUcsVUFBVSxDQUFDO0lBUzlDLDhFQUE4RTtJQUM5RSxNQUFhLHFCQUFxQjtRQUdoQyxZQUFtQixXQUEyQjtZQUEzQixnQkFBVyxHQUFYLFdBQVcsQ0FBZ0I7WUFGOUMsZUFBVSxHQUErQyxJQUFJLEdBQUcsRUFBRSxDQUFDO1FBRWxCLENBQUM7UUFFbEQsU0FBUyxDQUFDLElBQWE7WUFDckIsSUFBSSxFQUFFLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxFQUFFO2dCQUMzQixJQUFJLENBQUMsZ0JBQWdCLENBQUMsSUFBSSxDQUFDLENBQUM7YUFDN0I7WUFFRCxFQUFFLENBQUMsWUFBWSxDQUFDLElBQUksRUFBRSxJQUFJLENBQUMsRUFBRSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztRQUN0RCxDQUFDO1FBRU8sZ0JBQWdCLENBQUMsSUFBcUI7WUFDNUMsSUFBSSxDQUFDLElBQUksQ0FBQyxRQUFRLElBQUksQ0FBQyxJQUFJLENBQUMsUUFBUSxDQUFDLE1BQU0sRUFBRTtnQkFDM0MsT0FBTzthQUNSO1lBRUQsTUFBTSxpQkFBaUIsR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQztZQUM3Qyw0REFBNEQ7WUFDNUQsTUFBTSxlQUFlLEdBQUcsaUJBQWlCLENBQUMsZUFBbUMsQ0FBQztZQUM5RSxNQUFNLFVBQVUsR0FBRyxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUM7WUFDeEMsSUFBSSxPQUFPLEdBQUcsSUFBSSxDQUFDLFVBQVUsQ0FBQyxHQUFHLENBQUMsVUFBVSxDQUFDLENBQUM7WUFDOUMsSUFBSSxDQUFDLE9BQU8sRUFBRTtnQkFDWixPQUFPLEdBQUc7b0JBQ1IscUJBQXFCLEVBQUUsSUFBSTtvQkFDM0IsWUFBWSxFQUFFLElBQUk7b0JBQ2xCLGVBQWUsRUFBRSxJQUFJO2lCQUN0QixDQUFDO2FBQ0g7WUFFRCxJQUFJLGVBQWUsQ0FBQyxJQUFJLEtBQUssK0JBQXVCLEVBQUU7Z0JBQ3BELE1BQU0sZUFBZSxHQUFHLElBQUksQ0FBQyxrQkFBa0IsQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDdEQsSUFBSSxlQUFlLEVBQUU7b0JBQ25CLE9BQU8sQ0FBQyxxQkFBcUIsR0FBRyxJQUFJLENBQUM7b0JBQ3JDLE9BQU8sQ0FBQyxlQUFlLEdBQUcsZUFBZSxDQUFDO2lCQUMzQzthQUNGO2lCQUFNLElBQUksZUFBZSxDQUFDLElBQUksS0FBSyxxQkFBYSxFQUFFO2dCQUNqRCxPQUFPLENBQUMsWUFBWSxHQUFHLElBQUksQ0FBQzthQUM3QjtpQkFBTTtnQkFDTCxPQUFPO2FBQ1I7WUFDRCxJQUFJLENBQUMsVUFBVSxDQUFDLEdBQUcsQ0FBQyxVQUFVLEVBQUUsT0FBTyxDQUFDLENBQUM7UUFDM0MsQ0FBQztRQUVPLGtCQUFrQixDQUFDLElBQXFCO1lBQzlDLE1BQU0sUUFBUSxHQUFHLElBQUksQ0FBQyxRQUFRLENBQUM7WUFDL0IsT0FBTyxRQUFRLENBQUMsSUFBSSxDQUFDLEVBQUUsQ0FBQyxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsWUFBWSxJQUFJLEVBQUUsQ0FBQyxJQUFJLENBQUMsQ0FBQyxXQUFXLEtBQUssMkJBQW1CLENBQUMsQ0FBQztRQUMvRixDQUFDO0tBQ0Y7SUFqREQsc0RBaURDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5leHBvcnQgY29uc3QgQ09NTU9OX0lNUE9SVCA9ICdAYW5ndWxhci9jb21tb24nO1xuZXhwb3J0IGNvbnN0IFBMQVRGT1JNX0JST1dTRVJfSU1QT1JUID0gJ0Bhbmd1bGFyL3BsYXRmb3JtLWJyb3dzZXInO1xuZXhwb3J0IGNvbnN0IERPQ1VNRU5UX1RPS0VOX05BTUUgPSAnRE9DVU1FTlQnO1xuXG4vKiogVGhpcyBjb250YWlucyB0aGUgbWV0YWRhdGEgbmVjZXNzYXJ5IHRvIG1vdmUgaXRlbXMgZnJvbSBvbmUgaW1wb3J0IHRvIGFub3RoZXIgKi9cbmV4cG9ydCBpbnRlcmZhY2UgUmVzb2x2ZWREb2N1bWVudEltcG9ydCB7XG4gIHBsYXRmb3JtQnJvd3NlckltcG9ydDogdHMuTmFtZWRJbXBvcnRzfG51bGw7XG4gIGNvbW1vbkltcG9ydDogdHMuTmFtZWRJbXBvcnRzfG51bGw7XG4gIGRvY3VtZW50RWxlbWVudDogdHMuSW1wb3J0U3BlY2lmaWVyfG51bGw7XG59XG5cbi8qKiBWaXNpdG9yIHRoYXQgY2FuIGJlIHVzZWQgdG8gZmluZCBhIHNldCBvZiBpbXBvcnRzIGluIGEgVHlwZVNjcmlwdCBmaWxlLiAqL1xuZXhwb3J0IGNsYXNzIERvY3VtZW50SW1wb3J0VmlzaXRvciB7XG4gIGltcG9ydHNNYXA6IE1hcDx0cy5Tb3VyY2VGaWxlLCBSZXNvbHZlZERvY3VtZW50SW1wb3J0PiA9IG5ldyBNYXAoKTtcblxuICBjb25zdHJ1Y3RvcihwdWJsaWMgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyKSB7fVxuXG4gIHZpc2l0Tm9kZShub2RlOiB0cy5Ob2RlKSB7XG4gICAgaWYgKHRzLmlzTmFtZWRJbXBvcnRzKG5vZGUpKSB7XG4gICAgICB0aGlzLnZpc2l0TmFtZWRJbXBvcnQobm9kZSk7XG4gICAgfVxuXG4gICAgdHMuZm9yRWFjaENoaWxkKG5vZGUsIG5vZGUgPT4gdGhpcy52aXNpdE5vZGUobm9kZSkpO1xuICB9XG5cbiAgcHJpdmF0ZSB2aXNpdE5hbWVkSW1wb3J0KG5vZGU6IHRzLk5hbWVkSW1wb3J0cykge1xuICAgIGlmICghbm9kZS5lbGVtZW50cyB8fCAhbm9kZS5lbGVtZW50cy5sZW5ndGgpIHtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBpbXBvcnREZWNsYXJhdGlvbiA9IG5vZGUucGFyZW50LnBhcmVudDtcbiAgICAvLyBJZiB0aGlzIGlzIG5vdCBhIFN0cmluZ0xpdGVyYWwgaXQgd2lsbCBiZSBhIGdyYW1tYXIgZXJyb3JcbiAgICBjb25zdCBtb2R1bGVTcGVjaWZpZXIgPSBpbXBvcnREZWNsYXJhdGlvbi5tb2R1bGVTcGVjaWZpZXIgYXMgdHMuU3RyaW5nTGl0ZXJhbDtcbiAgICBjb25zdCBzb3VyY2VGaWxlID0gbm9kZS5nZXRTb3VyY2VGaWxlKCk7XG4gICAgbGV0IGltcG9ydHMgPSB0aGlzLmltcG9ydHNNYXAuZ2V0KHNvdXJjZUZpbGUpO1xuICAgIGlmICghaW1wb3J0cykge1xuICAgICAgaW1wb3J0cyA9IHtcbiAgICAgICAgcGxhdGZvcm1Ccm93c2VySW1wb3J0OiBudWxsLFxuICAgICAgICBjb21tb25JbXBvcnQ6IG51bGwsXG4gICAgICAgIGRvY3VtZW50RWxlbWVudDogbnVsbCxcbiAgICAgIH07XG4gICAgfVxuXG4gICAgaWYgKG1vZHVsZVNwZWNpZmllci50ZXh0ID09PSBQTEFURk9STV9CUk9XU0VSX0lNUE9SVCkge1xuICAgICAgY29uc3QgZG9jdW1lbnRFbGVtZW50ID0gdGhpcy5nZXREb2N1bWVudEVsZW1lbnQobm9kZSk7XG4gICAgICBpZiAoZG9jdW1lbnRFbGVtZW50KSB7XG4gICAgICAgIGltcG9ydHMucGxhdGZvcm1Ccm93c2VySW1wb3J0ID0gbm9kZTtcbiAgICAgICAgaW1wb3J0cy5kb2N1bWVudEVsZW1lbnQgPSBkb2N1bWVudEVsZW1lbnQ7XG4gICAgICB9XG4gICAgfSBlbHNlIGlmIChtb2R1bGVTcGVjaWZpZXIudGV4dCA9PT0gQ09NTU9OX0lNUE9SVCkge1xuICAgICAgaW1wb3J0cy5jb21tb25JbXBvcnQgPSBub2RlO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuICAgIHRoaXMuaW1wb3J0c01hcC5zZXQoc291cmNlRmlsZSwgaW1wb3J0cyk7XG4gIH1cblxuICBwcml2YXRlIGdldERvY3VtZW50RWxlbWVudChub2RlOiB0cy5OYW1lZEltcG9ydHMpOiB0cy5JbXBvcnRTcGVjaWZpZXJ8dW5kZWZpbmVkIHtcbiAgICBjb25zdCBlbGVtZW50cyA9IG5vZGUuZWxlbWVudHM7XG4gICAgcmV0dXJuIGVsZW1lbnRzLmZpbmQoZWwgPT4gKGVsLnByb3BlcnR5TmFtZSB8fCBlbC5uYW1lKS5lc2NhcGVkVGV4dCA9PT0gRE9DVU1FTlRfVE9LRU5fTkFNRSk7XG4gIH1cbn1cbiJdfQ==