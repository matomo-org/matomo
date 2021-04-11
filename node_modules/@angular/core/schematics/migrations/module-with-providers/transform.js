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
        define("@angular/core/schematics/migrations/module-with-providers/transform", ["require", "exports", "@angular/compiler-cli/src/ngtsc/imports", "@angular/compiler-cli/src/ngtsc/partial_evaluator", "@angular/compiler-cli/src/ngtsc/reflection", "typescript", "@angular/core/schematics/migrations/module-with-providers/util"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ModuleWithProvidersTransform = void 0;
    const imports_1 = require("@angular/compiler-cli/src/ngtsc/imports");
    const partial_evaluator_1 = require("@angular/compiler-cli/src/ngtsc/partial_evaluator");
    const reflection_1 = require("@angular/compiler-cli/src/ngtsc/reflection");
    const ts = require("typescript");
    const util_1 = require("@angular/core/schematics/migrations/module-with-providers/util");
    const TODO_COMMENT = 'TODO: The following node requires a generic type for `ModuleWithProviders`';
    class ModuleWithProvidersTransform {
        constructor(typeChecker, getUpdateRecorder) {
            this.typeChecker = typeChecker;
            this.getUpdateRecorder = getUpdateRecorder;
            this.printer = ts.createPrinter();
            this.partialEvaluator = new partial_evaluator_1.PartialEvaluator(new reflection_1.TypeScriptReflectionHost(this.typeChecker), this.typeChecker, 
            /* dependencyTracker */ null);
        }
        /** Migrates a given NgModule by walking through the referenced providers and static methods. */
        migrateModule(module) {
            return module.staticMethodsWithoutType.map(this._migrateStaticNgModuleMethod.bind(this))
                .filter(v => v);
        }
        /** Migrates a ModuleWithProviders type definition that has no explicit generic type */
        migrateType(type) {
            const parent = type.parent;
            let moduleText;
            if ((ts.isFunctionDeclaration(parent) || ts.isMethodDeclaration(parent)) && parent.body) {
                const returnStatement = parent.body.statements.find(ts.isReturnStatement);
                // No return type found, exit
                if (!returnStatement || !returnStatement.expression) {
                    return [{ node: parent, message: `Return type is not statically analyzable.` }];
                }
                moduleText = this._getNgModuleTypeOfExpression(returnStatement.expression);
            }
            else if (ts.isPropertyDeclaration(parent) || ts.isVariableDeclaration(parent)) {
                if (!parent.initializer) {
                    addTodoToNode(type, TODO_COMMENT);
                    this._updateNode(type, type);
                    return [{ node: parent, message: `Unable to determine type for declaration.` }];
                }
                moduleText = this._getNgModuleTypeOfExpression(parent.initializer);
            }
            if (moduleText) {
                this._addGenericToTypeReference(type, moduleText);
                return [];
            }
            return [{ node: parent, message: `Type is not statically analyzable.` }];
        }
        /** Add a given generic to a type reference node */
        _addGenericToTypeReference(node, typeName) {
            const newGenericExpr = util_1.createModuleWithProvidersType(typeName, node);
            this._updateNode(node, newGenericExpr);
        }
        /**
         * Migrates a given static method if its ModuleWithProviders does not provide
         * a generic type.
         */
        _updateStaticMethodType(method, typeName) {
            const newGenericExpr = util_1.createModuleWithProvidersType(typeName, method.type);
            const newMethodDecl = ts.updateMethod(method, method.decorators, method.modifiers, method.asteriskToken, method.name, method.questionToken, method.typeParameters, method.parameters, newGenericExpr, method.body);
            this._updateNode(method, newMethodDecl);
        }
        /** Whether the resolved value map represents a ModuleWithProviders object */
        isModuleWithProvidersType(value) {
            const ngModule = value.get('ngModule') !== undefined;
            const providers = value.get('providers') !== undefined;
            return ngModule && (value.size === 1 || (providers && value.size === 2));
        }
        /**
         * Determine the generic type of a suspected ModuleWithProviders return type and add it
         * explicitly
         */
        _migrateStaticNgModuleMethod(node) {
            const returnStatement = node.body &&
                node.body.statements.find(n => ts.isReturnStatement(n));
            // No return type found, exit
            if (!returnStatement || !returnStatement.expression) {
                return { node: node, message: `Return type is not statically analyzable.` };
            }
            const moduleText = this._getNgModuleTypeOfExpression(returnStatement.expression);
            if (moduleText) {
                this._updateStaticMethodType(node, moduleText);
                return null;
            }
            return { node: node, message: `Method type is not statically analyzable.` };
        }
        /** Evaluate and return the ngModule type from an expression */
        _getNgModuleTypeOfExpression(expr) {
            const evaluatedExpr = this.partialEvaluator.evaluate(expr);
            return this._getTypeOfResolvedValue(evaluatedExpr);
        }
        /**
         * Visits a given object literal expression to determine the ngModule type. If the expression
         * cannot be resolved, add a TODO to alert the user.
         */
        _getTypeOfResolvedValue(value) {
            if (value instanceof Map && this.isModuleWithProvidersType(value)) {
                const mapValue = value.get('ngModule');
                if (mapValue instanceof imports_1.Reference && ts.isClassDeclaration(mapValue.node) &&
                    mapValue.node.name) {
                    return mapValue.node.name.text;
                }
                else if (mapValue instanceof partial_evaluator_1.DynamicValue) {
                    addTodoToNode(mapValue.node, TODO_COMMENT);
                    this._updateNode(mapValue.node, mapValue.node);
                }
            }
            return undefined;
        }
        _updateNode(node, newNode) {
            const newText = this.printer.printNode(ts.EmitHint.Unspecified, newNode, node.getSourceFile());
            const recorder = this.getUpdateRecorder(node.getSourceFile());
            recorder.remove(node.getStart(), node.getWidth());
            recorder.insertRight(node.getStart(), newText);
        }
    }
    exports.ModuleWithProvidersTransform = ModuleWithProvidersTransform;
    /**
     * Adds a to-do to the given TypeScript node which alerts developers to fix
     * potential issues identified by the migration.
     */
    function addTodoToNode(node, text) {
        ts.setSyntheticLeadingComments(node, [{
                pos: -1,
                end: -1,
                hasTrailingNewLine: false,
                kind: ts.SyntaxKind.MultiLineCommentTrivia,
                text: ` ${text} `
            }]);
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidHJhbnNmb3JtLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvbW9kdWxlLXdpdGgtcHJvdmlkZXJzL3RyYW5zZm9ybS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFHSCxxRUFBa0U7SUFDbEUseUZBQWtJO0lBQ2xJLDJFQUFvRjtJQUNwRixpQ0FBaUM7SUFHakMseUZBQXFEO0lBT3JELE1BQU0sWUFBWSxHQUFHLDRFQUE0RSxDQUFDO0lBRWxHLE1BQWEsNEJBQTRCO1FBTXZDLFlBQ1ksV0FBMkIsRUFDM0IsaUJBQXdEO1lBRHhELGdCQUFXLEdBQVgsV0FBVyxDQUFnQjtZQUMzQixzQkFBaUIsR0FBakIsaUJBQWlCLENBQXVDO1lBUDVELFlBQU8sR0FBRyxFQUFFLENBQUMsYUFBYSxFQUFFLENBQUM7WUFDN0IscUJBQWdCLEdBQXFCLElBQUksb0NBQWdCLENBQzdELElBQUkscUNBQXdCLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxFQUFFLElBQUksQ0FBQyxXQUFXO1lBQ2hFLHVCQUF1QixDQUFDLElBQUksQ0FBQyxDQUFDO1FBSXFDLENBQUM7UUFFeEUsZ0dBQWdHO1FBQ2hHLGFBQWEsQ0FBQyxNQUF3QjtZQUNwQyxPQUFPLE1BQU0sQ0FBQyx3QkFBd0IsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLDRCQUE0QixDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztpQkFDNUUsTUFBTSxDQUFDLENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQyxDQUFzQixDQUFDO1FBQ2xELENBQUM7UUFFRCx1RkFBdUY7UUFDdkYsV0FBVyxDQUFDLElBQTBCO1lBQ3BDLE1BQU0sTUFBTSxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUM7WUFDM0IsSUFBSSxVQUE0QixDQUFDO1lBQ2pDLElBQUksQ0FBQyxFQUFF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Q0FBQztnQkFDUCxrQkFBa0IsRUFBRSxLQUFLO2dCQUN6QixJQUFJLEVBQUUsRUFBRSxDQUFDLFVBQVUsQ0FBQyxzQkFBc0I7Z0JBQzFDLElBQUksRUFBRSxJQUFJLElBQUksR0FBRzthQUNsQixDQUFDLENBQUMsQ0FBQztJQUNyQyxDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCB7VXBkYXRlUmVjb3JkZXJ9IGZyb20gJ0Bhbmd1bGFyLWRldmtpdC9zY2hlbWF0aWNzJztcbmltcG9ydCB7UmVmZXJlbmNlfSBmcm9tICdAYW5ndWxhci9jb21waWxlci1jbGkvc3JjL25ndHNjL2ltcG9ydHMnO1xuaW1wb3J0IHtEeW5hbWljVmFsdWUsIFBhcnRpYWxFdmFsdWF0b3IsIFJlc29sdmVkVmFsdWUsIFJlc29sdmVkVmFsdWVNYXB9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvcGFydGlhbF9ldmFsdWF0b3InO1xuaW1wb3J0IHtUeXBlU2NyaXB0UmVmbGVjdGlvbkhvc3R9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvcmVmbGVjdGlvbic7XG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcblxuaW1wb3J0IHtSZXNvbHZlZE5nTW9kdWxlfSBmcm9tICcuL2NvbGxlY3Rvcic7XG5pbXBvcnQge2NyZWF0ZU1vZHVsZVdpdGhQcm92aWRlcnNUeXBlfSBmcm9tICcuL3V0aWwnO1xuXG5leHBvcnQgaW50ZXJmYWNlIEFuYWx5c2lzRmFpbHVyZSB7XG4gIG5vZGU6IHRzLk5vZGU7XG4gIG1lc3NhZ2U6IHN0cmluZztcbn1cblxuY29uc3QgVE9ET19DT01NRU5UID0gJ1RPRE86IFRoZSBmb2xsb3dpbmcgbm9kZSByZXF1aXJlcyBhIGdlbmVyaWMgdHlwZSBmb3IgYE1vZHVsZVdpdGhQcm92aWRlcnNgJztcblxuZXhwb3J0IGNsYXNzIE1vZHVsZVdpdGhQcm92aWRlcnNUcmFuc2Zvcm0ge1xuICBwcml2YXRlIHByaW50ZXIgPSB0cy5jcmVhdGVQcmludGVyKCk7XG4gIHByaXZhdGUgcGFydGlhbEV2YWx1YXRvcjogUGFydGlhbEV2YWx1YXRvciA9IG5ldyBQYXJ0aWFsRXZhbHVhdG9yKFxuICAgICAgbmV3IFR5cGVTY3JpcHRSZWZsZWN0aW9uSG9zdCh0aGlzLnR5cGVDaGVja2VyKSwgdGhpcy50eXBlQ2hlY2tlcixcbiAgICAgIC8qIGRlcGVuZGVuY3lUcmFja2VyICovIG51bGwpO1xuXG4gIGNvbnN0cnVjdG9yKFxuICAgICAgcHJpdmF0ZSB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIsXG4gICAgICBwcml2YXRlIGdldFVwZGF0ZVJlY29yZGVyOiAoc2Y6IHRzLlNvdXJjZUZpbGUpID0+IFVwZGF0ZVJlY29yZGVyKSB7fVxuXG4gIC8qKiBNaWdyYXRlcyBhIGdpdmVuIE5nTW9kdWxlIGJ5IHdhbGtpbmcgdGhyb3VnaCB0aGUgcmVmZXJlbmNlZCBwcm92aWRlcnMgYW5kIHN0YXRpYyBtZXRob2RzLiAqL1xuICBtaWdyYXRlTW9kdWxlKG1vZHVsZTogUmVzb2x2ZWROZ01vZHVsZSk6IEFuYWx5c2lzRmFpbHVyZVtdIHtcbiAgICByZXR1cm4gbW9kdWxlLnN0YXRpY01ldGhvZHNXaXRob3V0VHlwZS5tYXAodGhpcy5fbWlncmF0ZVN0YXRpY05nTW9kdWxlTWV0aG9kLmJpbmQodGhpcykpXG4gICAgICAgICAgICAgICAuZmlsdGVyKHYgPT4gdikgYXMgQW5hbHlzaXNGYWlsdXJlW107XG4gIH1cblxuICAvKiogTWlncmF0ZXMgYSBNb2R1bGVXaXRoUHJvdmlkZXJzIHR5cGUgZGVmaW5pdGlvbiB0aGF0IGhhcyBubyBleHBsaWNpdCBnZW5lcmljIHR5cGUgKi9cbiAgbWlncmF0ZVR5cGUodHlwZTogdHMuVHlwZVJlZmVyZW5jZU5vZGUpOiBBbmFseXNpc0ZhaWx1cmVbXSB7XG4gICAgY29uc3QgcGFyZW50ID0gdHlwZS5wYXJlbnQ7XG4gICAgbGV0IG1vZHVsZVRleHQ6IHN0cmluZ3x1bmRlZmluZWQ7XG4gICAgaWYgKCh0cy5pc0Z1bmN0aW9uRGVjbGFyYXRpb24ocGFyZW50KSB8fCB0cy5pc01ldGhvZERlY2xhcmF0aW9uKHBhcmVudCkpICYmIHBhcmVudC5ib2R5KSB7XG4gICAgICBjb25zdCByZXR1cm5TdGF0ZW1lbnQgPSBwYXJlbnQuYm9keS5zdGF0ZW1lbnRzLmZpbmQodHMuaXNSZXR1cm5TdGF0ZW1lbnQpO1xuXG4gICAgICAvLyBObyByZXR1cm4gdHlwZSBmb3VuZCwgZXhpdFxuICAgICAgaWYgKCFyZXR1cm5TdGF0ZW1lbnQgfHwgIXJldHVyblN0YXRlbWVudC5leHByZXNzaW9uKSB7XG4gICAgICAgIHJldHVybiBbe25vZGU6IHBhcmVudCwgbWVzc2FnZTogYFJldHVybiB0eXBlIGlzIG5vdCBzdGF0aWNhbGx5IGFuYWx5emFibGUuYH1dO1xuICAgICAgfVxuXG4gICAgICBtb2R1bGVUZXh0ID0gdGhpcy5fZ2V0TmdNb2R1bGVUeXBlT2ZFeHByZXNzaW9uKHJldHVyblN0YXRlbWVudC5leHByZXNzaW9uKTtcbiAgICB9IGVsc2UgaWYgKHRzLmlzUHJvcGVydHlEZWNsYXJhdGlvbihwYXJlbnQpIHx8IHRzLmlzVmFyaWFibGVEZWNsYXJhdGlvbihwYXJlbnQpKSB7XG4gICAgICBpZiAoIXBhcmVudC5pbml0aWFsaXplcikge1xuICAgICAgICBhZGRUb2RvVG9Ob2RlKHR5cGUsIFRPRE9fQ09NTUVOVCk7XG4gICAgICAgIHRoaXMuX3VwZGF0ZU5vZGUodHlwZSwgdHlwZSk7XG4gICAgICAgIHJldHVybiBbe25vZGU6IHBhcmVudCwgbWVzc2FnZTogYFVuYWJsZSB0byBkZXRlcm1pbmUgdHlwZSBmb3IgZGVjbGFyYXRpb24uYH1dO1xuICAgICAgfVxuXG4gICAgICBtb2R1bGVUZXh0ID0gdGhpcy5fZ2V0TmdNb2R1bGVUeXBlT2ZFeHByZXNzaW9uKHBhcmVudC5pbml0aWFsaXplcik7XG4gICAgfVxuXG4gICAgaWYgKG1vZHVsZVRleHQpIHtcbiAgICAgIHRoaXMuX2FkZEdlbmVyaWNUb1R5cGVSZWZlcmVuY2UodHlwZSwgbW9kdWxlVGV4dCk7XG4gICAgICByZXR1cm4gW107XG4gICAgfVxuXG4gICAgcmV0dXJuIFt7bm9kZTogcGFyZW50LCBtZXNzYWdlOiBgVHlwZSBpcyBub3Qgc3RhdGljYWxseSBhbmFseXphYmxlLmB9XTtcbiAgfVxuXG4gIC8qKiBBZGQgYSBnaXZlbiBnZW5lcmljIHRvIGEgdHlwZSByZWZlcmVuY2Ugbm9kZSAqL1xuICBwcml2YXRlIF9hZGRHZW5lcmljVG9UeXBlUmVmZXJlbmNlKG5vZGU6IHRzLlR5cGVSZWZlcmVuY2VOb2RlLCB0eXBlTmFtZTogc3RyaW5nKSB7XG4gICAgY29uc3QgbmV3R2VuZXJpY0V4cHIgPSBjcmVhdGVNb2R1bGVXaXRoUHJvdmlkZXJzVHlwZSh0eXBlTmFtZSwgbm9kZSk7XG4gICAgdGhpcy5fdXBkYXRlTm9kZShub2RlLCBuZXdHZW5lcmljRXhwcik7XG4gIH1cblxuICAvKipcbiAgICogTWlncmF0ZXMgYSBnaXZlbiBzdGF0aWMgbWV0aG9kIGlmIGl0cyBNb2R1bGVXaXRoUHJvdmlkZXJzIGRvZXMgbm90IHByb3ZpZGVcbiAgICogYSBnZW5lcmljIHR5cGUuXG4gICAqL1xuICBwcml2YXRlIF91cGRhdGVTdGF0aWNNZXRob2RUeXBlKG1ldGhvZDogdHMuTWV0aG9kRGVjbGFyYXRpb24sIHR5cGVOYW1lOiBzdHJpbmcpIHtcbiAgICBjb25zdCBuZXdHZW5lcmljRXhwciA9XG4gICAgICAgIGNyZWF0ZU1vZHVsZVdpdGhQcm92aWRlcnNUeXBlKHR5cGVOYW1lLCBtZXRob2QudHlwZSBhcyB0cy5UeXBlUmVmZXJlbmNlTm9kZSk7XG4gICAgY29uc3QgbmV3TWV0aG9kRGVjbCA9IHRzLnVwZGF0ZU1ldGhvZChcbiAgICAgICAgbWV0aG9kLCBtZXRob2QuZGVjb3JhdG9ycywgbWV0aG9kLm1vZGlmaWVycywgbWV0aG9kLmFzdGVyaXNrVG9rZW4sIG1ldGhvZC5uYW1lLFxuICAgICAgICBtZXRob2QucXVlc3Rpb25Ub2tlbiwgbWV0aG9kLnR5cGVQYXJhbWV0ZXJzLCBtZXRob2QucGFyYW1ldGVycywgbmV3R2VuZXJpY0V4cHIsXG4gICAgICAgIG1ldGhvZC5ib2R5KTtcblxuICAgIHRoaXMuX3VwZGF0ZU5vZGUobWV0aG9kLCBuZXdNZXRob2REZWNsKTtcbiAgfVxuXG4gIC8qKiBXaGV0aGVyIHRoZSByZXNvbHZlZCB2YWx1ZSBtYXAgcmVwcmVzZW50cyBhIE1vZHVsZVdpdGhQcm92aWRlcnMgb2JqZWN0ICovXG4gIGlzTW9kdWxlV2l0aFByb3ZpZGVyc1R5cGUodmFsdWU6IFJlc29sdmVkVmFsdWVNYXApOiBib29sZWFuIHtcbiAgICBjb25zdCBuZ01vZHVsZSA9IHZhbHVlLmdldCgnbmdNb2R1bGUnKSAhPT0gdW5kZWZpbmVkO1xuICAgIGNvbnN0IHByb3ZpZGVycyA9IHZhbHVlLmdldCgncHJvdmlkZXJzJykgIT09IHVuZGVmaW5lZDtcblxuICAgIHJldHVybiBuZ01vZHVsZSAmJiAodmFsdWUuc2l6ZSA9PT0gMSB8fCAocHJvdmlkZXJzICYmIHZhbHVlLnNpemUgPT09IDIpKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBEZXRlcm1pbmUgdGhlIGdlbmVyaWMgdHlwZSBvZiBhIHN1c3BlY3RlZCBNb2R1bGVXaXRoUHJvdmlkZXJzIHJldHVybiB0eXBlIGFuZCBhZGQgaXRcbiAgICogZXhwbGljaXRseVxuICAgKi9cbiAgcHJpdmF0ZSBfbWlncmF0ZVN0YXRpY05nTW9kdWxlTWV0aG9kKG5vZGU6IHRzLk1ldGhvZERlY2xhcmF0aW9uKTogQW5hbHlzaXNGYWlsdXJlfG51bGwge1xuICAgIGNvbnN0IHJldHVyblN0YXRlbWVudCA9IG5vZGUuYm9keSAmJlxuICAgICAgICBub2RlLmJvZHkuc3RhdGVtZW50cy5maW5kKG4gPT4gdHMuaXNSZXR1cm5TdGF0ZW1lbnQobikpIGFzIHRzLlJldHVyblN0YXRlbWVudCB8IHVuZGVmaW5lZDtcblxuICAgIC8vIE5vIHJldHVybiB0eXBlIGZvdW5kLCBleGl0XG4gICAgaWYgKCFyZXR1cm5TdGF0ZW1lbnQgfHwgIXJldHVyblN0YXRlbWVudC5leHByZXNzaW9uKSB7XG4gICAgICByZXR1cm4ge25vZGU6IG5vZGUsIG1lc3NhZ2U6IGBSZXR1cm4gdHlwZSBpcyBub3Qgc3RhdGljYWxseSBhbmFseXphYmxlLmB9O1xuICAgIH1cblxuICAgIGNvbnN0IG1vZHVsZVRleHQgPSB0aGlzLl9nZXROZ01vZHVsZVR5cGVPZkV4cHJlc3Npb24ocmV0dXJuU3RhdGVtZW50LmV4cHJlc3Npb24pO1xuXG4gICAgaWYgKG1vZHVsZVRleHQpIHtcbiAgICAgIHRoaXMuX3VwZGF0ZVN0YXRpY01ldGhvZFR5cGUobm9kZSwgbW9kdWxlVGV4dCk7XG4gICAgICByZXR1cm4gbnVsbDtcbiAgICB9XG5cbiAgICByZXR1cm4ge25vZGU6IG5vZGUsIG1lc3NhZ2U6IGBNZXRob2QgdHlwZSBpcyBub3Qgc3RhdGljYWxseSBhbmFseXphYmxlLmB9O1xuICB9XG5cbiAgLyoqIEV2YWx1YXRlIGFuZCByZXR1cm4gdGhlIG5nTW9kdWxlIHR5cGUgZnJvbSBhbiBleHByZXNzaW9uICovXG4gIHByaXZhdGUgX2dldE5nTW9kdWxlVHlwZU9mRXhwcmVzc2lvbihleHByOiB0cy5FeHByZXNzaW9uKTogc3RyaW5nfHVuZGVmaW5lZCB7XG4gICAgY29uc3QgZXZhbHVhdGVkRXhwciA9IHRoaXMucGFydGlhbEV2YWx1YXRvci5ldmFsdWF0ZShleHByKTtcbiAgICByZXR1cm4gdGhpcy5fZ2V0VHlwZU9mUmVzb2x2ZWRWYWx1ZShldmFsdWF0ZWRFeHByKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBWaXNpdHMgYSBnaXZlbiBvYmplY3QgbGl0ZXJhbCBleHByZXNzaW9uIHRvIGRldGVybWluZSB0aGUgbmdNb2R1bGUgdHlwZS4gSWYgdGhlIGV4cHJlc3Npb25cbiAgICogY2Fubm90IGJlIHJlc29sdmVkLCBhZGQgYSBUT0RPIHRvIGFsZXJ0IHRoZSB1c2VyLlxuICAgKi9cbiAgcHJpdmF0ZSBfZ2V0VHlwZU9mUmVzb2x2ZWRWYWx1ZSh2YWx1ZTogUmVzb2x2ZWRWYWx1ZSk6IHN0cmluZ3x1bmRlZmluZWQge1xuICAgIGlmICh2YWx1ZSBpbnN0YW5jZW9mIE1hcCAmJiB0aGlzLmlzTW9kdWxlV2l0aFByb3ZpZGVyc1R5cGUodmFsdWUpKSB7XG4gICAgICBjb25zdCBtYXBWYWx1ZSA9IHZhbHVlLmdldCgnbmdNb2R1bGUnKSE7XG4gICAgICBpZiAobWFwVmFsdWUgaW5zdGFuY2VvZiBSZWZlcmVuY2UgJiYgdHMuaXNDbGFzc0RlY2xhcmF0aW9uKG1hcFZhbHVlLm5vZGUpICYmXG4gICAgICAgICAgbWFwVmFsdWUubm9kZS5uYW1lKSB7XG4gICAgICAgIHJldHVybiBtYXBWYWx1ZS5ub2RlLm5hbWUudGV4dDtcbiAgICAgIH0gZWxzZSBpZiAobWFwVmFsdWUgaW5zdGFuY2VvZiBEeW5hbWljVmFsdWUpIHtcbiAgICAgICAgYWRkVG9kb1RvTm9kZShtYXBWYWx1ZS5ub2RlLCBUT0RPX0NPTU1FTlQpO1xuICAgICAgICB0aGlzLl91cGRhdGVOb2RlKG1hcFZhbHVlLm5vZGUsIG1hcFZhbHVlLm5vZGUpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHJldHVybiB1bmRlZmluZWQ7XG4gIH1cblxuICBwcml2YXRlIF91cGRhdGVOb2RlKG5vZGU6IHRzLk5vZGUsIG5ld05vZGU6IHRzLk5vZGUpIHtcbiAgICBjb25zdCBuZXdUZXh0ID0gdGhpcy5wcmludGVyLnByaW50Tm9kZSh0cy5FbWl0SGludC5VbnNwZWNpZmllZCwgbmV3Tm9kZSwgbm9kZS5nZXRTb3VyY2VGaWxlKCkpO1xuICAgIGNvbnN0IHJlY29yZGVyID0gdGhpcy5nZXRVcGRhdGVSZWNvcmRlcihub2RlLmdldFNvdXJjZUZpbGUoKSk7XG5cbiAgICByZWNvcmRlci5yZW1vdmUobm9kZS5nZXRTdGFydCgpLCBub2RlLmdldFdpZHRoKCkpO1xuICAgIHJlY29yZGVyLmluc2VydFJpZ2h0KG5vZGUuZ2V0U3RhcnQoKSwgbmV3VGV4dCk7XG4gIH1cbn1cblxuLyoqXG4gKiBBZGRzIGEgdG8tZG8gdG8gdGhlIGdpdmVuIFR5cGVTY3JpcHQgbm9kZSB3aGljaCBhbGVydHMgZGV2ZWxvcGVycyB0byBmaXhcbiAqIHBvdGVudGlhbCBpc3N1ZXMgaWRlbnRpZmllZCBieSB0aGUgbWlncmF0aW9uLlxuICovXG5mdW5jdGlvbiBhZGRUb2RvVG9Ob2RlKG5vZGU6IHRzLk5vZGUsIHRleHQ6IHN0cmluZykge1xuICB0cy5zZXRTeW50aGV0aWNMZWFkaW5nQ29tbWVudHMobm9kZSwgW3tcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcG9zOiAtMSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZW5kOiAtMSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaGFzVHJhaWxpbmdOZXdMaW5lOiBmYWxzZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAga2luZDogdHMuU3ludGF4S2luZC5NdWx0aUxpbmVDb21tZW50VHJpdmlhLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0ZXh0OiBgICR7dGV4dH0gYFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfV0pO1xufVxuIl19