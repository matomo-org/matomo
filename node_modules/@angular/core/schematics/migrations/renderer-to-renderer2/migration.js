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
        define("@angular/core/schematics/migrations/renderer-to-renderer2/migration", ["require", "exports", "typescript"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.migrateExpression = void 0;
    const ts = require("typescript");
    /**
     * Migrates a function call expression from `Renderer` to `Renderer2`.
     * Returns null if the expression should be dropped.
     */
    function migrateExpression(node, typeChecker) {
        if (isPropertyAccessCallExpression(node)) {
            switch (node.expression.name.getText()) {
                case 'setElementProperty':
                    return { node: renameMethodCall(node, 'setProperty') };
                case 'setText':
                    return { node: renameMethodCall(node, 'setValue') };
                case 'listenGlobal':
                    return { node: renameMethodCall(node, 'listen') };
                case 'selectRootElement':
                    return { node: migrateSelectRootElement(node) };
                case 'setElementClass':
                    return { node: migrateSetElementClass(node) };
                case 'setElementStyle':
                    return { node: migrateSetElementStyle(node, typeChecker) };
                case 'invokeElementMethod':
                    return { node: migrateInvokeElementMethod(node) };
                case 'setBindingDebugInfo':
                    return { node: null };
                case 'createViewRoot':
                    return { node: migrateCreateViewRoot(node) };
                case 'setElementAttribute':
                    return {
                        node: switchToHelperCall(node, "__ngRendererSetElementAttributeHelper" /* setElementAttribute */, node.arguments),
                        requiredHelpers: [
                            "AnyDuringRendererMigration" /* any */, "__ngRendererSplitNamespaceHelper" /* splitNamespace */, "__ngRendererSetElementAttributeHelper" /* setElementAttribute */
                        ]
                    };
                case 'createElement':
                    return {
                        node: switchToHelperCall(node, "__ngRendererCreateElementHelper" /* createElement */, node.arguments.slice(0, 2)),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererSplitNamespaceHelper" /* splitNamespace */, "__ngRendererCreateElementHelper" /* createElement */]
                    };
                case 'createText':
                    return {
                        node: switchToHelperCall(node, "__ngRendererCreateTextHelper" /* createText */, node.arguments.slice(0, 2)),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererCreateTextHelper" /* createText */]
                    };
                case 'createTemplateAnchor':
                    return {
                        node: switchToHelperCall(node, "__ngRendererCreateTemplateAnchorHelper" /* createTemplateAnchor */, node.arguments.slice(0, 1)),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererCreateTemplateAnchorHelper" /* createTemplateAnchor */]
                    };
                case 'projectNodes':
                    return {
                        node: switchToHelperCall(node, "__ngRendererProjectNodesHelper" /* projectNodes */, node.arguments),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererProjectNodesHelper" /* projectNodes */]
                    };
                case 'animate':
                    return {
                        node: migrateAnimateCall(),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererAnimateHelper" /* animate */]
                    };
                case 'destroyView':
                    return {
                        node: switchToHelperCall(node, "__ngRendererDestroyViewHelper" /* destroyView */, [node.arguments[1]]),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererDestroyViewHelper" /* destroyView */]
                    };
                case 'detachView':
                    return {
                        node: switchToHelperCall(node, "__ngRendererDetachViewHelper" /* detachView */, [node.arguments[0]]),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererDetachViewHelper" /* detachView */]
                    };
                case 'attachViewAfter':
                    return {
                        node: switchToHelperCall(node, "__ngRendererAttachViewAfterHelper" /* attachViewAfter */, node.arguments),
                        requiredHelpers: ["AnyDuringRendererMigration" /* any */, "__ngRendererAttachViewAfterHelper" /* attachViewAfter */]
                    };
            }
        }
        return { node };
    }
    exports.migrateExpression = migrateExpression;
    /** Checks whether a node is a PropertyAccessExpression. */
    function isPropertyAccessCallExpression(node) {
        return ts.isCallExpression(node) && ts.isPropertyAccessExpression(node.expression);
    }
    /** Renames a method call while keeping all of the parameters in place. */
    function renameMethodCall(node, newName) {
        const newExpression = ts.updatePropertyAccess(node.expression, node.expression.expression, ts.createIdentifier(newName));
        return ts.updateCall(node, newExpression, node.typeArguments, node.arguments);
    }
    /**
     * Migrates a `selectRootElement` call by removing the last argument which is no longer supported.
     */
    function migrateSelectRootElement(node) {
        // The only thing we need to do is to drop the last argument
        // (`debugInfo`), if the consumer was passing it in.
        if (node.arguments.length > 1) {
            return ts.updateCall(node, node.expression, node.typeArguments, [node.arguments[0]]);
        }
        return node;
    }
    /**
     * Migrates a call to `setElementClass` either to a call to `addClass` or `removeClass`, or
     * to an expression like `isAdd ? addClass(el, className) : removeClass(el, className)`.
     */
    function migrateSetElementClass(node) {
        // Clone so we don't mutate by accident. Note that we assume that
        // the user's code is providing all three required arguments.
        const outputMethodArgs = node.arguments.slice();
        const isAddArgument = outputMethodArgs.pop();
        const createRendererCall = (isAdd) => {
            const innerExpression = node.expression.expression;
            const topExpression = ts.createPropertyAccess(innerExpression, isAdd ? 'addClass' : 'removeClass');
            return ts.createCall(topExpression, [], node.arguments.slice(0, 2));
        };
        // If the call has the `isAdd` argument as a literal boolean, we can map it directly to
        // `addClass` or `removeClass`. Note that we can't use the type checker here, because it
        // won't tell us whether the value resolves to true or false.
        if (isAddArgument.kind === ts.SyntaxKind.TrueKeyword ||
            isAddArgument.kind === ts.SyntaxKind.FalseKeyword) {
            return createRendererCall(isAddArgument.kind === ts.SyntaxKind.TrueKeyword);
        }
        // Otherwise create a ternary on the variable.
        return ts.createConditional(isAddArgument, createRendererCall(true), createRendererCall(false));
    }
    /**
     * Migrates a call to `setElementStyle` call either to a call to
     * `setStyle` or `removeStyle`. or to an expression like
     * `value == null ? removeStyle(el, key) : setStyle(el, key, value)`.
     */
    function migrateSetElementStyle(node, typeChecker) {
        const args = node.arguments;
        const addMethodName = 'setStyle';
        const removeMethodName = 'removeStyle';
        const lastArgType = args[2] ?
            typeChecker.typeToString(typeChecker.getTypeAtLocation(args[2]), node, ts.TypeFormatFlags.AddUndefined) :
            null;
        // Note that for a literal null, TS considers it a `NullKeyword`,
        // whereas a literal `undefined` is just an Identifier.
        if (args.length === 2 || lastArgType === 'null' || lastArgType === 'undefined') {
            // If we've got a call with two arguments, or one with three arguments where the last one is
            // `undefined` or `null`, we can safely switch to a `removeStyle` call.
            const innerExpression = node.expression.expression;
            const topExpression = ts.createPropertyAccess(innerExpression, removeMethodName);
            return ts.createCall(topExpression, [], args.slice(0, 2));
        }
        else if (args.length === 3) {
            // We need the checks for string literals, because the type of something
            // like `"blue"` is the literal `blue`, not `string`.
            if (lastArgType === 'string' || lastArgType === 'number' || ts.isStringLiteral(args[2]) ||
                ts.isNoSubstitutionTemplateLiteral(args[2]) || ts.isNumericLiteral(args[2])) {
                // If we've got three arguments and the last one is a string literal or a number, we
                // can safely rename to `setStyle`.
                return renameMethodCall(node, addMethodName);
            }
            else {
                // Otherwise migrate to a ternary that looks like:
                // `value == null ? removeStyle(el, key) : setStyle(el, key, value)`
                const condition = ts.createBinary(args[2], ts.SyntaxKind.EqualsEqualsToken, ts.createNull());
                const whenNullCall = renameMethodCall(ts.createCall(node.expression, [], args.slice(0, 2)), removeMethodName);
                return ts.createConditional(condition, whenNullCall, renameMethodCall(node, addMethodName));
            }
        }
        return node;
    }
    /**
     * Migrates a call to `invokeElementMethod(target, method, [arg1, arg2])` either to
     * `target.method(arg1, arg2)` or `(target as any)[method].apply(target, [arg1, arg2])`.
     */
    function migrateInvokeElementMethod(node) {
        const [target, name, args] = node.arguments;
        const isNameStatic = ts.isStringLiteral(name) || ts.isNoSubstitutionTemplateLiteral(name);
        const isArgsStatic = !args || ts.isArrayLiteralExpression(args);
        if (isNameStatic && isArgsStatic) {
            // If the name is a static string and the arguments are an array literal,
            // we can safely convert the node into a call expression.
            const expression = ts.createPropertyAccess(target, name.text);
            const callArguments = args ? args.elements : [];
            return ts.createCall(expression, [], callArguments);
        }
        else {
            // Otherwise create an expression in the form of `(target as any)[name].apply(target, args)`.
            const asExpression = ts.createParen(ts.createAsExpression(target, ts.createKeywordTypeNode(ts.SyntaxKind.AnyKeyword)));
            const elementAccess = ts.createElementAccess(asExpression, name);
            const applyExpression = ts.createPropertyAccess(elementAccess, 'apply');
            return ts.createCall(applyExpression, [], args ? [target, args] : [target]);
        }
    }
    /** Migrates a call to `createViewRoot` to whatever node was passed in as the first argument. */
    function migrateCreateViewRoot(node) {
        return node.arguments[0];
    }
    /** Migrates a call to `migrate` a direct call to the helper. */
    function migrateAnimateCall() {
        return ts.createCall(ts.createIdentifier("__ngRendererAnimateHelper" /* animate */), [], []);
    }
    /**
     * Switches out a call to the `Renderer` to a call to one of our helper functions.
     * Most of the helpers accept an instance of `Renderer2` as the first argument and all
     * subsequent arguments differ.
     * @param node Node of the original method call.
     * @param helper Name of the helper with which to replace the original call.
     * @param args Arguments that should be passed into the helper after the renderer argument.
     */
    function switchToHelperCall(node, helper, args) {
        return ts.createCall(ts.createIdentifier(helper), [], [node.expression.expression, ...args]);
    }
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibWlncmF0aW9uLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zY2hlbWF0aWNzL21pZ3JhdGlvbnMvcmVuZGVyZXItdG8tcmVuZGVyZXIyL21pZ3JhdGlvbi50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFPakM7OztPQUdHO0lBQ0gsU0FBZ0IsaUJBQWlCLENBQUMsSUFBdUIsRUFBRSxXQUEyQjtRQUVwRixJQUFJLDhCQUE4QixDQUFDLElBQUksQ0FBQyxFQUFFO1lBQ3hDLFFBQVEsSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsT0FBTyxFQUFFLEVBQUU7Z0JBQ3RDLEtBQUssb0JBQW9CO29CQUN2QixPQUFPLEVBQUMsSUFBSSxFQUFFLGdCQUFnQixDQUFDLElBQUksRUFBRSxhQUFhLENBQUMsRUFBQyxDQUFDO2dCQUN2RCxLQUFLLFNBQVM7b0JBQ1osT0FBTyxFQUFDLElBQUksRUFBRSxnQkFBZ0IsQ0FBQyxJQUFJLEVBQUUsVUFBVSxDQUFDLEVBQUMsQ0FBQztnQkFDcEQsS0FBSyxjQUFjO29CQUNqQixPQUFPLEVBQUMsSUFBSSxFQUFFLGdCQUFnQixDQUFDLElBQUksRUFBRSxRQUFRLENBQUMsRUFBQyxDQUFDO2dCQUNsRCxLQUFLLG1CQUFtQjtvQkFDdEIsT0FBTyxFQUFDLElBQUksRUFBRSx3QkFBd0IsQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDO2dCQUNoRCxLQUFLLGlCQUFpQjtvQkFDcEIsT0FBTyxFQUFDLElBQUksRUFBRSxzQkFBc0IsQ0FBQyxJQUFJLENBQUMsRUFBQyxDQUFDO2dCQUM5QyxLQUFLLGlCQUFpQjtvQkFDcEIsT0FBTyxFQUFDLElBQUksRUFBRSxzQkFBc0IsQ0FBQyxJQUFJLEVBQUUsV0FBVyxDQUFDLEVBQUMsQ0FBQztnQkFDM0QsS0FBSyxxQkFBcUI7b0JBQ3hCLE9B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akUsTUFBTSxlQUFlLEdBQUcsRUFBRSxDQUFDLG9CQUFvQixDQUFDLGFBQWEsRUFBRSxPQUFPLENBQUMsQ0FBQztZQUN4RSxPQUFPLEVBQUUsQ0FBQyxVQUFVLENBQUMsZUFBZSxFQUFFLEVBQUUsRUFBRSxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUM7U0FDN0U7SUFDSCxDQUFDO0lBRUQsZ0dBQWdHO0lBQ2hHLFNBQVMscUJBQXFCLENBQUMsSUFBdUI7UUFDcEQsT0FBTyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQzNCLENBQUM7SUFFRCxnRUFBZ0U7SUFDaEUsU0FBUyxrQkFBa0I7UUFDekIsT0FBTyxFQUFFLENBQUMsVUFBVSxDQUFDLEVBQUUsQ0FBQyxnQkFBZ0IsMkNBQXdCLEVBQUUsRUFBRSxFQUFFLEVBQUUsQ0FBQyxDQUFDO0lBQzVFLENBQUM7SUFFRDs7Ozs7OztPQU9HO0lBQ0gsU0FBUyxrQkFBa0IsQ0FDdkIsSUFBa0MsRUFBRSxNQUFzQixFQUMxRCxJQUFpRDtRQUNuRCxPQUFPLEVBQUUsQ0FBQyxVQUFVLENBQUMsRUFBRSxDQUFDLGdCQUFnQixDQUFDLE1BQU0sQ0FBQyxFQUFFLEVBQUUsRUFBRSxDQUFDLElBQUksQ0FBQyxVQUFVLENBQUMsVUFBVSxFQUFFLEdBQUcsSUFBSSxDQUFDLENBQUMsQ0FBQztJQUMvRixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBAbGljZW5zZVxuICogQ29weXJpZ2h0IEdvb2dsZSBMTEMgQWxsIFJpZ2h0cyBSZXNlcnZlZC5cbiAqXG4gKiBVc2Ugb2YgdGhpcyBzb3VyY2UgY29kZSBpcyBnb3Zlcm5lZCBieSBhbiBNSVQtc3R5bGUgbGljZW5zZSB0aGF0IGNhbiBiZVxuICogZm91bmQgaW4gdGhlIExJQ0VOU0UgZmlsZSBhdCBodHRwczovL2FuZ3VsYXIuaW8vbGljZW5zZVxuICovXG5cbmltcG9ydCAqIGFzIHRzIGZyb20gJ3R5cGVzY3JpcHQnO1xuXG5pbXBvcnQge0hlbHBlckZ1bmN0aW9ufSBmcm9tICcuL2hlbHBlcnMnO1xuXG4vKiogQSBjYWxsIGV4cHJlc3Npb24gdGhhdCBpcyBiYXNlZCBvbiBhIHByb3BlcnR5IGFjY2Vzcy4gKi9cbnR5cGUgUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbiA9IHRzLkNhbGxFeHByZXNzaW9uJntleHByZXNzaW9uOiB0cy5Qcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb259O1xuXG4vKipcbiAqIE1pZ3JhdGVzIGEgZnVuY3Rpb24gY2FsbCBleHByZXNzaW9uIGZyb20gYFJlbmRlcmVyYCB0byBgUmVuZGVyZXIyYC5cbiAqIFJldHVybnMgbnVsbCBpZiB0aGUgZXhwcmVzc2lvbiBzaG91bGQgYmUgZHJvcHBlZC5cbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIG1pZ3JhdGVFeHByZXNzaW9uKG5vZGU6IHRzLkNhbGxFeHByZXNzaW9uLCB0eXBlQ2hlY2tlcjogdHMuVHlwZUNoZWNrZXIpOlxuICAgIHtub2RlOiB0cy5Ob2RlfG51bGwsIHJlcXVpcmVkSGVscGVycz86IEhlbHBlckZ1bmN0aW9uW119IHtcbiAgaWYgKGlzUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbihub2RlKSkge1xuICAgIHN3aXRjaCAobm9kZS5leHByZXNzaW9uLm5hbWUuZ2V0VGV4dCgpKSB7XG4gICAgICBjYXNlICdzZXRFbGVtZW50UHJvcGVydHknOlxuICAgICAgICByZXR1cm4ge25vZGU6IHJlbmFtZU1ldGhvZENhbGwobm9kZSwgJ3NldFByb3BlcnR5Jyl9O1xuICAgICAgY2FzZSAnc2V0VGV4dCc6XG4gICAgICAgIHJldHVybiB7bm9kZTogcmVuYW1lTWV0aG9kQ2FsbChub2RlLCAnc2V0VmFsdWUnKX07XG4gICAgICBjYXNlICdsaXN0ZW5HbG9iYWwnOlxuICAgICAgICByZXR1cm4ge25vZGU6IHJlbmFtZU1ldGhvZENhbGwobm9kZSwgJ2xpc3RlbicpfTtcbiAgICAgIGNhc2UgJ3NlbGVjdFJvb3RFbGVtZW50JzpcbiAgICAgICAgcmV0dXJuIHtub2RlOiBtaWdyYXRlU2VsZWN0Um9vdEVsZW1lbnQobm9kZSl9O1xuICAgICAgY2FzZSAnc2V0RWxlbWVudENsYXNzJzpcbiAgICAgICAgcmV0dXJuIHtub2RlOiBtaWdyYXRlU2V0RWxlbWVudENsYXNzKG5vZGUpfTtcbiAgICAgIGNhc2UgJ3NldEVsZW1lbnRTdHlsZSc6XG4gICAgICAgIHJldHVybiB7bm9kZTogbWlncmF0ZVNldEVsZW1lbnRTdHlsZShub2RlLCB0eXBlQ2hlY2tlcil9O1xuICAgICAgY2FzZSAnaW52b2tlRWxlbWVudE1ldGhvZCc6XG4gICAgICAgIHJldHVybiB7bm9kZTogbWlncmF0ZUludm9rZUVsZW1lbnRNZXRob2Qobm9kZSl9O1xuICAgICAgY2FzZSAnc2V0QmluZGluZ0RlYnVnSW5mbyc6XG4gICAgICAgIHJldHVybiB7bm9kZTogbnVsbH07XG4gICAgICBjYXNlICdjcmVhdGVWaWV3Um9vdCc6XG4gICAgICAgIHJldHVybiB7bm9kZTogbWlncmF0ZUNyZWF0ZVZpZXdSb290KG5vZGUpfTtcbiAgICAgIGNhc2UgJ3NldEVsZW1lbnRBdHRyaWJ1dGUnOlxuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgIG5vZGU6IHN3aXRjaFRvSGVscGVyQ2FsbChub2RlLCBIZWxwZXJGdW5jdGlvbi5zZXRFbGVtZW50QXR0cmlidXRlLCBub2RlLmFyZ3VtZW50cyksXG4gICAgICAgICAgcmVxdWlyZWRIZWxwZXJzOiBbXG4gICAgICAgICAgICBIZWxwZXJGdW5jdGlvbi5hbnksIEhlbHBlckZ1bmN0aW9uLnNwbGl0TmFtZXNwYWNlLCBIZWxwZXJGdW5jdGlvbi5zZXRFbGVtZW50QXR0cmlidXRlXG4gICAgICAgICAgXVxuICAgICAgICB9O1xuICAgICAgY2FzZSAnY3JlYXRlRWxlbWVudCc6XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgbm9kZTogc3dpdGNoVG9IZWxwZXJDYWxsKG5vZGUsIEhlbHBlckZ1bmN0aW9uLmNyZWF0ZUVsZW1lbnQsIG5vZGUuYXJndW1lbnRzLnNsaWNlKDAsIDIpKSxcbiAgICAgICAgICByZXF1aXJlZEhlbHBlcnM6XG4gICAgICAgICAgICAgIFtIZWxwZXJGdW5jdGlvbi5hbnksIEhlbHBlckZ1bmN0aW9uLnNwbGl0TmFtZXNwYWNlLCBIZWxwZXJGdW5jdGlvbi5jcmVhdGVFbGVtZW50XVxuICAgICAgICB9O1xuICAgICAgY2FzZSAnY3JlYXRlVGV4dCc6XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgbm9kZTogc3dpdGNoVG9IZWxwZXJDYWxsKG5vZGUsIEhlbHBlckZ1bmN0aW9uLmNyZWF0ZVRleHQsIG5vZGUuYXJndW1lbnRzLnNsaWNlKDAsIDIpKSxcbiAgICAgICAgICByZXF1aXJlZEhlbHBlcnM6IFtIZWxwZXJGdW5jdGlvbi5hbnksIEhlbHBlckZ1bmN0aW9uLmNyZWF0ZVRleHRdXG4gICAgICAgIH07XG4gICAgICBjYXNlICdjcmVhdGVUZW1wbGF0ZUFuY2hvcic6XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgbm9kZTogc3dpdGNoVG9IZWxwZXJDYWxsKFxuICAgICAgICAgICAgICBub2RlLCBIZWxwZXJGdW5jdGlvbi5jcmVhdGVUZW1wbGF0ZUFuY2hvciwgbm9kZS5hcmd1bWVudHMuc2xpY2UoMCwgMSkpLFxuICAgICAgICAgIHJlcXVpcmVkSGVscGVyczogW0hlbHBlckZ1bmN0aW9uLmFueSwgSGVscGVyRnVuY3Rpb24uY3JlYXRlVGVtcGxhdGVBbmNob3JdXG4gICAgICAgIH07XG4gICAgICBjYXNlICdwcm9qZWN0Tm9kZXMnOlxuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgIG5vZGU6IHN3aXRjaFRvSGVscGVyQ2FsbChub2RlLCBIZWxwZXJGdW5jdGlvbi5wcm9qZWN0Tm9kZXMsIG5vZGUuYXJndW1lbnRzKSxcbiAgICAgICAgICByZXF1aXJlZEhlbHBlcnM6IFtIZWxwZXJGdW5jdGlvbi5hbnksIEhlbHBlckZ1bmN0aW9uLnByb2plY3ROb2Rlc11cbiAgICAgICAgfTtcbiAgICAgIGNhc2UgJ2FuaW1hdGUnOlxuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgIG5vZGU6IG1pZ3JhdGVBbmltYXRlQ2FsbCgpLFxuICAgICAgICAgIHJlcXVpcmVkSGVscGVyczogW0hlbHBlckZ1bmN0aW9uLmFueSwgSGVscGVyRnVuY3Rpb24uYW5pbWF0ZV1cbiAgICAgICAgfTtcbiAgICAgIGNhc2UgJ2Rlc3Ryb3lWaWV3JzpcbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICBub2RlOiBzd2l0Y2hUb0hlbHBlckNhbGwobm9kZSwgSGVscGVyRnVuY3Rpb24uZGVzdHJveVZpZXcsIFtub2RlLmFyZ3VtZW50c1sxXV0pLFxuICAgICAgICAgIHJlcXVpcmVkSGVscGVyczogW0hlbHBlckZ1bmN0aW9uLmFueSwgSGVscGVyRnVuY3Rpb24uZGVzdHJveVZpZXddXG4gICAgICAgIH07XG4gICAgICBjYXNlICdkZXRhY2hWaWV3JzpcbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICBub2RlOiBzd2l0Y2hUb0hlbHBlckNhbGwobm9kZSwgSGVscGVyRnVuY3Rpb24uZGV0YWNoVmlldywgW25vZGUuYXJndW1lbnRzWzBdXSksXG4gICAgICAgICAgcmVxdWlyZWRIZWxwZXJzOiBbSGVscGVyRnVuY3Rpb24uYW55LCBIZWxwZXJGdW5jdGlvbi5kZXRhY2hWaWV3XVxuICAgICAgICB9O1xuICAgICAgY2FzZSAnYXR0YWNoVmlld0FmdGVyJzpcbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICBub2RlOiBzd2l0Y2hUb0hlbHBlckNhbGwobm9kZSwgSGVscGVyRnVuY3Rpb24uYXR0YWNoVmlld0FmdGVyLCBub2RlLmFyZ3VtZW50cyksXG4gICAgICAgICAgcmVxdWlyZWRIZWxwZXJzOiBbSGVscGVyRnVuY3Rpb24uYW55LCBIZWxwZXJGdW5jdGlvbi5hdHRhY2hWaWV3QWZ0ZXJdXG4gICAgICAgIH07XG4gICAgfVxuICB9XG5cbiAgcmV0dXJuIHtub2RlfTtcbn1cblxuLyoqIENoZWNrcyB3aGV0aGVyIGEgbm9kZSBpcyBhIFByb3BlcnR5QWNjZXNzRXhwcmVzc2lvbi4gKi9cbmZ1bmN0aW9uIGlzUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbihub2RlOiB0cy5Ob2RlKTogbm9kZSBpcyBQcm9wZXJ0eUFjY2Vzc0NhbGxFeHByZXNzaW9uIHtcbiAgcmV0dXJuIHRzLmlzQ2FsbEV4cHJlc3Npb24obm9kZSkgJiYgdHMuaXNQcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb24obm9kZS5leHByZXNzaW9uKTtcbn1cblxuLyoqIFJlbmFtZXMgYSBtZXRob2QgY2FsbCB3aGlsZSBrZWVwaW5nIGFsbCBvZiB0aGUgcGFyYW1ldGVycyBpbiBwbGFjZS4gKi9cbmZ1bmN0aW9uIHJlbmFtZU1ldGhvZENhbGwobm9kZTogUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbiwgbmV3TmFtZTogc3RyaW5nKTogdHMuQ2FsbEV4cHJlc3Npb24ge1xuICBjb25zdCBuZXdFeHByZXNzaW9uID0gdHMudXBkYXRlUHJvcGVydHlBY2Nlc3MoXG4gICAgICBub2RlLmV4cHJlc3Npb24sIG5vZGUuZXhwcmVzc2lvbi5leHByZXNzaW9uLCB0cy5jcmVhdGVJZGVudGlmaWVyKG5ld05hbWUpKTtcblxuICByZXR1cm4gdHMudXBkYXRlQ2FsbChub2RlLCBuZXdFeHByZXNzaW9uLCBub2RlLnR5cGVBcmd1bWVudHMsIG5vZGUuYXJndW1lbnRzKTtcbn1cblxuLyoqXG4gKiBNaWdyYXRlcyBhIGBzZWxlY3RSb290RWxlbWVudGAgY2FsbCBieSByZW1vdmluZyB0aGUgbGFzdCBhcmd1bWVudCB3aGljaCBpcyBubyBsb25nZXIgc3VwcG9ydGVkLlxuICovXG5mdW5jdGlvbiBtaWdyYXRlU2VsZWN0Um9vdEVsZW1lbnQobm9kZTogdHMuQ2FsbEV4cHJlc3Npb24pOiB0cy5Ob2RlIHtcbiAgLy8gVGhlIG9ubHkgdGhpbmcgd2UgbmVlZCB0byBkbyBpcyB0byBkcm9wIHRoZSBsYXN0IGFyZ3VtZW50XG4gIC8vIChgZGVidWdJbmZvYCksIGlmIHRoZSBjb25zdW1lciB3YXMgcGFzc2luZyBpdCBpbi5cbiAgaWYgKG5vZGUuYXJndW1lbnRzLmxlbmd0aCA+IDEpIHtcbiAgICByZXR1cm4gdHMudXBkYXRlQ2FsbChub2RlLCBub2RlLmV4cHJlc3Npb24sIG5vZGUudHlwZUFyZ3VtZW50cywgW25vZGUuYXJndW1lbnRzWzBdXSk7XG4gIH1cblxuICByZXR1cm4gbm9kZTtcbn1cblxuLyoqXG4gKiBNaWdyYXRlcyBhIGNhbGwgdG8gYHNldEVsZW1lbnRDbGFzc2AgZWl0aGVyIHRvIGEgY2FsbCB0byBgYWRkQ2xhc3NgIG9yIGByZW1vdmVDbGFzc2AsIG9yXG4gKiB0byBhbiBleHByZXNzaW9uIGxpa2UgYGlzQWRkID8gYWRkQ2xhc3MoZWwsIGNsYXNzTmFtZSkgOiByZW1vdmVDbGFzcyhlbCwgY2xhc3NOYW1lKWAuXG4gKi9cbmZ1bmN0aW9uIG1pZ3JhdGVTZXRFbGVtZW50Q2xhc3Mobm9kZTogUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbik6IHRzLk5vZGUge1xuICAvLyBDbG9uZSBzbyB3ZSBkb24ndCBtdXRhdGUgYnkgYWNjaWRlbnQuIE5vdGUgdGhhdCB3ZSBhc3N1bWUgdGhhdFxuICAvLyB0aGUgdXNlcidzIGNvZGUgaXMgcHJvdmlkaW5nIGFsbCB0aHJlZSByZXF1aXJlZCBhcmd1bWVudHMuXG4gIGNvbnN0IG91dHB1dE1ldGhvZEFyZ3MgPSBub2RlLmFyZ3VtZW50cy5zbGljZSgpO1xuICBjb25zdCBpc0FkZEFyZ3VtZW50ID0gb3V0cHV0TWV0aG9kQXJncy5wb3AoKSE7XG4gIGNvbnN0IGNyZWF0ZVJlbmRlcmVyQ2FsbCA9IChpc0FkZDogYm9vbGVhbikgPT4ge1xuICAgIGNvbnN0IGlubmVyRXhwcmVzc2lvbiA9IG5vZGUuZXhwcmVzc2lvbi5leHByZXNzaW9uO1xuICAgIGNvbnN0IHRvcEV4cHJlc3Npb24gPVxuICAgICAgICB0cy5jcmVhdGVQcm9wZXJ0eUFjY2Vzcyhpbm5lckV4cHJlc3Npb24sIGlzQWRkID8gJ2FkZENsYXNzJyA6ICdyZW1vdmVDbGFzcycpO1xuICAgIHJldHVybiB0cy5jcmVhdGVDYWxsKHRvcEV4cHJlc3Npb24sIFtdLCBub2RlLmFyZ3VtZW50cy5zbGljZSgwLCAyKSk7XG4gIH07XG5cbiAgLy8gSWYgdGhlIGNhbGwgaGFzIHRoZSBgaXNBZGRgIGFyZ3VtZW50IGFzIGEgbGl0ZXJhbCBib29sZWFuLCB3ZSBjYW4gbWFwIGl0IGRpcmVjdGx5IHRvXG4gIC8vIGBhZGRDbGFzc2Agb3IgYHJlbW92ZUNsYXNzYC4gTm90ZSB0aGF0IHdlIGNhbid0IHVzZSB0aGUgdHlwZSBjaGVja2VyIGhlcmUsIGJlY2F1c2UgaXRcbiAgLy8gd29uJ3QgdGVsbCB1cyB3aGV0aGVyIHRoZSB2YWx1ZSByZXNvbHZlcyB0byB0cnVlIG9yIGZhbHNlLlxuICBpZiAoaXNBZGRBcmd1bWVudC5raW5kID09PSB0cy5TeW50YXhLaW5kLlRydWVLZXl3b3JkIHx8XG4gICAgICBpc0FkZEFyZ3VtZW50LmtpbmQgPT09IHRzLlN5bnRheEtpbmQuRmFsc2VLZXl3b3JkKSB7XG4gICAgcmV0dXJuIGNyZWF0ZVJlbmRlcmVyQ2FsbChpc0FkZEFyZ3VtZW50LmtpbmQgPT09IHRzLlN5bnRheEtpbmQuVHJ1ZUtleXdvcmQpO1xuICB9XG5cbiAgLy8gT3RoZXJ3aXNlIGNyZWF0ZSBhIHRlcm5hcnkgb24gdGhlIHZhcmlhYmxlLlxuICByZXR1cm4gdHMuY3JlYXRlQ29uZGl0aW9uYWwoaXNBZGRBcmd1bWVudCwgY3JlYXRlUmVuZGVyZXJDYWxsKHRydWUpLCBjcmVhdGVSZW5kZXJlckNhbGwoZmFsc2UpKTtcbn1cblxuLyoqXG4gKiBNaWdyYXRlcyBhIGNhbGwgdG8gYHNldEVsZW1lbnRTdHlsZWAgY2FsbCBlaXRoZXIgdG8gYSBjYWxsIHRvXG4gKiBgc2V0U3R5bGVgIG9yIGByZW1vdmVTdHlsZWAuIG9yIHRvIGFuIGV4cHJlc3Npb24gbGlrZVxuICogYHZhbHVlID09IG51bGwgPyByZW1vdmVTdHlsZShlbCwga2V5KSA6IHNldFN0eWxlKGVsLCBrZXksIHZhbHVlKWAuXG4gKi9cbmZ1bmN0aW9uIG1pZ3JhdGVTZXRFbGVtZW50U3R5bGUoXG4gICAgbm9kZTogUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbiwgdHlwZUNoZWNrZXI6IHRzLlR5cGVDaGVja2VyKTogdHMuTm9kZSB7XG4gIGNvbnN0IGFyZ3MgPSBub2RlLmFyZ3VtZW50cztcbiAgY29uc3QgYWRkTWV0aG9kTmFtZSA9ICdzZXRTdHlsZSc7XG4gIGNvbnN0IHJlbW92ZU1ldGhvZE5hbWUgPSAncmVtb3ZlU3R5bGUnO1xuICBjb25zdCBsYXN0QXJnVHlwZSA9IGFyZ3NbMl0gP1xuICAgICAgdHlwZUNoZWNrZXIudHlwZVRvU3RyaW5nKFxuICAgICAgICAgIHR5cGVDaGVja2VyLmdldFR5cGVBdExvY2F0aW9uKGFyZ3NbMl0pLCBub2RlLCB0cy5UeXBlRm9ybWF0RmxhZ3MuQWRkVW5kZWZpbmVkKSA6XG4gICAgICBudWxsO1xuXG4gIC8vIE5vdGUgdGhhdCBmb3IgYSBsaXRlcmFsIG51bGwsIFRTIGNvbnNpZGVycyBpdCBhIGBOdWxsS2V5d29yZGAsXG4gIC8vIHdoZXJlYXMgYSBsaXRlcmFsIGB1bmRlZmluZWRgIGlzIGp1c3QgYW4gSWRlbnRpZmllci5cbiAgaWYgKGFyZ3MubGVuZ3RoID09PSAyIHx8IGxhc3RBcmdUeXBlID09PSAnbnVsbCcgfHwgbGFzdEFyZ1R5cGUgPT09ICd1bmRlZmluZWQnKSB7XG4gICAgLy8gSWYgd2UndmUgZ290IGEgY2FsbCB3aXRoIHR3byBhcmd1bWVudHMsIG9yIG9uZSB3aXRoIHRocmVlIGFyZ3VtZW50cyB3aGVyZSB0aGUgbGFzdCBvbmUgaXNcbiAgICAvLyBgdW5kZWZpbmVkYCBvciBgbnVsbGAsIHdlIGNhbiBzYWZlbHkgc3dpdGNoIHRvIGEgYHJlbW92ZVN0eWxlYCBjYWxsLlxuICAgIGNvbnN0IGlubmVyRXhwcmVzc2lvbiA9IG5vZGUuZXhwcmVzc2lvbi5leHByZXNzaW9uO1xuICAgIGNvbnN0IHRvcEV4cHJlc3Npb24gPSB0cy5jcmVhdGVQcm9wZXJ0eUFjY2Vzcyhpbm5lckV4cHJlc3Npb24sIHJlbW92ZU1ldGhvZE5hbWUpO1xuICAgIHJldHVybiB0cy5jcmVhdGVDYWxsKHRvcEV4cHJlc3Npb24sIFtdLCBhcmdzLnNsaWNlKDAsIDIpKTtcbiAgfSBlbHNlIGlmIChhcmdzLmxlbmd0aCA9PT0gMykge1xuICAgIC8vIFdlIG5lZWQgdGhlIGNoZWNrcyBmb3Igc3RyaW5nIGxpdGVyYWxzLCBiZWNhdXNlIHRoZSB0eXBlIG9mIHNvbWV0aGluZ1xuICAgIC8vIGxpa2UgYFwiYmx1ZVwiYCBpcyB0aGUgbGl0ZXJhbCBgYmx1ZWAsIG5vdCBgc3RyaW5nYC5cbiAgICBpZiAobGFzdEFyZ1R5cGUgPT09ICdzdHJpbmcnIHx8IGxhc3RBcmdUeXBlID09PSAnbnVtYmVyJyB8fCB0cy5pc1N0cmluZ0xpdGVyYWwoYXJnc1syXSkgfHxcbiAgICAgICAgdHMuaXNOb1N1YnN0aXR1dGlvblRlbXBsYXRlTGl0ZXJhbChhcmdzWzJdKSB8fCB0cy5pc051bWVyaWNMaXRlcmFsKGFyZ3NbMl0pKSB7XG4gICAgICAvLyBJZiB3ZSd2ZSBnb3QgdGhyZWUgYXJndW1lbnRzIGFuZCB0aGUgbGFzdCBvbmUgaXMgYSBzdHJpbmcgbGl0ZXJhbCBvciBhIG51bWJlciwgd2VcbiAgICAgIC8vIGNhbiBzYWZlbHkgcmVuYW1lIHRvIGBzZXRTdHlsZWAuXG4gICAgICByZXR1cm4gcmVuYW1lTWV0aG9kQ2FsbChub2RlLCBhZGRNZXRob2ROYW1lKTtcbiAgICB9IGVsc2Uge1xuICAgICAgLy8gT3RoZXJ3aXNlIG1pZ3JhdGUgdG8gYSB0ZXJuYXJ5IHRoYXQgbG9va3MgbGlrZTpcbiAgICAgIC8vIGB2YWx1ZSA9PSBudWxsID8gcmVtb3ZlU3R5bGUoZWwsIGtleSkgOiBzZXRTdHlsZShlbCwga2V5LCB2YWx1ZSlgXG4gICAgICBjb25zdCBjb25kaXRpb24gPSB0cy5jcmVhdGVCaW5hcnkoYXJnc1syXSwgdHMuU3ludGF4S2luZC5FcXVhbHNFcXVhbHNUb2tlbiwgdHMuY3JlYXRlTnVsbCgpKTtcbiAgICAgIGNvbnN0IHdoZW5OdWxsQ2FsbCA9IHJlbmFtZU1ldGhvZENhbGwoXG4gICAgICAgICAgdHMuY3JlYXRlQ2FsbChub2RlLmV4cHJlc3Npb24sIFtdLCBhcmdzLnNsaWNlKDAsIDIpKSBhcyBQcm9wZXJ0eUFjY2Vzc0NhbGxFeHByZXNzaW9uLFxuICAgICAgICAgIHJlbW92ZU1ldGhvZE5hbWUpO1xuICAgICAgcmV0dXJuIHRzLmNyZWF0ZUNvbmRpdGlvbmFsKGNvbmRpdGlvbiwgd2hlbk51bGxDYWxsLCByZW5hbWVNZXRob2RDYWxsKG5vZGUsIGFkZE1ldGhvZE5hbWUpKTtcbiAgICB9XG4gIH1cblxuICByZXR1cm4gbm9kZTtcbn1cblxuLyoqXG4gKiBNaWdyYXRlcyBhIGNhbGwgdG8gYGludm9rZUVsZW1lbnRNZXRob2QodGFyZ2V0LCBtZXRob2QsIFthcmcxLCBhcmcyXSlgIGVpdGhlciB0b1xuICogYHRhcmdldC5tZXRob2QoYXJnMSwgYXJnMilgIG9yIGAodGFyZ2V0IGFzIGFueSlbbWV0aG9kXS5hcHBseSh0YXJnZXQsIFthcmcxLCBhcmcyXSlgLlxuICovXG5mdW5jdGlvbiBtaWdyYXRlSW52b2tlRWxlbWVudE1ldGhvZChub2RlOiB0cy5DYWxsRXhwcmVzc2lvbik6IHRzLk5vZGUge1xuICBjb25zdCBbdGFyZ2V0LCBuYW1lLCBhcmdzXSA9IG5vZGUuYXJndW1lbnRzO1xuICBjb25zdCBpc05hbWVTdGF0aWMgPSB0cy5pc1N0cmluZ0xpdGVyYWwobmFtZSkgfHwgdHMuaXNOb1N1YnN0aXR1dGlvblRlbXBsYXRlTGl0ZXJhbChuYW1lKTtcbiAgY29uc3QgaXNBcmdzU3RhdGljID0gIWFyZ3MgfHwgdHMuaXNBcnJheUxpdGVyYWxFeHByZXNzaW9uKGFyZ3MpO1xuXG4gIGlmIChpc05hbWVTdGF0aWMgJiYgaXNBcmdzU3RhdGljKSB7XG4gICAgLy8gSWYgdGhlIG5hbWUgaXMgYSBzdGF0aWMgc3RyaW5nIGFuZCB0aGUgYXJndW1lbnRzIGFyZSBhbiBhcnJheSBsaXRlcmFsLFxuICAgIC8vIHdlIGNhbiBzYWZlbHkgY29udmVydCB0aGUgbm9kZSBpbnRvIGEgY2FsbCBleHByZXNzaW9uLlxuICAgIGNvbnN0IGV4cHJlc3Npb24gPSB0cy5jcmVhdGVQcm9wZXJ0eUFjY2VzcyhcbiAgICAgICAgdGFyZ2V0LCAobmFtZSBhcyB0cy5TdHJpbmdMaXRlcmFsIHwgdHMuTm9TdWJzdGl0dXRpb25UZW1wbGF0ZUxpdGVyYWwpLnRleHQpO1xuICAgIGNvbnN0IGNhbGxBcmd1bWVudHMgPSBhcmdzID8gKGFyZ3MgYXMgdHMuQXJyYXlMaXRlcmFsRXhwcmVzc2lvbikuZWxlbWVudHMgOiBbXTtcbiAgICByZXR1cm4gdHMuY3JlYXRlQ2FsbChleHByZXNzaW9uLCBbXSwgY2FsbEFyZ3VtZW50cyk7XG4gIH0gZWxzZSB7XG4gICAgLy8gT3RoZXJ3aXNlIGNyZWF0ZSBhbiBleHByZXNzaW9uIGluIHRoZSBmb3JtIG9mIGAodGFyZ2V0IGFzIGFueSlbbmFtZV0uYXBwbHkodGFyZ2V0LCBhcmdzKWAuXG4gICAgY29uc3QgYXNFeHByZXNzaW9uID0gdHMuY3JlYXRlUGFyZW4oXG4gICAgICAgIHRzLmNyZWF0ZUFzRXhwcmVzc2lvbih0YXJnZXQsIHRzLmNyZWF0ZUtleXdvcmRUeXBlTm9kZSh0cy5TeW50YXhLaW5kLkFueUtleXdvcmQpKSk7XG4gICAgY29uc3QgZWxlbWVudEFjY2VzcyA9IHRzLmNyZWF0ZUVsZW1lbnRBY2Nlc3MoYXNFeHByZXNzaW9uLCBuYW1lKTtcbiAgICBjb25zdCBhcHBseUV4cHJlc3Npb24gPSB0cy5jcmVhdGVQcm9wZXJ0eUFjY2VzcyhlbGVtZW50QWNjZXNzLCAnYXBwbHknKTtcbiAgICByZXR1cm4gdHMuY3JlYXRlQ2FsbChhcHBseUV4cHJlc3Npb24sIFtdLCBhcmdzID8gW3RhcmdldCwgYXJnc10gOiBbdGFyZ2V0XSk7XG4gIH1cbn1cblxuLyoqIE1pZ3JhdGVzIGEgY2FsbCB0byBgY3JlYXRlVmlld1Jvb3RgIHRvIHdoYXRldmVyIG5vZGUgd2FzIHBhc3NlZCBpbiBhcyB0aGUgZmlyc3QgYXJndW1lbnQuICovXG5mdW5jdGlvbiBtaWdyYXRlQ3JlYXRlVmlld1Jvb3Qobm9kZTogdHMuQ2FsbEV4cHJlc3Npb24pOiB0cy5Ob2RlIHtcbiAgcmV0dXJuIG5vZGUuYXJndW1lbnRzWzBdO1xufVxuXG4vKiogTWlncmF0ZXMgYSBjYWxsIHRvIGBtaWdyYXRlYCBhIGRpcmVjdCBjYWxsIHRvIHRoZSBoZWxwZXIuICovXG5mdW5jdGlvbiBtaWdyYXRlQW5pbWF0ZUNhbGwoKSB7XG4gIHJldHVybiB0cy5jcmVhdGVDYWxsKHRzLmNyZWF0ZUlkZW50aWZpZXIoSGVscGVyRnVuY3Rpb24uYW5pbWF0ZSksIFtdLCBbXSk7XG59XG5cbi8qKlxuICogU3dpdGNoZXMgb3V0IGEgY2FsbCB0byB0aGUgYFJlbmRlcmVyYCB0byBhIGNhbGwgdG8gb25lIG9mIG91ciBoZWxwZXIgZnVuY3Rpb25zLlxuICogTW9zdCBvZiB0aGUgaGVscGVycyBhY2NlcHQgYW4gaW5zdGFuY2Ugb2YgYFJlbmRlcmVyMmAgYXMgdGhlIGZpcnN0IGFyZ3VtZW50IGFuZCBhbGxcbiAqIHN1YnNlcXVlbnQgYXJndW1lbnRzIGRpZmZlci5cbiAqIEBwYXJhbSBub2RlIE5vZGUgb2YgdGhlIG9yaWdpbmFsIG1ldGhvZCBjYWxsLlxuICogQHBhcmFtIGhlbHBlciBOYW1lIG9mIHRoZSBoZWxwZXIgd2l0aCB3aGljaCB0byByZXBsYWNlIHRoZSBvcmlnaW5hbCBjYWxsLlxuICogQHBhcmFtIGFyZ3MgQXJndW1lbnRzIHRoYXQgc2hvdWxkIGJlIHBhc3NlZCBpbnRvIHRoZSBoZWxwZXIgYWZ0ZXIgdGhlIHJlbmRlcmVyIGFyZ3VtZW50LlxuICovXG5mdW5jdGlvbiBzd2l0Y2hUb0hlbHBlckNhbGwoXG4gICAgbm9kZTogUHJvcGVydHlBY2Nlc3NDYWxsRXhwcmVzc2lvbiwgaGVscGVyOiBIZWxwZXJGdW5jdGlvbixcbiAgICBhcmdzOiB0cy5FeHByZXNzaW9uW118dHMuTm9kZUFycmF5PHRzLkV4cHJlc3Npb24+KTogdHMuTm9kZSB7XG4gIHJldHVybiB0cy5jcmVhdGVDYWxsKHRzLmNyZWF0ZUlkZW50aWZpZXIoaGVscGVyKSwgW10sIFtub2RlLmV4cHJlc3Npb24uZXhwcmVzc2lvbiwgLi4uYXJnc10pO1xufVxuIl19