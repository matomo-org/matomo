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
        define("@angular/core/schematics/migrations/missing-injectable/providers_evaluator", ["require", "exports", "@angular/compiler-cli/src/ngtsc/annotations", "@angular/compiler-cli/src/ngtsc/partial_evaluator/src/interpreter"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.ProvidersEvaluator = void 0;
    const annotations_1 = require("@angular/compiler-cli/src/ngtsc/annotations");
    const interpreter_1 = require("@angular/compiler-cli/src/ngtsc/partial_evaluator/src/interpreter");
    /**
     * Providers evaluator that extends the ngtsc static interpreter. This is necessary because
     * the static interpreter by default only exposes the resolved value, but we are also interested
     * in the TypeScript nodes that declare providers. It would be possible to manually traverse the
     * AST to collect these nodes, but that would mean that we need to re-implement the static
     * interpreter in order to handle all possible scenarios. (e.g. spread operator, function calls,
     * callee scope). This can be avoided by simply extending the static interpreter and intercepting
     * the "visitObjectLiteralExpression" method.
     */
    class ProvidersEvaluator extends interpreter_1.StaticInterpreter {
        constructor() {
            super(...arguments);
            this._providerLiterals = [];
        }
        visitObjectLiteralExpression(node, context) {
            const resolvedValue = super.visitObjectLiteralExpression(node, Object.assign(Object.assign({}, context), { insideProviderDef: true }));
            // do not collect nested object literals. e.g. a provider could use a
            // spread assignment (which resolves to another object literal). In that
            // case the referenced object literal is not a provider object literal.
            if (!context.insideProviderDef) {
                this._providerLiterals.push({ node, resolvedValue });
            }
            return resolvedValue;
        }
        /**
         * Evaluates the given expression and returns its statically resolved value
         * and a list of object literals which define Angular providers.
         */
        evaluate(expr) {
            this._providerLiterals = [];
            const resolvedValue = this.visit(expr, {
                originatingFile: expr.getSourceFile(),
                absoluteModuleName: null,
                resolutionContext: expr.getSourceFile().fileName,
                scope: new Map(),
                foreignFunctionResolver: annotations_1.forwardRefResolver
            });
            return { resolvedValue, literals: this._providerLiterals };
        }
    }
    exports.ProvidersEvaluator = ProvidersEvaluator;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicHJvdmlkZXJzX2V2YWx1YXRvci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL21pc3NpbmctaW5qZWN0YWJsZS9wcm92aWRlcnNfZXZhbHVhdG9yLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7R0FNRzs7Ozs7Ozs7Ozs7OztJQUVILDZFQUErRTtJQUUvRSxtR0FBb0c7SUFRcEc7Ozs7Ozs7O09BUUc7SUFDSCxNQUFhLGtCQUFtQixTQUFRLCtCQUFpQjtRQUF6RDs7WUFDVSxzQkFBaUIsR0FBc0IsRUFBRSxDQUFDO1FBNkJwRCxDQUFDO1FBM0JDLDRCQUE0QixDQUFDLElBQWdDLEVBQUUsT0FBWTtZQUN6RSxNQUFNLGFBQWEsR0FDZixLQUFLLENBQUMsNEJBQTRCLENBQUMsSUFBSSxrQ0FBTSxPQUFPLEtBQUUsaUJBQWlCLEVBQUUsSUFBSSxJQUFFLENBQUM7WUFDcEYscUVBQXFFO1lBQ3JFLHdFQUF3RTtZQUN4RSx1RUFBdUU7WUFDdkUsSUFBSSxDQUFDLE9BQU8sQ0FBQyxpQkFBaUIsRUFBRTtnQkFDOUIsSUFBSSxDQUFDLGlCQUFpQixDQUFDLElBQUksQ0FBQyxFQUFDLElBQUksRUFBRSxhQUFhLEVBQUMsQ0FBQyxDQUFDO2FBQ3BEO1lBQ0QsT0FBTyxhQUFhLENBQUM7UUFDdkIsQ0FBQztRQUVEOzs7V0FHRztRQUNILFFBQVEsQ0FBQyxJQUFtQjtZQUMxQixJQUFJLENBQUMsaUJBQWlCLEdBQUcsRUFBRSxDQUFDO1lBQzVCLE1BQU0sYUFBYSxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsSUFBSSxFQUFFO2dCQUNyQyxlQUFlLEVBQUUsSUFBSSxDQUFDLGFBQWEsRUFBRTtnQkFDckMsa0JBQWtCLEVBQUUsSUFBSTtnQkFDeEIsaUJBQWlCLEVBQUUsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLFFBQVE7Z0JBQ2hELEtBQUssRUFBRSxJQUFJLEdBQUcsRUFBRTtnQkFDaEIsdUJBQXVCLEVBQUUsZ0NBQWtCO2FBQzVDLENBQUMsQ0FBQztZQUNILE9BQU8sRUFBQyxhQUFhLEVBQUUsUUFBUSxFQUFFLElBQUksQ0FBQyxpQkFBaUIsRUFBQyxDQUFDO1FBQzNELENBQUM7S0FDRjtJQTlCRCxnREE4QkMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHtmb3J3YXJkUmVmUmVzb2x2ZXJ9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvYW5ub3RhdGlvbnMnO1xuaW1wb3J0IHtSZXNvbHZlZFZhbHVlfSBmcm9tICdAYW5ndWxhci9jb21waWxlci1jbGkvc3JjL25ndHNjL3BhcnRpYWxfZXZhbHVhdG9yJztcbmltcG9ydCB7U3RhdGljSW50ZXJwcmV0ZXJ9IGZyb20gJ0Bhbmd1bGFyL2NvbXBpbGVyLWNsaS9zcmMvbmd0c2MvcGFydGlhbF9ldmFsdWF0b3Ivc3JjL2ludGVycHJldGVyJztcbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5leHBvcnQgaW50ZXJmYWNlIFByb3ZpZGVyTGl0ZXJhbCB7XG4gIG5vZGU6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uO1xuICByZXNvbHZlZFZhbHVlOiBSZXNvbHZlZFZhbHVlO1xufVxuXG4vKipcbiAqIFByb3ZpZGVycyBldmFsdWF0b3IgdGhhdCBleHRlbmRzIHRoZSBuZ3RzYyBzdGF0aWMgaW50ZXJwcmV0ZXIuIFRoaXMgaXMgbmVjZXNzYXJ5IGJlY2F1c2VcbiAqIHRoZSBzdGF0aWMgaW50ZXJwcmV0ZXIgYnkgZGVmYXVsdCBvbmx5IGV4cG9zZXMgdGhlIHJlc29sdmVkIHZhbHVlLCBidXQgd2UgYXJlIGFsc28gaW50ZXJlc3RlZFxuICogaW4gdGhlIFR5cGVTY3JpcHQgbm9kZXMgdGhhdCBkZWNsYXJlIHByb3ZpZGVycy4gSXQgd291bGQgYmUgcG9zc2libGUgdG8gbWFudWFsbHkgdHJhdmVyc2UgdGhlXG4gKiBBU1QgdG8gY29sbGVjdCB0aGVzZSBub2RlcywgYnV0IHRoYXQgd291bGQgbWVhbiB0aGF0IHdlIG5lZWQgdG8gcmUtaW1wbGVtZW50IHRoZSBzdGF0aWNcbiAqIGludGVycHJldGVyIGluIG9yZGVyIHRvIGhhbmRsZSBhbGwgcG9zc2libGUgc2NlbmFyaW9zLiAoZS5nLiBzcHJlYWQgb3BlcmF0b3IsIGZ1bmN0aW9uIGNhbGxzLFxuICogY2FsbGVlIHNjb3BlKS4gVGhpcyBjYW4gYmUgYXZvaWRlZCBieSBzaW1wbHkgZXh0ZW5kaW5nIHRoZSBzdGF0aWMgaW50ZXJwcmV0ZXIgYW5kIGludGVyY2VwdGluZ1xuICogdGhlIFwidmlzaXRPYmplY3RMaXRlcmFsRXhwcmVzc2lvblwiIG1ldGhvZC5cbiAqL1xuZXhwb3J0IGNsYXNzIFByb3ZpZGVyc0V2YWx1YXRvciBleHRlbmRzIFN0YXRpY0ludGVycHJldGVyIHtcbiAgcHJpdmF0ZSBfcHJvdmlkZXJMaXRlcmFsczogUHJvdmlkZXJMaXRlcmFsW10gPSBbXTtcblxuICB2aXNpdE9iamVjdExpdGVyYWxFeHByZXNzaW9uKG5vZGU6IHRzLk9iamVjdExpdGVyYWxFeHByZXNzaW9uLCBjb250ZXh0OiBhbnkpIHtcbiAgICBjb25zdCByZXNvbHZlZFZhbHVlID1cbiAgICAgICAgc3VwZXIudmlzaXRPYmplY3RMaXRlcmFsRXhwcmVzc2lvbihub2RlLCB7Li4uY29udGV4dCwgaW5zaWRlUHJvdmlkZXJEZWY6IHRydWV9KTtcbiAgICAvLyBkbyBub3QgY29sbGVjdCBuZXN0ZWQgb2JqZWN0IGxpdGVyYWxzLiBlLmcuIGEgcHJvdmlkZXIgY291bGQgdXNlIGFcbiAgICAvLyBzcHJlYWQgYXNzaWdubWVudCAod2hpY2ggcmVzb2x2ZXMgdG8gYW5vdGhlciBvYmplY3QgbGl0ZXJhbCkuIEluIHRoYXRcbiAgICAvLyBjYXNlIHRoZSByZWZlcmVuY2VkIG9iamVjdCBsaXRlcmFsIGlzIG5vdCBhIHByb3ZpZGVyIG9iamVjdCBsaXRlcmFsLlxuICAgIGlmICghY29udGV4dC5pbnNpZGVQcm92aWRlckRlZikge1xuICAgICAgdGhpcy5fcHJvdmlkZXJMaXRlcmFscy5wdXNoKHtub2RlLCByZXNvbHZlZFZhbHVlfSk7XG4gICAgfVxuICAgIHJldHVybiByZXNvbHZlZFZhbHVlO1xuICB9XG5cbiAgLyoqXG4gICAqIEV2YWx1YXRlcyB0aGUgZ2l2ZW4gZXhwcmVzc2lvbiBhbmQgcmV0dXJucyBpdHMgc3RhdGljYWxseSByZXNvbHZlZCB2YWx1ZVxuICAgKiBhbmQgYSBsaXN0IG9mIG9iamVjdCBsaXRlcmFscyB3aGljaCBkZWZpbmUgQW5ndWxhciBwcm92aWRlcnMuXG4gICAqL1xuICBldmFsdWF0ZShleHByOiB0cy5FeHByZXNzaW9uKSB7XG4gICAgdGhpcy5fcHJvdmlkZXJMaXRlcmFscyA9IFtdO1xuICAgIGNvbnN0IHJlc29sdmVkVmFsdWUgPSB0aGlzLnZpc2l0KGV4cHIsIHtcbiAgICAgIG9yaWdpbmF0aW5nRmlsZTogZXhwci5nZXRTb3VyY2VGaWxlKCksXG4gICAgICBhYnNvbHV0ZU1vZHVsZU5hbWU6IG51bGwsXG4gICAgICByZXNvbHV0aW9uQ29udGV4dDogZXhwci5nZXRTb3VyY2VGaWxlKCkuZmlsZU5hbWUsXG4gICAgICBzY29wZTogbmV3IE1hcCgpLFxuICAgICAgZm9yZWlnbkZ1bmN0aW9uUmVzb2x2ZXI6IGZvcndhcmRSZWZSZXNvbHZlclxuICAgIH0pO1xuICAgIHJldHVybiB7cmVzb2x2ZWRWYWx1ZSwgbGl0ZXJhbHM6IHRoaXMuX3Byb3ZpZGVyTGl0ZXJhbHN9O1xuICB9XG59XG4iXX0=