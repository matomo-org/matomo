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
        define("@angular/core/schematics/migrations/undecorated-classes-with-di/ng_declaration_collector", ["require", "exports", "@angular/compiler-cli/src/ngtsc/imports", "typescript", "@angular/core/schematics/utils/ng_decorators", "@angular/core/schematics/utils/typescript/property_name"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getNgClassDecorators = exports.hasNgDeclarationDecorator = exports.hasInjectableDecorator = exports.hasDirectiveDecorator = exports.NgDeclarationCollector = void 0;
    const imports_1 = require("@angular/compiler-cli/src/ngtsc/imports");
    const ts = require("typescript");
    const ng_decorators_1 = require("@angular/core/schematics/utils/ng_decorators");
    const property_name_1 = require("@angular/core/schematics/utils/typescript/property_name");
    /**
     * Visitor that walks through specified TypeScript nodes and collects all defined
     * directives and provider classes. Directives are separated by decorated and
     * undecorated directives.
     */
    class NgDeclarationCollector {
        constructor(typeChecker, evaluator) {
            this.typeChecker = typeChecker;
            this.evaluator = evaluator;
            /** List of resolved directives which are decorated. */
            this.decoratedDirectives = [];
            /** List of resolved providers which are decorated. */
            this.decoratedProviders = [];
            /** Set of resolved Angular declarations which are not decorated. */
            this.undecoratedDeclarations = new Set();
        }
        visitNode(node) {
            if (ts.isClassDeclaration(node)) {
                this._visitClassDeclaration(node);
            }
            ts.forEachChild(node, n => this.visitNode(n));
        }
        _visitClassDeclaration(node) {
            if (!node.decorators || !node.decorators.length) {
                return;
            }
            const ngDecorators = ng_decorators_1.getAngularDecorators(this.typeChecker, node.decorators);
            const ngModuleDecorator = ngDecorators.find(({ name }) => name === 'NgModule');
            if (hasDirectiveDecorator(node, this.typeChecker, ngDecorators)) {
                this.decoratedDirectives.push(node);
            }
            else if (hasInjectableDecorator(node, this.typeChecker, ngDecorators)) {
                this.decoratedProviders.push(node);
            }
            else if (ngModuleDecorator) {
                this._visitNgModuleDecorator(ngModuleDecorator);
            }
        }
        _visitNgModuleDecorator(decorator) {
            const decoratorCall = decorator.node.expression;
            const metadata = decoratorCall.arguments[0];
            if (!metadata || !ts.isObjectLiteralExpression(metadata)) {
                return;
            }
            let entryComponentsNode = null;
            let declarationsNode = null;
            metadata.properties.forEach(p => {
                if (!ts.isPropertyAssignment(p)) {
                    return;
                }
                const name = property_name_1.getPropertyNameText(p.name);
                if (name === 'entryComponents') {
                    entryComponentsNode = p.initializer;
                }
                else if (name === 'declarations') {
                    declarationsNode = p.initializer;
                }
            });
            // In case the module specifies the "entryComponents" field, walk through all
            // resolved entry components and collect the referenced directives.
            if (entryComponentsNode) {
                flattenTypeList(this.evaluator.evaluate(entryComponentsNode)).forEach(ref => {
                    if (ts.isClassDeclaration(ref.node) &&
                        !hasNgDeclarationDecorator(ref.node, this.typeChecker)) {
                        this.undecoratedDeclarations.add(ref.node);
                    }
                });
            }
            // In case the module specifies the "declarations" field, walk through all
            // resolved declarations and collect the referenced directives.
            if (declarationsNode) {
                flattenTypeList(this.evaluator.evaluate(declarationsNode)).forEach(ref => {
                    if (ts.isClassDeclaration(ref.node) &&
                        !hasNgDeclarationDecorator(ref.node, this.typeChecker)) {
                        this.undecoratedDeclarations.add(ref.node);
                    }
                });
            }
        }
    }
    exports.NgDeclarationCollector = NgDeclarationCollector;
    /** Flattens a list of type references. */
    function flattenTypeList(value) {
        if (Array.isArray(value)) {
            return value.reduce((res, v) => res.concat(flattenTypeList(v)), []);
        }
        else if (value instanceof imports_1.Reference) {
            return [value];
        }
        return [];
    }
    /** Checks whether the given node has the "@Directive" or "@Component" decorator set. */
    function hasDirectiveDecorator(node, typeChecker, ngDecorators) {
        return (ngDecorators || getNgClassDecorators(node, typeChecker))
            .some(({ name }) => name === 'Directive' || name === 'Component');
    }
    exports.hasDirectiveDecorator = hasDirectiveDecorator;
    /** Checks whether the given node has the "@Injectable" decorator set. */
    function hasInjectableDecorator(node, typeChecker, ngDecorators) {
        return (ngDecorators || getNgClassDecorators(node, typeChecker))
            .some(({ name }) => name === 'Injectable');
    }
    exports.hasInjectableDecorator = hasInjectableDecorator;
    /** Whether the given node has an explicit decorator that describes an Angular declaration. */
    function hasNgDeclarationDecorator(node, typeChecker) {
        return getNgClassDecorators(node, typeChecker)
            .some(({ name }) => name === 'Component' || name === 'Directive' || name === 'Pipe');
    }
    exports.hasNgDeclarationDecorator = hasNgDeclarationDecorator;
    /** Gets all Angular decorators of a given class declaration. */
    function getNgClassDecorators(node, typeChecker) {
        if (!node.decorators) {
            return [];
        }
        return ng_decorators_1.getAngularDecorators(typeChecker, node.decorators);
    }
    exports.getNgClassDecorators = getNgClassDecorators;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibmdfZGVjbGFyYXRpb25fY29sbGVjdG9yLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvdW5kZWNvcmF0ZWQtY2xhc3Nlcy13aXRoLWRpL25nX2RlY2xhcmF0aW9uX2NvbGxlY3Rvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxxRUFBa0U7SUFFbEUsaUNBQWlDO0lBRWpDLGdGQUE0RTtJQUM1RSwyRkFBeUU7SUFHekU7Ozs7T0FJRztJQUNILE1BQWEsc0JBQXNCO1FBVWpDLFlBQW1CLFdBQTJCLEVBQVUsU0FBMkI7WUFBaEUsZ0JBQVcsR0FBWCxXQUFXLENBQWdCO1lBQVUsY0FBUyxHQUFULFNBQVMsQ0FBa0I7WUFUbkYsdURBQXVEO1lBQ3ZELHdCQUFtQixHQUEwQixFQUFFLENBQUM7WUFFaEQsc0RBQXNEO1lBQ3RELHVCQUFrQixHQUEwQixFQUFFLENBQUM7WUFFL0Msb0VBQW9FO1lBQ3BFLDRCQUF1QixHQUFHLElBQUksR0FBRyxFQUF1QixDQUFDO1FBRTZCLENBQUM7UUFFdkYsU0FBUyxDQUFDLElBQWE7WUFDckIsSUFBSSxFQUFFLENBQUMsa0JBQWtCLENBQUMsSUFBSSxDQUFDLEVBQUU7Z0JBQy9CLElBQUksQ0FBQyxzQkFBc0IsQ0FBQyxJQUFJLENBQUMsQ0FBQzthQUNuQztZQUVELEVBQUUsQ0FBQyxZQUFZLENBQUMsSUFBSSxFQUFFLENBQUMsQ0FBQyxFQUFFLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDO1FBQ2hELENBQUM7UUFFTyxzQkFBc0IsQ0FBQyxJQUF5QjtZQUN0RCxJQUFJLENBQUMsSUFBSSxDQUFDLFVBQVUsSUFBSSxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsTUFBTSxFQUFFO2dCQUMvQyxPQUFPO2FBQ1I7WUFFRCxN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SUM7SUFDRCw4RkFBOEY7SUFDOUYsU0FBZ0IseUJBQXlCLENBQUMsSUFBeUIsRUFBRSxXQUEyQjtRQUM5RixPQUFPLG9CQUFvQixDQUFDLElBQUksRUFBRSxXQUFXLENBQUM7YUFDekMsSUFBSSxDQUFDLENBQUMsRUFBQyxJQUFJLEVBQUMsRUFBRSxFQUFFLENBQUMsSUFBSSxLQUFLLFdBQVcsSUFBSSxJQUFJLEtBQUssV0FBVyxJQUFJLElBQUksS0FBSyxNQUFNLENBQUMsQ0FBQztJQUN6RixDQUFDO0lBSEQsOERBR0M7SUFFRCxnRUFBZ0U7SUFDaEUsU0FBZ0Isb0JBQW9CLENBQ2hDLElBQXlCLEVBQUUsV0FBMkI7UUFDeEQsSUFBSSxDQUFDLElBQUksQ0FBQyxVQUFVLEVBQUU7WUFDcEIsT0FBTyxFQUFFLENBQUM7U0FDWDtRQUNELE9BQU8sb0NBQW9CLENBQUMsV0FBVyxFQUFFLElBQUksQ0FBQyxVQUFVLENBQUMsQ0FBQztJQUM1RCxDQUFDO0lBTkQsb0RBTUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtSZWZlcmVuY2V9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvaW1wb3J0cyc7XG5pbXBvcnQge1BhcnRpYWxFdmFsdWF0b3IsIFJlc29sdmVkVmFsdWV9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvcGFydGlhbF9ldmFsdWF0b3InO1xuaW1wb3J0ICogYXMgdHMgZnJvbSAndHlwZXNjcmlwdCc7XG5cbmltcG9ydCB7Z2V0QW5ndWxhckRlY29yYXRvcnMsIE5nRGVjb3JhdG9yfSBmcm9tICcuLi8uLi91dGlscy9uZ19kZWNvcmF0b3JzJztcbmltcG9ydCB7Z2V0UHJvcGVydHlOYW1lVGV4dH0gZnJvbSAnLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9wcm9wZXJ0eV9uYW1lJztcblxuXG4vKipcbiAqIFZpc2l0b3IgdGhhdCB3YWxrcyB0aHJvdWdoIHNwZWNpZmllZCBUeXBlU2NyaXB0IG5vZGVzIGFuZCBjb2xsZWN0cyBhbGwgZGVmaW5lZFxuICogZGlyZWN0aXZlcyBhbmQgcHJvdmlkZXIgY2xhc3Nlcy4gRGlyZWN0aXZlcyBhcmUgc2VwYXJhdGVkIGJ5IGRlY29yYXRlZCBhbmRcbiAqIHVuZGVjb3JhdGVkIGRpcmVjdGl2ZXMuXG4gKi9cbmV4cG9ydCBjbGFzcyBOZ0RlY2xhcmF0aW9uQ29sbGVjdG9yIHtcbiAgLyoqIExpc3Qgb2YgcmVzb2x2ZWQgZGlyZWN0aXZlcyB3aGljaCBhcmUgZGVjb3JhdGVkLiAqL1xuICBkZWNvcmF0ZWREaXJlY3RpdmVzOiB0cy5DbGFzc0RlY2xhcmF0aW9uW10gPSBbXTtcblxuICAvKiogTGlzdCBvZiByZXNvbHZlZCBwcm92aWRlcnMgd2hpY2ggYXJlIGRlY29yYXRlZC4gKi9cbiAgZGVjb3JhdGVkUHJvdmlkZXJzOiB0cy5DbGFzc0RlY2xhcmF0aW9uW10gPSBbXTtcblxuICAvKiogU2V0IG9mIHJlc29sdmVkIEFuZ3VsYXIgZGVjbGFyYXRpb25zIHdoaWNoIGFyZSBub3QgZGVjb3JhdGVkLiAqL1xuICB1bmRlY29yYXRlZERlY2xhcmF0aW9ucyA9IG5ldyBTZXQ8dHMuQ2xhc3NEZWNsYXJhdGlvbj4oKTtcblxuICBjb25zdHJ1Y3RvcihwdWJsaWMgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBwcml2YXRlIGV2YWx1YXRvcjogUGFydGlhbEV2YWx1YXRvcikge31cblxuICB2aXNpdE5vZGUobm9kZTogdHMuTm9kZSkge1xuICAgIGlmICh0cy5pc0NsYXNzRGVjbGFyYXRpb24obm9kZSkpIHtcbiAgICAgIHRoaXMuX3Zpc2l0Q2xhc3NEZWNsYXJhdGlvbihub2RlKTtcbiAgICB9XG5cbiAgICB0cy5mb3JFYWNoQ2hpbGQobm9kZSwgbiA9PiB0aGlzLnZpc2l0Tm9kZShuKSk7XG4gIH1cblxuICBwcml2YXRlIF92aXNpdENsYXNzRGVjbGFyYXRpb24obm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbikge1xuICAgIGlmICghbm9kZS5kZWNvcmF0b3JzIHx8ICFub2RlLmRlY29yYXRvcnMubGVuZ3RoKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgbmdEZWNvcmF0b3JzID0gZ2V0QW5ndWxhckRlY29yYXRvcnModGhpcy50eXBlQ2hlY2tlciwgbm9kZS5kZWNvcmF0b3JzKTtcbiAgICBjb25zdCBuZ01vZHVsZURlY29yYXRvciA9IG5nRGVjb3JhdG9ycy5maW5kKCh7bmFtZX0pID0+IG5hbWUgPT09ICdOZ01vZHVsZScpO1xuXG4gICAgaWYgKGhhc0RpcmVjdGl2ZURlY29yYXRvcihub2RlLCB0aGlzLnR5cGVDaGVja2VyLCBuZ0RlY29yYXRvcnMpKSB7XG4gICAgICB0aGlzLmRlY29yYXRlZERpcmVjdGl2ZXMucHVzaChub2RlKTtcbiAgICB9IGVsc2UgaWYgKGhhc0luamVjdGFibGVEZWNvcmF0b3Iobm9kZSwgdGhpcy50eXBlQ2hlY2tlciwgbmdEZWNvcmF0b3JzKSkge1xuICAgICAgdGhpcy5kZWNvcmF0ZWRQcm92aWRlcnMucHVzaChub2RlKTtcbiAgICB9IGVsc2UgaWYgKG5nTW9kdWxlRGVjb3JhdG9yKSB7XG4gICAgICB0aGlzLl92aXNpdE5nTW9kdWxlRGVjb3JhdG9yKG5nTW9kdWxlRGVjb3JhdG9yKTtcbiAgICB9XG4gIH1cblxuICBwcml2YXRlIF92aXNpdE5nTW9kdWxlRGVjb3JhdG9yKGRlY29yYXRvcjogTmdEZWNvcmF0b3IpIHtcbiAgICBjb25zdCBkZWNvcmF0b3JDYWxsID0gZGVjb3JhdG9yLm5vZGUuZXhwcmVzc2lvbjtcbiAgICBjb25zdCBtZXRhZGF0YSA9IGRlY29yYXRvckNhbGwuYXJndW1lbnRzWzBdO1xuXG4gICAgaWYgKCFtZXRhZGF0YSB8fCAhdHMuaXNPYmplY3RMaXRlcmFsRXhwcmVzc2lvbihtZXRhZGF0YSkpIHtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBsZXQgZW50cnlDb21wb25lbnRzTm9kZTogdHMuRXhwcmVzc2lvbnxudWxsID0gbnVsbDtcbiAgICBsZXQgZGVjbGFyYXRpb25zTm9kZTogdHMuRXhwcmVzc2lvbnxudWxsID0gbnVsbDtcblxuICAgIG1ldGFkYXRhLnByb3BlcnRpZXMuZm9yRWFjaChwID0+IHtcbiAgICAgIGlmICghdHMuaXNQcm9wZXJ0eUFzc2lnbm1lbnQocCkpIHtcbiAgICAgICAgcmV0dXJuO1xuICAgICAgfVxuXG4gICAgICBjb25zdCBuYW1lID0gZ2V0UHJvcGVydHlOYW1lVGV4dChwLm5hbWUpO1xuXG4gICAgICBpZiAobmFtZSA9PT0gJ2VudHJ5Q29tcG9uZW50cycpIHtcbiAgICAgICAgZW50cnlDb21wb25lbnRzTm9kZSA9IHAuaW5pdGlhbGl6ZXI7XG4gICAgICB9IGVsc2UgaWYgKG5hbWUgPT09ICdkZWNsYXJhdGlvbnMnKSB7XG4gICAgICAgIGRlY2xhcmF0aW9uc05vZGUgPSBwLmluaXRpYWxpemVyO1xuICAgICAgfVxuICAgIH0pO1xuXG4gICAgLy8gSW4gY2FzZSB0aGUgbW9kdWxlIHNwZWNpZmllcyB0aGUgXCJlbnRyeUNvbXBvbmVudHNcIiBmaWVsZCwgd2FsayB0aHJvdWdoIGFsbFxuICAgIC8vIHJlc29sdmVkIGVudHJ5IGNvbXBvbmVudHMgYW5kIGNvbGxlY3QgdGhlIHJlZmVyZW5jZWQgZGlyZWN0aXZlcy5cbiAgICBpZiAoZW50cnlDb21wb25lbnRzTm9kZSkge1xuICAgICAgZmxhdHRlblR5cGVMaXN0KHRoaXMuZXZhbHVhdG9yLmV2YWx1YXRlKGVudHJ5Q29tcG9uZW50c05vZGUpKS5mb3JFYWNoKHJlZiA9PiB7XG4gICAgICAgIGlmICh0cy5pc0NsYXNzRGVjbGFyYXRpb24ocmVmLm5vZGUpICYmXG4gICAgICAgICAgICAhaGFzTmdEZWNsYXJhdGlvbkRlY29yYXRvcihyZWYubm9kZSwgdGhpcy50eXBlQ2hlY2tlcikpIHtcbiAgICAgICAgICB0aGlzLnVuZGVjb3JhdGVkRGVjbGFyYXRpb25zLmFkZChyZWYubm9kZSk7XG4gICAgICAgIH1cbiAgICAgIH0pO1xuICAgIH1cblxuICAgIC8vIEluIGNhc2UgdGhlIG1vZHVsZSBzcGVjaWZpZXMgdGhlIFwiZGVjbGFyYXRpb25zXCIgZmllbGQsIHdhbGsgdGhyb3VnaCBhbGxcbiAgICAvLyByZXNvbHZlZCBkZWNsYXJhdGlvbnMgYW5kIGNvbGxlY3QgdGhlIHJlZmVyZW5jZWQgZGlyZWN0aXZlcy5cbiAgICBpZiAoZGVjbGFyYXRpb25zTm9kZSkge1xuICAgICAgZmxhdHRlblR5cGVMaXN0KHRoaXMuZXZhbHVhdG9yLmV2YWx1YXRlKGRlY2xhcmF0aW9uc05vZGUpKS5mb3JFYWNoKHJlZiA9PiB7XG4gICAgICAgIGlmICh0cy5pc0NsYXNzRGVjbGFyYXRpb24ocmVmLm5vZGUpICYmXG4gICAgICAgICAgICAhaGFzTmdEZWNsYXJhdGlvbkRlY29yYXRvcihyZWYubm9kZSwgdGhpcy50eXBlQ2hlY2tlcikpIHtcbiAgICAgICAgICB0aGlzLnVuZGVjb3JhdGVkRGVjbGFyYXRpb25zLmFkZChyZWYubm9kZSk7XG4gICAgICAgIH1cbiAgICAgIH0pO1xuICAgIH1cbiAgfVxufVxuXG4vKiogRmxhdHRlbnMgYSBsaXN0IG9mIHR5cGUgcmVmZXJlbmNlcy4gKi9cbmZ1bmN0aW9uIGZsYXR0ZW5UeXBlTGlzdCh2YWx1ZTogUmVzb2x2ZWRWYWx1ZSk6IFJlZmVyZW5jZVtdIHtcbiAgaWYgKEFycmF5LmlzQXJyYXkodmFsdWUpKSB7XG4gICAgcmV0dXJuIDxSZWZlcmVuY2VbXT52YWx1ZS5yZWR1Y2UoXG4gICAgICAgIChyZXM6IFJlZmVyZW5jZVtdLCB2OiBSZXNvbHZlZFZhbHVlKSA9PiByZXMuY29uY2F0KGZsYXR0ZW5UeXBlTGlzdCh2KSksIFtdKTtcbiAgfSBlbHNlIGlmICh2YWx1ZSBpbnN0YW5jZW9mIFJlZmVyZW5jZSkge1xuICAgIHJldHVybiBbdmFsdWVdO1xuICB9XG4gIHJldHVybiBbXTtcbn1cblxuLyoqIENoZWNrcyB3aGV0aGVyIHRoZSBnaXZlbiBub2RlIGhhcyB0aGUgXCJARGlyZWN0aXZlXCIgb3IgXCJAQ29tcG9uZW50XCIgZGVjb3JhdG9yIHNldC4gKi9cbmV4cG9ydCBmdW5jdGlvbiBoYXNEaXJlY3RpdmVEZWNvcmF0b3IoXG4gICAgbm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbiwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBuZ0RlY29yYXRvcnM/OiBOZ0RlY29yYXRvcltdKTogYm9vbGVhbiB7XG4gIHJldHVybiAobmdEZWNvcmF0b3JzIHx8IGdldE5nQ2xhc3NEZWNvcmF0b3JzKG5vZGUsIHR5cGVDaGVja2VyKSlcbiAgICAgIC5zb21lKCh7bmFtZX0pID0+IG5hbWUgPT09ICdEaXJlY3RpdmUnIHx8IG5hbWUgPT09ICdDb21wb25lbnQnKTtcbn1cblxuXG5cbi8qKiBDaGVja3Mgd2hldGhlciB0aGUgZ2l2ZW4gbm9kZSBoYXMgdGhlIFwiQEluamVjdGFibGVcIiBkZWNvcmF0b3Igc2V0LiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGhhc0luamVjdGFibGVEZWNvcmF0b3IoXG4gICAgbm9kZTogdHMuQ2xhc3NEZWNsYXJhdGlvbiwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyLCBuZ0RlY29yYXRvcnM/OiBOZ0RlY29yYXRvcltdKTogYm9vbGVhbiB7XG4gIHJldHVybiAobmdEZWNvcmF0b3JzIHx8IGdldE5nQ2xhc3NEZWNvcmF0b3JzKG5vZGUsIHR5cGVDaGVja2VyKSlcbiAgICAgIC5zb21lKCh7bmFtZX0pID0+IG5hbWUgPT09ICdJbmplY3RhYmxlJyk7XG59XG4vKiogV2hldGhlciB0aGUgZ2l2ZW4gbm9kZSBoYXMgYW4gZXhwbGljaXQgZGVjb3JhdG9yIHRoYXQgZGVzY3JpYmVzIGFuIEFuZ3VsYXIgZGVjbGFyYXRpb24uICovXG5leHBvcnQgZnVuY3Rpb24gaGFzTmdEZWNsYXJhdGlvbkRlY29yYXRvcihub2RlOiB0cy5DbGFzc0RlY2xhcmF0aW9uLCB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIpIHtcbiAgcmV0dXJuIGdldE5nQ2xhc3NEZWNvcmF0b3JzKG5vZGUsIHR5cGVDaGVja2VyKVxuICAgICAgLnNvbWUoKHtuYW1lfSkgPT4gbmFtZSA9PT0gJ0NvbXBvbmVudCcgfHwgbmFtZSA9PT0gJ0RpcmVjdGl2ZScgfHwgbmFtZSA9PT0gJ1BpcGUnKTtcbn1cblxuLyoqIEdldHMgYWxsIEFuZ3VsYXIgZGVjb3JhdG9ycyBvZiBhIGdpdmVuIGNsYXNzIGRlY2xhcmF0aW9uLiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGdldE5nQ2xhc3NEZWNvcmF0b3JzKFxuICAgIG5vZGU6IHRzLkNsYXNzRGVjbGFyYXRpb24sIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlcik6IE5nRGVjb3JhdG9yW10ge1xuICBpZiAoIW5vZGUuZGVjb3JhdG9ycykge1xuICAgIHJldHVybiBbXTtcbiAgfVxuICByZXR1cm4gZ2V0QW5ndWxhckRlY29yYXRvcnModHlwZUNoZWNrZXIsIG5vZGUuZGVjb3JhdG9ycyk7XG59XG4iXX0=