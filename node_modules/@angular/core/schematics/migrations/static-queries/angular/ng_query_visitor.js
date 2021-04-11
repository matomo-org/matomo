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
        define("@angular/core/schematics/migrations/static-queries/angular/ng_query_visitor", ["require", "exports", "typescript", "@angular/core/schematics/utils/ng_decorators", "@angular/core/schematics/utils/typescript/class_declaration", "@angular/core/schematics/utils/typescript/property_name", "@angular/core/schematics/migrations/static-queries/angular/directive_inputs", "@angular/core/schematics/migrations/static-queries/angular/query-definition"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.NgQueryResolveVisitor = void 0;
    const ts = require("typescript");
    const ng_decorators_1 = require("@angular/core/schematics/utils/ng_decorators");
    const class_declaration_1 = require("@angular/core/schematics/utils/typescript/class_declaration");
    const property_name_1 = require("@angular/core/schematics/utils/typescript/property_name");
    const directive_inputs_1 = require("@angular/core/schematics/migrations/static-queries/angular/directive_inputs");
    const query_definition_1 = require("@angular/core/schematics/migrations/static-queries/angular/query-definition");
    /**
     * Visitor that can be used to determine Angular queries within given TypeScript nodes.
     * Besides resolving queries, the visitor also records class relations and searches for
     * Angular input setters which can be used to analyze the timing usage of a given query.
     */
    class NgQueryResolveVisitor {
        constructor(typeChecker) {
            this.typeChecker = typeChecker;
            /** Resolved Angular query definitions. */
            this.resolvedQueries = new Map();
            /** Maps a class declaration to its class metadata. */
            this.classMetadata = new Map();
        }
        visitNode(node) {
            switch (node.kind) {
                case ts.SyntaxKind.PropertyDeclaration:
                    this.visitPropertyDeclaration(node);
                    break;
                case ts.SyntaxKind.ClassDeclaration:
                    this.visitClassDeclaration(node);
                    break;
                case ts.SyntaxKind.GetAccessor:
                case ts.SyntaxKind.SetAccessor:
                    this.visitAccessorDeclaration(node);
                    break;
            }
            ts.forEachChild(node, n => this.visitNode(n));
        }
        visitPropertyDeclaration(node) {
            this._recordQueryDeclaration(node, node, property_name_1.getPropertyNameText(node.name));
        }
        visitAccessorDeclaration(node) {
            this._recordQueryDeclaration(node, null, property_name_1.getPropertyNameText(node.name));
        }
        visitClassDeclaration(node) {
            this._recordClassInputSetters(node);
            this._recordClassInheritances(node);
        }
        _recordQueryDeclaration(node, property, queryName) {
            if (!node.decorators || !node.decorators.length) {
                return;
            }
            const ngDecorators = ng_decorators_1.getAngularDecorators(this.typeChecker, node.decorators);
            const queryDecorator = ngDecorators.find(({ name }) => name === 'ViewChild' || name === 'ContentChild');
            // Ensure that the current property declaration is defining a query.
            if (!queryDecorator) {
                return;
            }
            const queryContainer = class_declaration_1.findParentClassDeclaration(node);
            // If the query is not located within a class declaration, skip this node.
            if (!queryContainer) {
                return;
            }
            const sourceFile = node.getSourceFile();
            const newQueries = this.resolvedQueries.get(sourceFile) || [];
            this.resolvedQueries.set(sourceFile, newQueries.concat({
                name: queryName,
                type: queryDecorator.name === 'ViewChild' ? query_definition_1.QueryType.ViewChild : query_definition_1.QueryType.ContentChild,
                node,
                property,
                decorator: queryDecorator,
                container: queryContainer,
            }));
        }
        _recordClassInputSetters(node) {
            const resolvedInputNames = directive_inputs_1.getInputNamesOfClass(node, this.typeChecker);
            if (resolvedInputNames) {
                const classMetadata = this._getClassMetadata(node);
                classMetadata.ngInputNames = resolvedInputNames;
                this.classMetadata.set(node, classMetadata);
            }
        }
        _recordClassInheritances(node) {
            const baseTypes = class_declaration_1.getBaseTypeIdentifiers(node);
            if (!baseTypes || baseTypes.length !== 1) {
                return;
            }
            const superClass = baseTypes[0];
            const baseClassMetadata = this._getClassMetadata(node);
            // We need to resolve the value declaration through the resolved type as the base
            // class could be declared in different source files and the local symbol won't
            // contain a value declaration as the value is not declared locally.
            const symbol = this.typeChecker.getTypeAtLocation(superClass).getSymbol();
            if (symbol && symbol.valueDeclaration && ts.isClassDeclaration(symbol.valueDeclaration)) {
                const extendedClass = symbol.valueDeclaration;
                const classMetadataExtended = this._getClassMetadata(extendedClass);
                // Record all classes that derive from the given class. This makes it easy to
                // determine all classes that could potentially use inherited queries statically.
                classMetadataExtended.derivedClasses.push(node);
                this.classMetadata.set(extendedClass, classMetadataExtended);
                // Record the super class of the current class.
                baseClassMetadata.superClass = extendedClass;
                this.classMetadata.set(node, baseClassMetadata);
            }
        }
        _getClassMetadata(node) {
            return this.classMetadata.get(node) || { derivedClasses: [], superClass: null, ngInputNames: [] };
        }
    }
    exports.NgQueryResolveVisitor = NgQueryResolveVisitor;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfcXVlcnlfdmlzaXRvci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3N0YXRpYy1xdWVyaWVzL2FuZ3VsYXIvbmdfcXVlcnlfdmlzaXRvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFHakMsZ0ZBQWtFO0lBQ2xFLG1HQUErRztJQUMvRywyRkFBNEU7SUFFNUUsa0hBQXdEO0lBQ3hELGtIQUFnRTtJQWtCaEU7Ozs7T0FJRztJQUNILE1BQWEscUJBQXFCO1FBT2hDLFlBQW1CLFdBQTJCO1lBQTNCLGdCQUFXLEdBQVgsV0FBVyxDQUFnQjtZQU45QywwQ0FBMEM7WUFDMUMsb0JBQWUsR0FBRyxJQUFJLEdBQUcsRUFBc0MsQ0FBQztZQUVoRSxzREFBc0Q7WUFDdEQsa0JBQWEsR0FBcUIsSUFBSSxHQUFHLEVBQUUsQ0FBQztRQUVLLENBQUM7UUFFbEQsU0FBUyxDQUFDLElBQWE7WUFDckIsUUFBUSxJQUFJLENBQUMsSUFBSSxFQUFFO2dCQUNqQixLQUFLLEVBQUUsQ0FBQyxVQUFVLENBQUMsbUJBQW1CO29CQUNwQyxJQUFJLENBQUMsd0JBQXdCLENBQUMsSUFBOEIsQ0FBQyxDQUFDO29CQUM5RCxNQUFNO2dCQUNSLEtBQUssRUFBRSxDQUFDLFVBQVUsQ0FBQyxnQkFBZ0I7b0JBQ2pDLElBQUksQ0FBQyxxQkFBcUIsQ0FBQyxJQUEyQixDQUFDLENBQUM7b0JBQ3hELE1BQU07Z0JBQ1IsS0FBSyxFQUFFLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQztnQkFDL0IsS0FBSyxFQUFFLENBQUMsVUFBVSxDQUFDLFdBQVc7b0JBQzVCLElBQUksQ0FBQyx3QkFBd0IsQ0FBQyxJQUE4QixDQUFDLENBQUM7b0JBQzlELE1BQU07YUFDVDtZQUVELEVBQUUsQ0FBQyxZQUFZLENBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ2hELENBQUM7UUFFTyx3QkFBd0IsQ0FBQyxJQUE0QjtZQUMzRCxJQUFJLENBQUMsdUJBQXVCLENBQUMsSUFBSSxFQUFFLElBQUksRUFBRSxtQ0FBbUIsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztRQUMzRSxDQUFDO1FBRU8sd0JBQXdCLENBQUMsSUFBNEI7WUFDM0QsSUFBSSxDQUFDLHVCQUF1QixDQUFDLElBQUksRUFBRSxJQUFJLEVBQUUsbUNBQW1CLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7UUFDM0UsQ0FBQztRQUVPLHFCQUFxQixDQUFDLElBQXlCO1lBQ3JELElBQUksQ0FBQyx3QkFBd0IsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUNwQyxJQUFJLENBQUMsd0JBQXdCLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDdEMsQ0FBQztRQUVPLHVCQUF1QixDQUMzQixJQUFhLEVBQUUsUUFBcUMsRUFBRSxTQUFzQjtZQUM5RSxJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBSSxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsTUFBTSxFQUFFO2dCQUMvQyxPQUFPO2FBQ1I7WUFFRCxNQUFNLFlBQVksR0FBRyxvQ0FBb0IsQ0FBQyxJQUFJLENBQUMsV0FBVyxFQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQztZQUM3RSxNQUFNLGNBQWMsR0FDaEIsWUFBWSxDQUFDLElBQUksQ0FBQyxDQUFDLEVBQUMsSUFBSSxFQUFDLEVBQUUsRUFBRSxDQUFDLElBQUksS0FBSyxXQUFXLElBQUksSUFBSSxLQUFLLGNBQWMsQ0FBQyxDQUFDO1lBRW5GLG9FQUFvRTtZQUNwRSxJQUFJLENBQUMsY0FBYyxFQUFFO2dCQUNuQixPQUFPO2FBQ1I7WUFFRCxNQUFNLGNBQWMsR0FBRyw4Q0FBMEIsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUV4RCwwRUFBMEU7WUFDMUUsSUFBSSxDQUFDLGNBQWMsRUFBRTtnQkFDbkIsT0FBTzthQUNSO1lBRUQsTUFBTSxVQUFVLEdBQUcsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDO1lBQ3hDLE1BQU0sVUFBVSxHQUFHLElBQUksQ0FBQyxlQUFlLENBQUMsR0FBRyxDQUFDLFVBQVUsQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUU5RCxJQUFJLENBQUMsZUFBZSxDQUFDLEdBQUcsQ0FBQyxVQUFVLEVBQUUsVUFBVSxDQUFDLE1BQU0sQ0FBQztnQkFDckQsSUFBSSxFQUFFLFNBQVM7Z0JBQ2YsSUFBSSxFQUFFLGNBQWMsQ0FBQyxJQUFJLEtBQUssV0FBVyxDQUFDLENBQUMsQ0FBQyw0QkFBUyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsNEJBQVMsQ0FBQyxZQUFZO2dCQUN4RixJQUFJO2dCQUNKLFFBQVE7Z0JBQ1IsU0FBUyxFQUFFLGNBQWM7Z0JBQ3pCLFNBQVMsRUFBRSxjQUFjO2FBQzFCLENBQUMsQ0FBQyxDQUFDO1FBQ04sQ0FBQztRQUVPLHdCQUF3QixDQUFDLElBQXlCO1lBQ3hELE1BQU0sa0JBQWtCLEdBQUcsdUNBQW9CLENBQUMsSUFBSSxFQUFFLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQztZQUV4RSxJQUFJLGtCQUFrQixFQUFFO2dCQUN0QixNQUFNLGFBQWEsR0FBRyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxDQUFDLENBQUM7Z0JBRW5ELGFBQWEsQ0FBQyxZQUFZLEdBQUcsa0JBQWtCLENBQUM7Z0JBQ2hELElBQUksQ0FBQyxhQUFhLENBQUMsR0FBRyxDQUFDLElBQUksRUFBRSxhQUFhLENBQUMsQ0FBQzthQUM3QztRQUNILENBQUM7UUFFTyx3QkFBd0IsQ0FBQyxJQUF5QjtZQUN4RCxNQUFNLFNBQVMsR0FBRywwQ0FBc0IsQ0FBQyxJQUFJLENBQUMsQ0FBQztZQUUvQyxJQUFJLENBQUMsU0FBUyxJQUFJLFNBQVMsQ0FBQyxNQUFNLEtBQUssQ0FBQyxFQUFFO2dCQUN4QyxPQUFPO2FBQ1I7WUFFRCxNQUFNLFVBQVUsR0FBRyxTQUFTLENBQUMsQ0FBQyxDQUFDLENBQUM7WUFDaEMsTUFBTSxpQkFBaUIsR0FBRyxJQUFJLENBQUMsaUJBQWlCLENBQUMsSUFBSSxDQUFDLENBQUM7WUFFdkQsaUZBQWlGO1lBQ2pGLCtFQUErRTtZQUMvRSxvRUFBb0U7WUFDcEUsTUFBTSxNQUFNLEdBQUcsSUFBSSxDQUFDLFdBQVcsQ0FBQyxpQkFBaUIsQ0FBQyxVQUFVLENBQUMsQ0FBQyxTQUFTLEVBQUUsQ0FBQztZQUUxRSxJQUFJLE1BQU0sSUFBSSxNQUFNLENBQUMsZ0JBQWdCLElBQUksRUFBRSxDQUFDLGtCQUFrQixDQUFDLE1BQU0sQ0FBQyxnQkFBZ0IsQ0FBQyxFQUFFO2dCQUN2RixNQUFNLGFBQWEsR0FBRyxNQUFNLENBQUMsZ0JBQWdCLENBQUM7Z0JBQzlDLE1BQU0scUJBQXFCLEdBQUcsSUFBSSxDQUFDLGlCQUFpQixDQUFDLGFBQWEsQ0FBQyxDQUFDO2dCQUVwRSw2RUFBNkU7Z0JBQzdFLGlGQUFpRjtnQkFDakYscUJBQXFCLENBQUMsY0FBYyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztnQkFDaEQsSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsYUFBYSxFQUFFLHFCQUFxQixDQUFDLENBQUM7Z0JBRTdELCtDQUErQztnQkFDL0MsaUJBQWlCLENBQUMsVUFBVSxHQUFHLGFBQWEsQ0FBQztnQkFDN0MsSUFBSSxDQUFDLGFBQWEsQ0FBQyxHQUFHLENBQUMsSUFBSSxFQUFFLGlCQUFpQixDQUFDLENBQUM7YUFDakQ7UUFDSCxDQUFDO1FBRU8saUJBQWlCLENBQUMsSUFBeUI7WUFDakQsT0FBTyxJQUFJLENBQUMsYUFBYSxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsSUFBSSxFQUFDLGNBQWMsRUFBRSxFQUFFLEVBQUUsVUFBVSxFQUFFLElBQUksRUFBRSxZQUFZLEVBQUUsRUFBRSxFQUFDLENBQUM7UUFDbEcsQ0FBQztLQUNGO0lBdEhELHNEQXNIQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuaW1wb3J0IHtSZXNvbHZlZFRlbXBsYXRlfSBmcm9tICcuLi8uLi8uLi91dGlscy9uZ19jb21wb25lbnRfdGVtcGxhdGUnO1xuaW1wb3J0IHtnZXRBbmd1bGFyRGVjb3JhdG9yc30gZnJvbSAnLi4vLi4vLi4vdXRpbHMvbmdfZGVjb3JhdG9ycyc7XG5pbXBvcnQge2ZpbmRQYXJlbnRDbGFzc0RlY2xhcmF0aW9uLCBnZXRCYXNlVHlwZUlkZW50aWZpZXJzfSBmcm9tICcuLi8uLi8uLi91dGlscy90eXBlc2NyaXB0L2NsYXNzX2RlY2xhcmF0aW9uJztcbmltcG9ydCB7Z2V0UHJvcGVydHlOYW1lVGV4dH0gZnJvbSAnLi4vLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9wcm9wZXJ0eV9uYW1lJztcblxuaW1wb3J0IHtnZXRJbnB1dE5hbWVzT2ZDbGFzc30gZnJvbSAnLi9kaXJlY3RpdmVfaW5wdXRzJztcbmltcG9ydCB7TmdRdWVyeURlZmluaXRpb24sIFF1ZXJ5VHlwZX0gZnJvbSAnLi9xdWVyeS1kZWZpbml0aW9uJztcblxuXG4vKiogUmVzb2x2ZWQgbWV0YWRhdGEgb2YgYSBnaXZlbiBjbGFzcy4gKi9cbmV4cG9ydCBpbnRlcmZhY2UgQ2xhc3NNZXRhZGF0YSB7XG4gIC8qKiBMaXN0IG9mIGNsYXNzIGRlY2xhcmF0aW9ucyB0aGF0IGRlcml2ZSBmcm9tIHRoZSBnaXZlbiBjbGFzcy4gKi9cbiAgZGVyaXZlZENsYXNzZXM6IHRzLkNsYXNzRGVjbGFyYXRpb25bXTtcbiAgLyoqIFN1cGVyIGNsYXNzIG9mIHRoZSBnaXZlbiBjbGFzcy4gKi9cbiAgc3VwZXJDbGFzczogdHMuQ2xhc3NEZWNsYXJhdGlvbnxudWxsO1xuICAvKiogTGlzdCBvZiBwcm9wZXJ0eSBuYW1lcyB0aGF0IGRlY2xhcmUgYW4gQW5ndWxhciBpbnB1dCB3aXRoaW4gdGhlIGdpdmVuIGNsYXNzLiAqL1xuICBuZ0lucHV0TmFtZXM6IHN0cmluZ1tdO1xuICAvKiogQ29tcG9uZW50IHRlbXBsYXRlIHRoYXQgYmVsb25ncyB0byB0aGF0IGNsYXNzIGlmIHByZXNlbnQuICovXG4gIHRlbXBsYXRlPzogUmVzb2x2ZWRUZW1wbGF0ZTtcbn1cblxuLyoqIFR5cGUgdGhhdCBkZXNjcmliZXMgYSBtYXAgd2hpY2ggY2FuIGJlIHVzZWQgdG8gZ2V0IGEgY2xhc3MgZGVjbGFyYXRpb24ncyBtZXRhZGF0YS4gKi9cbmV4cG9ydCB0eXBlIENsYXNzTWV0YWRhdGFNYXAgPSBNYXA8dHMuQ2xhc3NEZWNsYXJhdGlvbiwgQ2xhc3NNZXRhZGF0YT47XG5cbi8qKlxuICogVmlzaXRvciB0aGF0IGNhbiBiZSB1c2VkIHRvIGRldGVybWluZSBBbmd1bGFyIHF1ZXJpZXMgd2l0aGluIGdpdmVuIFR5cGVTY3JpcHQgbm9kZXMuXG4gKiBCZXNpZGVzIHJlc29sdmluZyBxdWVyaWVzLCB0aGUgdmlzaXRvciBhbHNvIHJlY29yZHMgY2xhc3MgcmVsYXRpb25zIGFuZCBzZWFyY2hlcyBmb3JcbiAqIEFuZ3VsYXIgaW5wdXQgc2V0dGVycyB3aGljaCBjYW4gYmUgdXNlZCB0byBhbmFseXplIHRoZSB0aW1pbmcgdXNhZ2Ugb2YgYSBnaXZlbiBxdWVyeS5cbiAqL1xuZXhwb3J0IGNsYXNzIE5nUXVlcnlSZXNvbHZlVmlzaXRvciB7XG4gIC8qKiBSZXNvbHZlZCBBbmd1bGFyIHF1ZXJ5IGRlZmluaXRpb25zLiAqL1xuICByZXNvbHZlZFF1ZXJpZXMgPSBuZXcgTWFwPHRzLlNvdXJjZUZpbGUsIE5nUXVlcnlEZWZpbml0aW9uW10+KCk7XG5cbiAgLyoqIE1hcHMgYSBjbGFzcyBkZWNsYXJhdGlvbiB0byBpdHMgY2xhc3MgbWV0YWRhdGEuICovXG4gIGNsYXNzTWV0YWRhdGE6IENsYXNzTWV0YWRhdGFNYXAgPSBuZXcgTWFwKCk7XG5cbiAgY29uc3RydWN0b3IocHVibGljIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlcikge31cblxuICB2aXNpdE5vZGUobm9kZTogdHMuTm9kZSkge1xuICAgIHN3aXRjaCAobm9kZS5raW5kKSB7XG4gICAgICBjYXNlIHRzLlN5bnRheEtpbmQuUHJvcGVydHlEZWNsYXJhdGlvbjpcbiAgICAgICAgdGhpcy52aXNpdFByb3BlcnR5RGVjbGFyYXRpb24obm9kZSBhcyB0cy5Qcm9wZXJ0eURlY2xhcmF0aW9uKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIHRzLlN5bnRheEtpbmQuQ2xhc3NEZWNsYXJhdGlvbjpcbiAgICAgICAgdGhpcy52aXNpdENsYXNzRGVjbGFyYXRpb24obm9kZSBhcyB0cy5DbGFzc0RlY2xhcmF0aW9uKTtcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIHRzLlN5bnRheEtpbmQuR2V0QWNjZXNzb3I6XG4gICAgICBjYXNlIHRzLlN5bnRheEtpbmQuU2V0QWNjZXNzb3I6XG4gICAgICAgIHRoaXMudmlzaXRBY2Nlc3NvckRlY2xhcmF0aW9uKG5vZGUgYXMgdHMuQWNjZXNzb3JEZWNsYXJhdGlvbik7XG4gICAgICAgIGJyZWFrO1xuICAgIH1cblxuICAgIHRzLmZvckVhY2hDaGlsZChub2RlLCBuID0+IHRoaXMudmlzaXROb2RlKG4pKTtcbiAgfVxuXG4gIHByaXZhdGUgdmlzaXRQcm9wZXJ0eURlY2xhcmF0aW9uKG5vZGU6IHRzLlByb3BlcnR5RGVjbGFyYXRpb24pIHtcbiAgICB0aGlzLl9yZWNvcmRRdWVyeURlY2xhcmF0aW9uKG5vZGUsIG5vZGUsIGdldFByb3BlcnR5TmFtZVRleHQobm9kZS5uYW1lKSk7XG4gIH1cblxuICBwcml2YXRlIHZpc2l0QWNjZXNzb3JEZWNsYXJhdGlvbihub2RlOiB0cy5BY2Nlc3NvckRlY2xhcmF0aW9uKSB7XG4gICAgdGhpcy5fcmVjb3JkUXVlcnlEZWNsYXJhdGlvbihub2RlLCBudWxsLCBnZXRQcm9wZXJ0eU5hbWVUZXh0KG5vZGUubmFtZSkpO1xuICB9XG5cbiAgcHJpdmF0ZSB2aXNpdENsYXNzRGVjbGFyYXRpb24obm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbikge1xuICAgIHRoaXMuX3JlY29yZENsYXNzSW5wdXRTZXR0ZXJzKG5vZGUpO1xuICAgIHRoaXMuX3JlY29yZENsYXNzSW5oZXJpdGFuY2VzKG5vZGUpO1xuICB9XG5cbiAgcHJpdmF0ZSBfcmVjb3JkUXVlcnlEZWNsYXJhdGlvbihcbiAgICAgIG5vZGU6IHRzLk5vZGUsIHByb3BlcnR5OiB0cy5Qcm9wZXJ0eURlY2xhcmF0aW9ufG51bGwsIHF1ZXJ5TmFtZTogc3RyaW5nfG51bGwpIHtcbiAgICBpZiAoIW5vZGUuZGVjb3JhdG9ycyB8fCAhbm9kZS5kZWNvcmF0b3JzLmxlbmd0aCkge1xuICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIGNvbnN0IG5nRGVjb3JhdG9ycyA9IGdldEFuZ3VsYXJEZWNvcmF0b3JzKHRoaXMudHlwZUNoZWNrZXIsIG5vZGUuZGVjb3JhdG9ycyk7XG4gICAgY29uc3QgcXVlcnlEZWNvcmF0b3IgPVxuICAgICAgICBuZ0RlY29yYXRvcnMuZmluZCgoe25hbWV9KSA9PiBuYW1lID09PSAnVmlld0NoaWxkJyB8fCBuYW1lID09PSAnQ29udGVudENoaWxkJyk7XG5cbiAgICAvLyBFbnN1cmUgdGhhdCB0aGUgY3VycmVudCBwcm9wZXJ0eSBkZWNsYXJhdGlvbiBpcyBkZWZpbmluZyBhIHF1ZXJ5LlxuICAgIGlmICghcXVlcnlEZWNvcmF0b3IpIHtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBxdWVyeUNvbnRhaW5lciA9IGZpbmRQYXJlbnRDbGFzc0RlY2xhcmF0aW9uKG5vZGUpO1xuXG4gICAgLy8gSWYgdGhlIHF1ZXJ5IGlzIG5vdCBsb2NhdGVkIHdpdGhpbiBhIGNsYXNzIGRlY2xhcmF0aW9uLCBza2lwIHRoaXMgbm9kZS5cbiAgICBpZiAoIXF1ZXJ5Q29udGFpbmVyKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3Qgc291cmNlRmlsZSA9IG5vZGUuZ2V0U291cmNlRmlsZSgpO1xuICAgIGNvbnN0IG5ld1F1ZXJpZXMgPSB0aGlzLnJlc29sdmVkUXVlcmllcy5nZXQoc291cmNlRmlsZSkgfHwgW107XG5cbiAgICB0aGlzLnJlc29sdmVkUXVlcmllcy5zZXQoc291cmNlRmlsZSwgbmV3UXVlcmllcy5jb25jYXQoe1xuICAgICAgbmFtZTogcXVlcnlOYW1lLFxuICAgICAgdHlwZTogcXVlcnlEZWNvcmF0b3IubmFtZSA9PT0gJ1ZpZXdDaGlsZCcgPyBRdWVyeVR5cGUuVmlld0NoaWxkIDogUXVlcnlUeXBlLkNvbnRlbnRDaGlsZCxcbiAgICAgIG5vZGUsXG4gICAgICBwcm9wZXJ0eSxcbiAgICAgIGRlY29yYXRvcjogcXVlcnlEZWNvcmF0b3IsXG4gICAgICBjb250YWluZXI6IHF1ZXJ5Q29udGFpbmVyLFxuICAgIH0pKTtcbiAgfVxuXG4gIHByaXZhdGUgX3JlY29yZENsYXNzSW5wdXRTZXR0ZXJzKG5vZGU6IHRzLkNsYXNzRGVjbGFyYXRpb24pIHtcbiAgICBjb25zdCByZXNvbHZlZElucHV0TmFtZXMgPSBnZXRJbnB1dE5hbWVzT2ZDbGFzcyhub2RlLCB0aGlzLnR5cGVDaGVja2VyKTtcblxuICAgIGlmIChyZXNvbHZlZElucHV0TmFtZXMpIHtcbiAgICAgIGNvbnN0IGNsYXNzTWV0YWRhdGEgPSB0aGlzLl9nZXRDbGFzc01ldGFkYXRhKG5vZGUpO1xuXG4gICAgICBjbGFzc01ldGFkYXRhLm5nSW5wdXROYW1lcyA9IHJlc29sdmVkSW5wdXROYW1lcztcbiAgICAgIHRoaXMuY2xhc3NNZXRhZGF0YS5zZXQobm9kZSwgY2xhc3NNZXRhZGF0YSk7XG4gICAgfVxuICB9XG5cbiAgcHJpdmF0ZSBfcmVjb3JkQ2xhc3NJbmhlcml0YW5jZXMobm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbikge1xuICAgIGNvbnN0IGJhc2VUeXBlcyA9IGdldEJhc2VUeXBlSWRlbnRpZmllcnMobm9kZSk7XG5cbiAgICBpZiAoIWJhc2VUeXBlcyB8fCBiYXNlVHlwZXMubGVuZ3RoICE9PSAxKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3Qgc3VwZXJDbGFzcyA9IGJhc2VUeXBlc1swXTtcbiAgICBjb25zdCBiYXNlQ2xhc3NNZXRhZGF0YSA9IHRoaXMuX2dldENsYXNzTWV0YWRhdGEobm9kZSk7XG5cbiAgICAvLyBXZSBuZWVkIHRvIHJlc29sdmUgdGhlIHZhbHVlIGRlY2xhcmF0aW9uIHRocm91Z2ggdGhlIHJlc29sdmVkIHR5cGUgYXMgdGhlIGJhc2VcbiAgICAvLyBjbGFzcyBjb3VsZCBiZSBkZWNsYXJlZCBpbiBkaWZmZXJlbnQgc291cmNlIGZpbGVzIGFuZCB0aGUgbG9jYWwgc3ltYm9sIHdvbid0XG4gICAgLy8gY29udGFpbiBhIHZhbHVlIGRlY2xhcmF0aW9uIGFzIHRoZSB2YWx1ZSBpcyBub3QgZGVjbGFyZWQgbG9jYWxseS5cbiAgICBjb25zdCBzeW1ib2wgPSB0aGlzLnR5cGVDaGVja2VyLmdldFR5cGVBdExvY2F0aW9uKHN1cGVyQ2xhc3MpLmdldFN5bWJvbCgpO1xuXG4gICAgaWYgKHN5bWJvbCAmJiBzeW1ib2wudmFsdWVEZWNsYXJhdGlvbiAmJiB0cy5pc0NsYXNzRGVjbGFyYXRpb24oc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pKSB7XG4gICAgICBjb25zdCBleHRlbmRlZENsYXNzID0gc3ltYm9sLnZhbHVlRGVjbGFyYXRpb247XG4gICAgICBjb25zdCBjbGFzc01ldGFkYXRhRXh0ZW5kZWQgPSB0aGlzLl9nZXRDbGFzc01ldGFkYXRhKGV4dGVuZGVkQ2xhc3MpO1xuXG4gICAgICAvLyBSZWNvcmQgYWxsIGNsYXNzZXMgdGhhdCBkZXJpdmUgZnJvbSB0aGUgZ2l2ZW4gY2xhc3MuIFRoaXMgbWFrZXMgaXQgZWFzeSB0b1xuICAgICAgLy8gZGV0ZXJtaW5lIGFsbCBjbGFzc2VzIHRoYXQgY291bGQgcG90ZW50aWFsbHkgdXNlIGluaGVyaXRlZCBxdWVyaWVzIHN0YXRpY2FsbHkuXG4gICAgICBjbGFzc01ldGFkYXRhRXh0ZW5kZWQuZGVyaXZlZENsYXNzZXMucHVzaChub2RlKTtcbiAgICAgIHRoaXMuY2xhc3NNZXRhZGF0YS5zZXQoZXh0ZW5kZWRDbGFzcywgY2xhc3NNZXRhZGF0YUV4dGVuZGVkKTtcblxuICAgICAgLy8gUmVjb3JkIHRoZSBzdXBlciBjbGFzcyBvZiB0aGUgY3VycmVudCBjbGFzcy5cbiAgICAgIGJhc2VDbGFzc01ldGFkYXRhLnN1cGVyQ2xhc3MgPSBleHRlbmRlZENsYXNzO1xuICAgICAgdGhpcy5jbGFzc01ldGFkYXRhLnNldChub2RlLCBiYXNlQ2xhc3NNZXRhZGF0YSk7XG4gICAgfVxuICB9XG5cbiAgcHJpdmF0ZSBfZ2V0Q2xhc3NNZXRhZGF0YShub2RlOiB0cy5DbGFzc0RlY2xhcmF0aW9uKTogQ2xhc3NNZXRhZGF0YSB7XG4gICAgcmV0dXJuIHRoaXMuY2xhc3NNZXRhZGF0YS5nZXQobm9kZSkgfHwge2Rlcml2ZWRDbGFzc2VzOiBbXSwgc3VwZXJDbGFzczogbnVsbCwgbmdJbnB1dE5hbWVzOiBbXX07XG4gIH1cbn1cbiJdfQ==