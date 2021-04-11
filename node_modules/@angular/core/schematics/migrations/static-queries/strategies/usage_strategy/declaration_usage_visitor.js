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
        define("@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/declaration_usage_visitor", ["require", "exports", "typescript", "@angular/core/schematics/utils/typescript/functions", "@angular/core/schematics/utils/typescript/property_name"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DeclarationUsageVisitor = exports.ResolvedUsage = void 0;
    const ts = require("typescript");
    const functions_1 = require("@angular/core/schematics/utils/typescript/functions");
    const property_name_1 = require("@angular/core/schematics/utils/typescript/property_name");
    var ResolvedUsage;
    (function (ResolvedUsage) {
        ResolvedUsage[ResolvedUsage["SYNCHRONOUS"] = 0] = "SYNCHRONOUS";
        ResolvedUsage[ResolvedUsage["ASYNCHRONOUS"] = 1] = "ASYNCHRONOUS";
        ResolvedUsage[ResolvedUsage["AMBIGUOUS"] = 2] = "AMBIGUOUS";
    })(ResolvedUsage = exports.ResolvedUsage || (exports.ResolvedUsage = {}));
    /**
     * List of TypeScript syntax tokens that can be used within a binary expression as
     * compound assignment. These imply a read and write of the left-side expression.
     */
    const BINARY_COMPOUND_TOKENS = [
        ts.SyntaxKind.CaretEqualsToken,
        ts.SyntaxKind.AsteriskEqualsToken,
        ts.SyntaxKind.AmpersandEqualsToken,
        ts.SyntaxKind.BarEqualsToken,
        ts.SyntaxKind.AsteriskAsteriskEqualsToken,
        ts.SyntaxKind.PlusEqualsToken,
        ts.SyntaxKind.MinusEqualsToken,
        ts.SyntaxKind.SlashEqualsToken,
    ];
    /**
     * List of known asynchronous external call expressions which aren't analyzable
     * but are guaranteed to not execute the passed argument synchronously.
     */
    const ASYNC_EXTERNAL_CALLS = [
        { parent: ['Promise'], name: 'then' },
        { parent: ['Promise'], name: 'catch' },
        { parent: [null, 'Window'], name: 'requestAnimationFrame' },
        { parent: [null, 'Window'], name: 'setTimeout' },
        { parent: [null, 'Window'], name: 'setInterval' },
        { parent: ['*'], name: 'addEventListener' },
    ];
    /**
     * Class that can be used to determine if a given TypeScript node is used within
     * other given TypeScript nodes. This is achieved by walking through all children
     * of the given node and checking for usages of the given declaration. The visitor
     * also handles potential control flow changes caused by call/new expressions.
     */
    class DeclarationUsageVisitor {
        constructor(declaration, typeChecker, baseContext = new Map()) {
            this.declaration = declaration;
            this.typeChecker = typeChecker;
            this.baseContext = baseContext;
            /** Set of visited symbols that caused a jump in control flow. */
            this.visitedJumpExprNodes = new Set();
            /**
             * Queue of nodes that need to be checked for declaration usage and
             * are guaranteed to be executed synchronously.
             */
            this.nodeQueue = [];
            /**
             * Nodes which need to be checked for declaration usage but aren't
             * guaranteed to execute synchronously.
             */
            this.ambiguousNodeQueue = [];
            /**
             * Function context that holds the TypeScript node values for all parameters
             * of the currently analyzed function block.
             */
            this.context = new Map();
        }
        isReferringToSymbol(node) {
            const symbol = this.typeChecker.getSymbolAtLocation(node);
            return !!symbol && symbol.valueDeclaration === this.declaration;
        }
        addJumpExpressionToQueue(callExpression) {
            const node = functions_1.unwrapExpression(callExpression.expression);
            // In case the given expression is already referring to a function-like declaration,
            // we don't need to resolve the symbol of the expression as the jump expression is
            // defined inline and we can just add the given node to the queue.
            if (functions_1.isFunctionLikeDeclaration(node) && node.body) {
                this.nodeQueue.push(node.body);
                return;
            }
            const callExprSymbol = this._getDeclarationSymbolOfNode(node);
            if (!callExprSymbol || !callExprSymbol.valueDeclaration) {
                this.peekIntoJumpExpression(callExpression);
                return;
            }
            const expressionDecl = this._resolveNodeFromContext(callExprSymbol.valueDeclaration);
            // Note that we should not add previously visited symbols to the queue as
            // this could cause cycles.
            if (!functions_1.isFunctionLikeDeclaration(expressionDecl) ||
                this.visitedJumpExprNodes.has(expressionDecl) || !expressionDecl.body) {
                this.peekIntoJumpExpression(callExpression);
                return;
            }
            // Update the context for the new jump expression and its specified arguments.
            this._updateContext(callExpression.arguments, expressionDecl.parameters);
            this.visitedJumpExprNodes.add(expressionDecl);
            this.nodeQueue.push(expressionDecl.body);
        }
        addNewExpressionToQueue(node) {
            const newExprSymbol = this._getDeclarationSymbolOfNode(functions_1.unwrapExpression(node.expression));
            // Only handle new expressions which resolve to classes. Technically "new" could
            // also call void functions or objects with a constructor signature. Also note that
            // we should not visit already visited symbols as this could cause cycles.
            if (!newExprSymbol || !newExprSymbol.valueDeclaration ||
                !ts.isClassDeclaration(newExprSymbol.valueDeclaration)) {
                this.peekIntoJumpExpression(node);
                return;
            }
            const targetConstructor = newExprSymbol.valueDeclaration.members.find(ts.isConstructorDeclaration);
            if (targetConstructor && targetConstructor.body &&
                !this.visitedJumpExprNodes.has(targetConstructor)) {
                // Update the context for the new expression and its specified constructor
                // parameters if arguments are passed to the class constructor.
                if (node.arguments) {
                    this._updateContext(node.arguments, targetConstructor.parameters);
                }
                this.visitedJumpExprNodes.add(targetConstructor);
                this.nodeQueue.push(targetConstructor.body);
            }
            else {
                this.peekIntoJumpExpression(node);
            }
        }
        visitPropertyAccessors(node, checkSetter, checkGetter) {
            const propertySymbol = this._getPropertyAccessSymbol(node);
            if (!propertySymbol || !propertySymbol.declarations.length ||
                (propertySymbol.getFlags() & ts.SymbolFlags.Accessor) === 0) {
                return;
            }
            // Since we checked the symbol flags and the symbol is describing an accessor, the
            // declarations are guaranteed to only contain the getters and setters.
            const accessors = propertySymbol.declarations;
            accessors
                .filter(d => (checkSetter && ts.isSetAccessor(d) || checkGetter && ts.isGetAccessor(d)) &&
                d.body && !this.visitedJumpExprNodes.has(d))
                .forEach(d => {
                this.visitedJumpExprNodes.add(d);
                this.nodeQueue.push(d.body);
            });
        }
        visitBinaryExpression(node) {
            const leftExpr = functions_1.unwrapExpression(node.left);
            if (!ts.isPropertyAccessExpression(leftExpr)) {
                return false;
            }
            if (BINARY_COMPOUND_TOKENS.indexOf(node.operatorToken.kind) !== -1) {
                // Compound assignments always cause the getter and setter to be called.
                // Therefore we need to check the setter and getter of the property access.
                this.visitPropertyAccessors(leftExpr, /* setter */ true, /* getter */ true);
            }
            else if (node.operatorToken.kind === ts.SyntaxKind.EqualsToken) {
                // Value assignments using the equals token only cause the "setter" to be called.
                // Therefore we need to analyze the setter declaration of the property access.
                this.visitPropertyAccessors(leftExpr, /* setter */ true, /* getter */ false);
            }
            else {
                // If the binary expression is not an assignment, it's a simple property read and
                // we need to check the getter declaration if present.
                this.visitPropertyAccessors(leftExpr, /* setter */ false, /* getter */ true);
            }
            return true;
        }
        getResolvedNodeUsage(searchNode) {
            this.nodeQueue = [searchNode];
            this.visitedJumpExprNodes.clear();
            this.context.clear();
            // Copy base context values into the current function block context. The
            // base context is useful if nodes need to be mapped to other nodes. e.g.
            // abstract super class methods are mapped to their implementation node of
            // the derived class.
            this.baseContext.forEach((value, key) => this.context.set(key, value));
            return this.isSynchronouslyUsedInNode(searchNode);
        }
        isSynchronouslyUsedInNode(searchNode) {
            this.ambiguousNodeQueue = [];
            while (this.nodeQueue.length) {
                const node = this.nodeQueue.shift();
                if (ts.isIdentifier(node) && this.isReferringToSymbol(node)) {
                    return ResolvedUsage.SYNCHRONOUS;
                }
                // Handle call expressions within TypeScript nodes that cause a jump in control
                // flow. We resolve the call expression value declaration and add it to the node queue.
                if (ts.isCallExpression(node)) {
                    this.addJumpExpressionToQueue(node);
                }
                // Handle new expressions that cause a jump in control flow. We resolve the
                // constructor declaration of the target class and add it to the node queue.
                if (ts.isNewExpression(node)) {
                    this.addNewExpressionToQueue(node);
                }
                // We also need to handle binary expressions where a value can be either assigned to
                // the property, or a value is read from a property expression. Depending on the
                // binary expression operator, setters or getters need to be analyzed.
                if (ts.isBinaryExpression(node)) {
                    // In case the binary expression contained a property expression on the left side, we
                    // don't want to continue visiting this property expression on its own. This is necessary
                    // because visiting the expression on its own causes a loss of context. e.g. property
                    // access expressions *do not* always cause a value read (e.g. property assignments)
                    if (this.visitBinaryExpression(node)) {
                        this.nodeQueue.push(node.right);
                        continue;
                    }
                }
                // Handle property access expressions. Property expressions which are part of binary
                // expressions won't be added to the node queue, so these access expressions are
                // guaranteed to be "read" accesses and we need to check the "getter" declaration.
                if (ts.isPropertyAccessExpression(node)) {
                    this.visitPropertyAccessors(node, /* setter */ false, /* getter */ true);
                }
                // Do not visit nodes that declare a block of statements but are not executed
                // synchronously (e.g. function declarations). We only want to check TypeScript
                // nodes which are synchronously executed in the control flow.
                if (!functions_1.isFunctionLikeDeclaration(node)) {
                    this.nodeQueue.push(...node.getChildren());
                }
            }
            if (this.ambiguousNodeQueue.length) {
                // Update the node queue to all stored ambiguous nodes. These nodes are not
                // guaranteed to be executed and therefore in case of a synchronous usage
                // within one of those nodes, the resolved usage is ambiguous.
                this.nodeQueue = this.ambiguousNodeQueue;
                const usage = this.isSynchronouslyUsedInNode(searchNode);
                return usage === ResolvedUsage.SYNCHRONOUS ? ResolvedUsage.AMBIGUOUS : usage;
            }
            return ResolvedUsage.ASYNCHRONOUS;
        }
        /**
         * Peeks into the given jump expression by adding all function like declarations
         * which are referenced in the jump expression arguments to the ambiguous node
         * queue. These arguments could technically access the given declaration but it's
         * not guaranteed that the jump expression is executed. In that case the resolved
         * usage is ambiguous.
         */
        peekIntoJumpExpression(jumpExp) {
            if (!jumpExp.arguments) {
                return;
            }
            // For some call expressions we don't want to add the arguments to the
            // ambiguous node queue. e.g. "setTimeout" is not analyzable but is
            // guaranteed to execute its argument asynchronously. We handle a subset
            // of these call expressions by having a hardcoded list of some.
            if (ts.isCallExpression(jumpExp)) {
                const symbol = this._getDeclarationSymbolOfNode(jumpExp.expression);
                if (symbol && symbol.valueDeclaration) {
                    const parentNode = symbol.valueDeclaration.parent;
                    if (parentNode && (ts.isInterfaceDeclaration(parentNode) || ts.isSourceFile(parentNode)) &&
                        (ts.isMethodSignature(symbol.valueDeclaration) ||
                            ts.isFunctionDeclaration(symbol.valueDeclaration)) &&
                        symbol.valueDeclaration.name) {
                        const parentName = ts.isInterfaceDeclaration(parentNode) ? parentNode.name.text : null;
                        const callName = property_name_1.getPropertyNameText(symbol.valueDeclaration.name);
                        if (ASYNC_EXTERNAL_CALLS.some(c => (c.name === callName &&
                            (c.parent.indexOf(parentName) !== -1 || c.parent.indexOf('*') !== -1)))) {
                            return;
                        }
                    }
                }
            }
            jumpExp.arguments.forEach((node) => {
                node = this._resolveDeclarationOfNode(node);
                if (ts.isVariableDeclaration(node) && node.initializer) {
                    node = node.initializer;
                }
                if (functions_1.isFunctionLikeDeclaration(node) && !!node.body) {
                    this.ambiguousNodeQueue.push(node.body);
                }
            });
        }
        /**
         * Resolves a given node from the context. In case the node is not mapped in
         * the context, the original node is returned.
         */
        _resolveNodeFromContext(node) {
            if (this.context.has(node)) {
                return this.context.get(node);
            }
            return node;
        }
        /**
         * Updates the context to reflect the newly set parameter values. This allows future
         * references to function parameters to be resolved to the actual node through the context.
         */
        _updateContext(callArgs, parameters) {
            parameters.forEach((parameter, index) => {
                let argumentNode = callArgs[index];
                if (!argumentNode) {
                    if (!parameter.initializer) {
                        return;
                    }
                    // Argument can be undefined in case the function parameter has a default
                    // value. In that case we want to store the parameter default value in the context.
                    argumentNode = parameter.initializer;
                }
                if (ts.isIdentifier(argumentNode)) {
                    this.context.set(parameter, this._resolveDeclarationOfNode(argumentNode));
                }
                else {
                    this.context.set(parameter, argumentNode);
                }
            });
        }
        /**
         * Resolves the declaration of a given TypeScript node. For example an identifier can
         * refer to a function parameter. This parameter can then be resolved through the
         * function context.
         */
        _resolveDeclarationOfNode(node) {
            const symbol = this._getDeclarationSymbolOfNode(node);
            if (!symbol || !symbol.valueDeclaration) {
                return node;
            }
            return this._resolveNodeFromContext(symbol.valueDeclaration);
        }
        /**
         * Gets the declaration symbol of a given TypeScript node. Resolves aliased
         * symbols to the symbol containing the value declaration.
         */
        _getDeclarationSymbolOfNode(node) {
            let symbol = this.typeChecker.getSymbolAtLocation(node);
            if (!symbol) {
                return null;
            }
            // Resolve the symbol to it's original declaration symbol.
            while (symbol.flags & ts.SymbolFlags.Alias) {
                symbol = this.typeChecker.getAliasedSymbol(symbol);
            }
            return symbol;
        }
        /** Gets the symbol of the given property access expression. */
        _getPropertyAccessSymbol(node) {
            let propertySymbol = this._getDeclarationSymbolOfNode(node.name);
            if (!propertySymbol || !propertySymbol.valueDeclaration) {
                return null;
            }
            if (!this.context.has(propertySymbol.valueDeclaration)) {
                return propertySymbol;
            }
            // In case the context has the value declaration of the given property access
            // name identifier, we need to replace the "propertySymbol" with the symbol
            // referring to the resolved symbol based on the context. e.g. abstract properties
            // can ultimately resolve into an accessor declaration based on the implementation.
            const contextNode = this._resolveNodeFromContext(propertySymbol.valueDeclaration);
            if (!ts.isAccessor(contextNode)) {
                return null;
            }
            // Resolve the symbol referring to the "accessor" using the name identifier
            // of the accessor declaration.
            return this._getDeclarationSymbolOfNode(contextNode.name);
        }
    }
    exports.DeclarationUsageVisitor = DeclarationUsageVisitor;
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGVjbGFyYXRpb25fdXNhZ2VfdmlzaXRvci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc2NoZW1hdGljcy9taWdyYXRpb25zL3N0YXRpYy1xdWVyaWVzL3N0cmF0ZWdpZXMvdXNhZ2Vfc3RyYXRlZ3kvZGVjbGFyYXRpb25fdXNhZ2VfdmlzaXRvci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7Ozs7SUFFSCxpQ0FBaUM7SUFDakMsbUZBQW1HO0lBQ25HLDJGQUErRTtJQUkvRSxJQUFZLGFBSVg7SUFKRCxXQUFZLGFBQWE7UUFDdkIsK0RBQVcsQ0FBQTtRQUNYLGlFQUFZLENBQUE7UUFDWiwyREFBUyxDQUFBO0lBQ1gsQ0FBQyxFQUpXLGFBQWEsR0FBYixxQkFBYSxLQUFiLHFCQUFhLFFBSXhCO0lBRUQ7OztPQUdHO0lBQ0gsTUFBTSxzQkFBc0IsR0FBRztRQUM3QixFQUFFLENBQUMsVUFBVSxDQUFDLGdCQUFnQjtRQUM5QixFQUFFLENBQUMsVUFBVSxDQUFDLG1CQUFtQjtRQUNqQyxFQUFFLENBQUMsVUFBVSxDQUFDLG9CQUFvQjtRQUNsQyxFQUFFLENBQUMsVUFBVSxDQUFDLGNBQWM7UUFDNUIsRUFBRSxDQUFDLFVBQVUsQ0FBQywyQkFBMkI7UUFDekMsRUFBRSxDQUFDLFVBQVUsQ0FBQyxlQUFlO1FBQzdCLEVBQUUsQ0FBQyxVQUFVLENBQUMsZ0JBQWdCO1FBQzlCLEVBQUUsQ0FBQyxVQUFVLENBQUMsZ0JBQWdCO0tBQy9CLENBQUM7SUFFRjs7O09BR0c7SUFDSCxNQUFNLG9CQUFvQixHQUFHO1FBQzNCLEVBQUMsTUFBTSxFQUFFLENBQUMsU0FBUyxDQUFDLEVBQUUsSUFBSSxFQUFFLE1BQU0sRUFBQztRQUNuQyxFQUFDLE1BQU0sRUFBRSxDQUFDLFNBQVMsQ0FBQyxFQUFFLElBQUksRUFBRSxPQUFPLEVBQUM7UUFDcEMs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SSxDQUFDLHVCQUF1QixDQUFDLGNBQWMsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDO1lBRWxGLElBQUksQ0FBQyxFQUFFLENBQUMsVUFBVSxDQUFDLFdBQVcsQ0FBQyxFQUFFO2dCQUMvQixPQUFPLElBQUksQ0FBQzthQUNiO1lBRUQsMkVBQTJFO1lBQzNFLCtCQUErQjtZQUMvQixPQUFPLElBQUksQ0FBQywyQkFBMkIsQ0FBQyxXQUFXLENBQUMsSUFBSSxDQUFDLENBQUM7UUFDNUQsQ0FBQztLQUNGO0lBN1dELDBEQTZXQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG5pbXBvcnQgKiBhcyB0cyBmcm9tICd0eXBlc2NyaXB0JztcbmltcG9ydCB7aXNGdW5jdGlvbkxpa2VEZWNsYXJhdGlvbiwgdW53cmFwRXhwcmVzc2lvbn0gZnJvbSAnLi4vLi4vLi4vLi4vdXRpbHMvdHlwZXNjcmlwdC9mdW5jdGlvbnMnO1xuaW1wb3J0IHtnZXRQcm9wZXJ0eU5hbWVUZXh0fSBmcm9tICcuLi8uLi8uLi8uLi91dGlscy90eXBlc2NyaXB0L3Byb3BlcnR5X25hbWUnO1xuXG5leHBvcnQgdHlwZSBGdW5jdGlvbkNvbnRleHQgPSBNYXA8dHMuTm9kZSwgdHMuTm9kZT47XG5cbmV4cG9ydCBlbnVtIFJlc29sdmVkVXNhZ2Uge1xuICBTWU5DSFJPTk9VUyxcbiAgQVNZTkNIUk9OT1VTLFxuICBBTUJJR1VPVVMsXG59XG5cbi8qKlxuICogTGlzdCBvZiBUeXBlU2NyaXB0IHN5bnRheCB0b2tlbnMgdGhhdCBjYW4gYmUgdXNlZCB3aXRoaW4gYSBiaW5hcnkgZXhwcmVzc2lvbiBhc1xuICogY29tcG91bmQgYXNzaWdubWVudC4gVGhlc2UgaW1wbHkgYSByZWFkIGFuZCB3cml0ZSBvZiB0aGUgbGVmdC1zaWRlIGV4cHJlc3Npb24uXG4gKi9cbmNvbnN0IEJJTkFSWV9DT01QT1VORF9UT0tFTlMgPSBbXG4gIHRzLlN5bnRheEtpbmQuQ2FyZXRFcXVhbHNUb2tlbixcbiAgdHMuU3ludGF4S2luZC5Bc3Rlcmlza0VxdWFsc1Rva2VuLFxuICB0cy5TeW50YXhLaW5kLkFtcGVyc2FuZEVxdWFsc1Rva2VuLFxuICB0cy5TeW50YXhLaW5kLkJhckVxdWFsc1Rva2VuLFxuICB0cy5TeW50YXhLaW5kLkFzdGVyaXNrQXN0ZXJpc2tFcXVhbHNUb2tlbixcbiAgdHMuU3ludGF4S2luZC5QbHVzRXF1YWxzVG9rZW4sXG4gIHRzLlN5bnRheEtpbmQuTWludXNFcXVhbHNUb2tlbixcbiAgdHMuU3ludGF4S2luZC5TbGFzaEVxdWFsc1Rva2VuLFxuXTtcblxuLyoqXG4gKiBMaXN0IG9mIGtub3duIGFzeW5jaHJvbm91cyBleHRlcm5hbCBjYWxsIGV4cHJlc3Npb25zIHdoaWNoIGFyZW4ndCBhbmFseXphYmxlXG4gKiBidXQgYXJlIGd1YXJhbnRlZWQgdG8gbm90IGV4ZWN1dGUgdGhlIHBhc3NlZCBhcmd1bWVudCBzeW5jaHJvbm91c2x5LlxuICovXG5jb25zdCBBU1lOQ19FWFRFUk5BTF9DQUxMUyA9IFtcbiAge3BhcmVudDogWydQcm9taXNlJ10sIG5hbWU6ICd0aGVuJ30sXG4gIHtwYXJlbnQ6IFsnUHJvbWlzZSddLCBuYW1lOiAnY2F0Y2gnfSxcbiAge3BhcmVudDogW251bGwsICdXaW5kb3cnXSwgbmFtZTogJ3JlcXVlc3RBbmltYXRpb25GcmFtZSd9LFxuICB7cGFyZW50OiBbbnVsbCwgJ1dpbmRvdyddLCBuYW1lOiAnc2V0VGltZW91dCd9LFxuICB7cGFyZW50OiBbbnVsbCwgJ1dpbmRvdyddLCBuYW1lOiAnc2V0SW50ZXJ2YWwnfSxcbiAge3BhcmVudDogWycqJ10sIG5hbWU6ICdhZGRFdmVudExpc3RlbmVyJ30sXG5dO1xuXG4vKipcbiAqIENsYXNzIHRoYXQgY2FuIGJlIHVzZWQgdG8gZGV0ZXJtaW5lIGlmIGEgZ2l2ZW4gVHlwZVNjcmlwdCBub2RlIGlzIHVzZWQgd2l0aGluXG4gKiBvdGhlciBnaXZlbiBUeXBlU2NyaXB0IG5vZGVzLiBUaGlzIGlzIGFjaGlldmVkIGJ5IHdhbGtpbmcgdGhyb3VnaCBhbGwgY2hpbGRyZW5cbiAqIG9mIHRoZSBnaXZlbiBub2RlIGFuZCBjaGVja2luZyBmb3IgdXNhZ2VzIG9mIHRoZSBnaXZlbiBkZWNsYXJhdGlvbi4gVGhlIHZpc2l0b3JcbiAqIGFsc28gaGFuZGxlcyBwb3RlbnRpYWwgY29udHJvbCBmbG93IGNoYW5nZXMgY2F1c2VkIGJ5IGNhbGwvbmV3IGV4cHJlc3Npb25zLlxuICovXG5leHBvcnQgY2xhc3MgRGVjbGFyYXRpb25Vc2FnZVZpc2l0b3Ige1xuICAvKiogU2V0IG9mIHZpc2l0ZWQgc3ltYm9scyB0aGF0IGNhdXNlZCBhIGp1bXAgaW4gY29udHJvbCBmbG93LiAqL1xuICBwcml2YXRlIHZpc2l0ZWRKdW1wRXhwck5vZGVzID0gbmV3IFNldDx0cy5Ob2RlPigpO1xuXG4gIC8qKlxuICAgKiBRdWV1ZSBvZiBub2RlcyB0aGF0IG5lZWQgdG8gYmUgY2hlY2tlZCBmb3IgZGVjbGFyYXRpb24gdXNhZ2UgYW5kXG4gICAqIGFyZSBndWFyYW50ZWVkIHRvIGJlIGV4ZWN1dGVkIHN5bmNocm9ub3VzbHkuXG4gICAqL1xuICBwcml2YXRlIG5vZGVRdWV1ZTogdHMuTm9kZVtdID0gW107XG5cbiAgLyoqXG4gICAqIE5vZGVzIHdoaWNoIG5lZWQgdG8gYmUgY2hlY2tlZCBmb3IgZGVjbGFyYXRpb24gdXNhZ2UgYnV0IGFyZW4ndFxuICAgKiBndWFyYW50ZWVkIHRvIGV4ZWN1dGUgc3luY2hyb25vdXNseS5cbiAgICovXG4gIHByaXZhdGUgYW1iaWd1b3VzTm9kZVF1ZXVlOiB0cy5Ob2RlW10gPSBbXTtcblxuICAvKipcbiAgICogRnVuY3Rpb24gY29udGV4dCB0aGF0IGhvbGRzIHRoZSBUeXBlU2NyaXB0IG5vZGUgdmFsdWVzIGZvciBhbGwgcGFyYW1ldGVyc1xuICAgKiBvZiB0aGUgY3VycmVudGx5IGFuYWx5emVkIGZ1bmN0aW9uIGJsb2NrLlxuICAgKi9cbiAgcHJpdmF0ZSBjb250ZXh0OiBGdW5jdGlvbkNvbnRleHQgPSBuZXcgTWFwKCk7XG5cbiAgY29uc3RydWN0b3IoXG4gICAgICBwcml2YXRlIGRlY2xhcmF0aW9uOiB0cy5Ob2RlLCBwcml2YXRlIHR5cGVDaGVja2VyOiB0cy5UeXBlQ2hlY2tlcixcbiAgICAgIHByaXZhdGUgYmFzZUNvbnRleHQ6IEZ1bmN0aW9uQ29udGV4dCA9IG5ldyBNYXAoKSkge31cblxuICBwcml2YXRlIGlzUmVmZXJyaW5nVG9TeW1ib2wobm9kZTogdHMuTm9kZSk6IGJvb2xlYW4ge1xuICAgIGNvbnN0IHN5bWJvbCA9IHRoaXMudHlwZUNoZWNrZXIuZ2V0U3ltYm9sQXRMb2NhdGlvbihub2RlKTtcbiAgICByZXR1cm4gISFzeW1ib2wgJiYgc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24gPT09IHRoaXMuZGVjbGFyYXRpb247XG4gIH1cblxuICBwcml2YXRlIGFkZEp1bXBFeHByZXNzaW9uVG9RdWV1ZShjYWxsRXhwcmVzc2lvbjogdHMuQ2FsbEV4cHJlc3Npb24pIHtcbiAgICBjb25zdCBub2RlID0gdW53cmFwRXhwcmVzc2lvbihjYWxsRXhwcmVzc2lvbi5leHByZXNzaW9uKTtcblxuICAgIC8vIEluIGNhc2UgdGhlIGdpdmVuIGV4cHJlc3Npb24gaXMgYWxyZWFkeSByZWZlcnJpbmcgdG8gYSBmdW5jdGlvbi1saWtlIGRlY2xhcmF0aW9uLFxuICAgIC8vIHdlIGRvbid0IG5lZWQgdG8gcmVzb2x2ZSB0aGUgc3ltYm9sIG9mIHRoZSBleHByZXNzaW9uIGFzIHRoZSBqdW1wIGV4cHJlc3Npb24gaXNcbiAgICAvLyBkZWZpbmVkIGlubGluZSBhbmQgd2UgY2FuIGp1c3QgYWRkIHRoZSBnaXZlbiBub2RlIHRvIHRoZSBxdWV1ZS5cbiAgICBpZiAoaXNGdW5jdGlvbkxpa2VEZWNsYXJhdGlvbihub2RlKSAmJiBub2RlLmJvZHkpIHtcbiAgICAgIHRoaXMubm9kZVF1ZXVlLnB1c2gobm9kZS5ib2R5KTtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBjYWxsRXhwclN5bWJvbCA9IHRoaXMuX2dldERlY2xhcmF0aW9uU3ltYm9sT2ZOb2RlKG5vZGUpO1xuXG4gICAgaWYgKCFjYWxsRXhwclN5bWJvbCB8fCAhY2FsbEV4cHJTeW1ib2wudmFsdWVEZWNsYXJhdGlvbikge1xuICAgICAgdGhpcy5wZWVrSW50b0p1bXBFeHByZXNzaW9uKGNhbGxFeHByZXNzaW9uKTtcbiAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICBjb25zdCBleHByZXNzaW9uRGVjbCA9IHRoaXMuX3Jlc29sdmVOb2RlRnJvbUNvbnRleHQoY2FsbEV4cHJTeW1ib2wudmFsdWVEZWNsYXJhdGlvbik7XG5cbiAgICAvLyBOb3RlIHRoYXQgd2Ugc2hvdWxkIG5vdCBhZGQgcHJldmlvdXNseSB2aXNpdGVkIHN5bWJvbHMgdG8gdGhlIHF1ZXVlIGFzXG4gICAgLy8gdGhpcyBjb3VsZCBjYXVzZSBjeWNsZXMuXG4gICAgaWYgKCFpc0Z1bmN0aW9uTGlrZURlY2xhcmF0aW9uKGV4cHJlc3Npb25EZWNsKSB8fFxuICAgICAgICB0aGlzLnZpc2l0ZWRKdW1wRXhwck5vZGVzLmhhcyhleHByZXNzaW9uRGVjbCkgfHwgIWV4cHJlc3Npb25EZWNsLmJvZHkpIHtcbiAgICAgIHRoaXMucGVla0ludG9KdW1wRXhwcmVzc2lvbihjYWxsRXhwcmVzc2lvbik7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgLy8gVXBkYXRlIHRoZSBjb250ZXh0IGZvciB0aGUgbmV3IGp1bXAgZXhwcmVzc2lvbiBhbmQgaXRzIHNwZWNpZmllZCBhcmd1bWVudHMuXG4gICAgdGhpcy5fdXBkYXRlQ29udGV4dChjYWxsRXhwcmVzc2lvbi5hcmd1bWVudHMsIGV4cHJlc3Npb25EZWNsLnBhcmFtZXRlcnMpO1xuXG4gICAgdGhpcy52aXNpdGVkSnVtcEV4cHJOb2Rlcy5hZGQoZXhwcmVzc2lvbkRlY2wpO1xuICAgIHRoaXMubm9kZVF1ZXVlLnB1c2goZXhwcmVzc2lvbkRlY2wuYm9keSk7XG4gIH1cblxuICBwcml2YXRlIGFkZE5ld0V4cHJlc3Npb25Ub1F1ZXVlKG5vZGU6IHRzLk5ld0V4cHJlc3Npb24pIHtcbiAgICBjb25zdCBuZXdFeHByU3ltYm9sID0gdGhpcy5fZ2V0RGVjbGFyYXRpb25TeW1ib2xPZk5vZGUodW53cmFwRXhwcmVzc2lvbihub2RlLmV4cHJlc3Npb24pKTtcblxuICAgIC8vIE9ubHkgaGFuZGxlIG5ldyBleHByZXNzaW9ucyB3aGljaCByZXNvbHZlIHRvIGNsYXNzZXMuIFRlY2huaWNhbGx5IFwibmV3XCIgY291bGRcbiAgICAvLyBhbHNvIGNhbGwgdm9pZCBmdW5jdGlvbnMgb3Igb2JqZWN0cyB3aXRoIGEgY29uc3RydWN0b3Igc2lnbmF0dXJlLiBBbHNvIG5vdGUgdGhhdFxuICAgIC8vIHdlIHNob3VsZCBub3QgdmlzaXQgYWxyZWFkeSB2aXNpdGVkIHN5bWJvbHMgYXMgdGhpcyBjb3VsZCBjYXVzZSBjeWNsZXMuXG4gICAgaWYgKCFuZXdFeHByU3ltYm9sIHx8ICFuZXdFeHByU3ltYm9sLnZhbHVlRGVjbGFyYXRpb24gfHxcbiAgICAgICAgIXRzLmlzQ2xhc3NEZWNsYXJhdGlvbihuZXdFeHByU3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pKSB7XG4gICAgICB0aGlzLnBlZWtJbnRvSnVtcEV4cHJlc3Npb24obm9kZSk7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgdGFyZ2V0Q29uc3RydWN0b3IgPVxuICAgICAgICBuZXdFeHByU3ltYm9sLnZhbHVlRGVjbGFyYXRpb24ubWVtYmVycy5maW5kKHRzLmlzQ29uc3RydWN0b3JEZWNsYXJhdGlvbik7XG5cbiAgICBpZiAodGFyZ2V0Q29uc3RydWN0b3IgJiYgdGFyZ2V0Q29uc3RydWN0b3IuYm9keSAmJlxuICAgICAgICAhdGhpcy52aXNpdGVkSnVtcEV4cHJOb2Rlcy5oYXModGFyZ2V0Q29uc3RydWN0b3IpKSB7XG4gICAgICAvLyBVcGRhdGUgdGhlIGNvbnRleHQgZm9yIHRoZSBuZXcgZXhwcmVzc2lvbiBhbmQgaXRzIHNwZWNpZmllZCBjb25zdHJ1Y3RvclxuICAgICAgLy8gcGFyYW1ldGVycyBpZiBhcmd1bWVudHMgYXJlIHBhc3NlZCB0byB0aGUgY2xhc3MgY29uc3RydWN0b3IuXG4gICAgICBpZiAobm9kZS5hcmd1bWVudHMpIHtcbiAgICAgICAgdGhpcy5fdXBkYXRlQ29udGV4dChub2RlLmFyZ3VtZW50cywgdGFyZ2V0Q29uc3RydWN0b3IucGFyYW1ldGVycyk7XG4gICAgICB9XG5cbiAgICAgIHRoaXMudmlzaXRlZEp1bXBFeHByTm9kZXMuYWRkKHRhcmdldENvbnN0cnVjdG9yKTtcbiAgICAgIHRoaXMubm9kZVF1ZXVlLnB1c2godGFyZ2V0Q29uc3RydWN0b3IuYm9keSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHRoaXMucGVla0ludG9KdW1wRXhwcmVzc2lvbihub2RlKTtcbiAgICB9XG4gIH1cblxuICBwcml2YXRlIHZpc2l0UHJvcGVydHlBY2Nlc3NvcnMoXG4gICAgICBub2RlOiB0cy5Qcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb24sIGNoZWNrU2V0dGVyOiBib29sZWFuLCBjaGVja0dldHRlcjogYm9vbGVhbikge1xuICAgIGNvbnN0IHByb3BlcnR5U3ltYm9sID0gdGhpcy5fZ2V0UHJvcGVydHlBY2Nlc3NTeW1ib2wobm9kZSk7XG5cbiAgICBpZiAoIXByb3BlcnR5U3ltYm9sIHx8ICFwcm9wZXJ0eVN5bWJvbC5kZWNsYXJhdGlvbnMubGVuZ3RoIHx8XG4gICAgICAgIChwcm9wZXJ0eVN5bWJvbC5nZXRGbGFncygpICYgdHMuU3ltYm9sRmxhZ3MuQWNjZXNzb3IpID09PSAwKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgLy8gU2luY2Ugd2UgY2hlY2tlZCB0aGUgc3ltYm9sIGZsYWdzIGFuZCB0aGUgc3ltYm9sIGlzIGRlc2NyaWJpbmcgYW4gYWNjZXNzb3IsIHRoZVxuICAgIC8vIGRlY2xhcmF0aW9ucyBhcmUgZ3VhcmFudGVlZCB0byBvbmx5IGNvbnRhaW4gdGhlIGdldHRlcnMgYW5kIHNldHRlcnMuXG4gICAgY29uc3QgYWNjZXNzb3JzID0gcHJvcGVydHlTeW1ib2wuZGVjbGFyYXRpb25zIGFzIHRzLkFjY2Vzc29yRGVjbGFyYXRpb25bXTtcblxuICAgIGFjY2Vzc29yc1xuICAgICAgICAuZmlsdGVyKFxuICAgICAgICAgICAgZCA9PiAoY2hlY2tTZXR0ZXIgJiYgdHMuaXNTZXRBY2Nlc3NvcihkKSB8fCBjaGVja0dldHRlciAmJiB0cy5pc0dldEFjY2Vzc29yKGQpKSAmJlxuICAgICAgICAgICAgICAgIGQuYm9keSAmJiAhdGhpcy52aXNpdGVkSnVtcEV4cHJOb2Rlcy5oYXMoZCkpXG4gICAgICAgIC5mb3JFYWNoKGQgPT4ge1xuICAgICAgICAgIHRoaXMudmlzaXRlZEp1bXBFeHByTm9kZXMuYWRkKGQpO1xuICAgICAgICAgIHRoaXMubm9kZVF1ZXVlLnB1c2goZC5ib2R5ISk7XG4gICAgICAgIH0pO1xuICB9XG5cbiAgcHJpdmF0ZSB2aXNpdEJpbmFyeUV4cHJlc3Npb24obm9kZTogdHMuQmluYXJ5RXhwcmVzc2lvbik6IGJvb2xlYW4ge1xuICAgIGNvbnN0IGxlZnRFeHByID0gdW53cmFwRXhwcmVzc2lvbihub2RlLmxlZnQpO1xuXG4gICAgaWYgKCF0cy5pc1Byb3BlcnR5QWNjZXNzRXhwcmVzc2lvbihsZWZ0RXhwcikpIHtcbiAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBpZiAoQklOQVJZX0NPTVBPVU5EX1RPS0VOUy5pbmRleE9mKG5vZGUub3BlcmF0b3JUb2tlbi5raW5kKSAhPT0gLTEpIHtcbiAgICAgIC8vIENvbXBvdW5kIGFzc2lnbm1lbnRzIGFsd2F5cyBjYXVzZSB0aGUgZ2V0dGVyIGFuZCBzZXR0ZXIgdG8gYmUgY2FsbGVkLlxuICAgICAgLy8gVGhlcmVmb3JlIHdlIG5lZWQgdG8gY2hlY2sgdGhlIHNldHRlciBhbmQgZ2V0dGVyIG9mIHRoZSBwcm9wZXJ0eSBhY2Nlc3MuXG4gICAgICB0aGlzLnZpc2l0UHJvcGVydHlBY2Nlc3NvcnMobGVmdEV4cHIsIC8qIHNldHRlciAqLyB0cnVlLCAvKiBnZXR0ZXIgKi8gdHJ1ZSk7XG4gICAgfSBlbHNlIGlmIChub2RlLm9wZXJhdG9yVG9rZW4ua2luZCA9PT0gdHMuU3ludGF4S2luZC5FcXVhbHNUb2tlbikge1xuICAgICAgLy8gVmFsdWUgYXNzaWdubWVudHMgdXNpbmcgdGhlIGVxdWFscyB0b2tlbiBvbmx5IGNhdXNlIHRoZSBcInNldHRlclwiIHRvIGJlIGNhbGxlZC5cbiAgICAgIC8vIFRoZXJlZm9yZSB3ZSBuZWVkIHRvIGFuYWx5emUgdGhlIHNldHRlciBkZWNsYXJhdGlvbiBvZiB0aGUgcHJvcGVydHkgYWNjZXNzLlxuICAgICAgdGhpcy52aXNpdFByb3BlcnR5QWNjZXNzb3JzKGxlZnRFeHByLCAvKiBzZXR0ZXIgKi8gdHJ1ZSwgLyogZ2V0dGVyICovIGZhbHNlKTtcbiAgICB9IGVsc2Uge1xuICAgICAgLy8gSWYgdGhlIGJpbmFyeSBleHByZXNzaW9uIGlzIG5vdCBhbiBhc3NpZ25tZW50LCBpdCdzIGEgc2ltcGxlIHByb3BlcnR5IHJlYWQgYW5kXG4gICAgICAvLyB3ZSBuZWVkIHRvIGNoZWNrIHRoZSBnZXR0ZXIgZGVjbGFyYXRpb24gaWYgcHJlc2VudC5cbiAgICAgIHRoaXMudmlzaXRQcm9wZXJ0eUFjY2Vzc29ycyhsZWZ0RXhwciwgLyogc2V0dGVyICovIGZhbHNlLCAvKiBnZXR0ZXIgKi8gdHJ1ZSk7XG4gICAgfVxuICAgIHJldHVybiB0cnVlO1xuICB9XG5cbiAgZ2V0UmVzb2x2ZWROb2RlVXNhZ2Uoc2VhcmNoTm9kZTogdHMuTm9kZSk6IFJlc29sdmVkVXNhZ2Uge1xuICAgIHRoaXMubm9kZVF1ZXVlID0gW3NlYXJjaE5vZGVdO1xuICAgIHRoaXMudmlzaXRlZEp1bXBFeHByTm9kZXMuY2xlYXIoKTtcbiAgICB0aGlzLmNvbnRleHQuY2xlYXIoKTtcblxuICAgIC8vIENvcHkgYmFzZSBjb250ZXh0IHZhbHVlcyBpbnRvIHRoZSBjdXJyZW50IGZ1bmN0aW9uIGJsb2NrIGNvbnRleHQuIFRoZVxuICAgIC8vIGJhc2UgY29udGV4dCBpcyB1c2VmdWwgaWYgbm9kZXMgbmVlZCB0byBiZSBtYXBwZWQgdG8gb3RoZXIgbm9kZXMuIGUuZy5cbiAgICAvLyBhYnN0cmFjdCBzdXBlciBjbGFzcyBtZXRob2RzIGFyZSBtYXBwZWQgdG8gdGhlaXIgaW1wbGVtZW50YXRpb24gbm9kZSBvZlxuICAgIC8vIHRoZSBkZXJpdmVkIGNsYXNzLlxuICAgIHRoaXMuYmFzZUNvbnRleHQuZm9yRWFjaCgodmFsdWUsIGtleSkgPT4gdGhpcy5jb250ZXh0LnNldChrZXksIHZhbHVlKSk7XG5cbiAgICByZXR1cm4gdGhpcy5pc1N5bmNocm9ub3VzbHlVc2VkSW5Ob2RlKHNlYXJjaE5vZGUpO1xuICB9XG5cbiAgcHJpdmF0ZSBpc1N5bmNocm9ub3VzbHlVc2VkSW5Ob2RlKHNlYXJjaE5vZGU6IHRzLk5vZGUpOiBSZXNvbHZlZFVzYWdlIHtcbiAgICB0aGlzLmFtYmlndW91c05vZGVRdWV1ZSA9IFtdO1xuXG4gICAgd2hpbGUgKHRoaXMubm9kZVF1ZXVlLmxlbmd0aCkge1xuICAgICAgY29uc3Qgbm9kZSA9IHRoaXMubm9kZVF1ZXVlLnNoaWZ0KCkhO1xuXG4gICAgICBpZiAodHMuaXNJZGVudGlmaWVyKG5vZGUpICYmIHRoaXMuaXNSZWZlcnJpbmdUb1N5bWJvbChub2RlKSkge1xuICAgICAgICByZXR1cm4gUmVzb2x2ZWRVc2FnZS5TWU5DSFJPTk9VUztcbiAgICAgIH1cblxuICAgICAgLy8gSGFuZGxlIGNhbGwgZXhwcmVzc2lvbnMgd2l0aGluIFR5cGVTY3JpcHQgbm9kZXMgdGhhdCBjYXVzZSBhIGp1bXAgaW4gY29udHJvbFxuICAgICAgLy8gZmxvdy4gV2UgcmVzb2x2ZSB0aGUgY2FsbCBleHByZXNzaW9uIHZhbHVlIGRlY2xhcmF0aW9uIGFuZCBhZGQgaXQgdG8gdGhlIG5vZGUgcXVldWUuXG4gICAgICBpZiAodHMuaXNDYWxsRXhwcmVzc2lvbihub2RlKSkge1xuICAgICAgICB0aGlzLmFkZEp1bXBFeHByZXNzaW9uVG9RdWV1ZShub2RlKTtcbiAgICAgIH1cblxuICAgICAgLy8gSGFuZGxlIG5ldyBleHByZXNzaW9ucyB0aGF0IGNhdXNlIGEganVtcCBpbiBjb250cm9sIGZsb3cuIFdlIHJlc29sdmUgdGhlXG4gICAgICAvLyBjb25zdHJ1Y3RvciBkZWNsYXJhdGlvbiBvZiB0aGUgdGFyZ2V0IGNsYXNzIGFuZCBhZGQgaXQgdG8gdGhlIG5vZGUgcXVldWUuXG4gICAgICBpZiAodHMuaXNOZXdFeHByZXNzaW9uKG5vZGUpKSB7XG4gICAgICAgIHRoaXMuYWRkTmV3RXhwcmVzc2lvblRvUXVldWUobm9kZSk7XG4gICAgICB9XG5cbiAgICAgIC8vIFdlIGFsc28gbmVlZCB0byBoYW5kbGUgYmluYXJ5IGV4cHJlc3Npb25zIHdoZXJlIGEgdmFsdWUgY2FuIGJlIGVpdGhlciBhc3NpZ25lZCB0b1xuICAgICAgLy8gdGhlIHByb3BlcnR5LCBvciBhIHZhbHVlIGlzIHJlYWQgZnJvbSBhIHByb3BlcnR5IGV4cHJlc3Npb24uIERlcGVuZGluZyBvbiB0aGVcbiAgICAgIC8vIGJpbmFyeSBleHByZXNzaW9uIG9wZXJhdG9yLCBzZXR0ZXJzIG9yIGdldHRlcnMgbmVlZCB0byBiZSBhbmFseXplZC5cbiAgICAgIGlmICh0cy5pc0JpbmFyeUV4cHJlc3Npb24obm9kZSkpIHtcbiAgICAgICAgLy8gSW4gY2FzZSB0aGUgYmluYXJ5IGV4cHJlc3Npb24gY29udGFpbmVkIGEgcHJvcGVydHkgZXhwcmVzc2lvbiBvbiB0aGUgbGVmdCBzaWRlLCB3ZVxuICAgICAgICAvLyBkb24ndCB3YW50IHRvIGNvbnRpbnVlIHZpc2l0aW5nIHRoaXMgcHJvcGVydHkgZXhwcmVzc2lvbiBvbiBpdHMgb3duLiBUaGlzIGlzIG5lY2Vzc2FyeVxuICAgICAgICAvLyBiZWNhdXNlIHZpc2l0aW5nIHRoZSBleHByZXNzaW9uIG9uIGl0cyBvd24gY2F1c2VzIGEgbG9zcyBvZiBjb250ZXh0LiBlLmcuIHByb3BlcnR5XG4gICAgICAgIC8vIGFjY2VzcyBleHByZXNzaW9ucyAqZG8gbm90KiBhbHdheXMgY2F1c2UgYSB2YWx1ZSByZWFkIChlLmcuIHByb3BlcnR5IGFzc2lnbm1lbnRzKVxuICAgICAgICBpZiAodGhpcy52aXNpdEJpbmFyeUV4cHJlc3Npb24obm9kZSkpIHtcbiAgICAgICAgICB0aGlzLm5vZGVRdWV1ZS5wdXNoKG5vZGUucmlnaHQpO1xuICAgICAgICAgIGNvbnRpbnVlO1xuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIC8vIEhhbmRsZSBwcm9wZXJ0eSBhY2Nlc3MgZXhwcmVzc2lvbnMuIFByb3BlcnR5IGV4cHJlc3Npb25zIHdoaWNoIGFyZSBwYXJ0IG9mIGJpbmFyeVxuICAgICAgLy8gZXhwcmVzc2lvbnMgd29uJ3QgYmUgYWRkZWQgdG8gdGhlIG5vZGUgcXVldWUsIHNvIHRoZXNlIGFjY2VzcyBleHByZXNzaW9ucyBhcmVcbiAgICAgIC8vIGd1YXJhbnRlZWQgdG8gYmUgXCJyZWFkXCIgYWNjZXNzZXMgYW5kIHdlIG5lZWQgdG8gY2hlY2sgdGhlIFwiZ2V0dGVyXCIgZGVjbGFyYXRpb24uXG4gICAgICBpZiAodHMuaXNQcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb24obm9kZSkpIHtcbiAgICAgICAgdGhpcy52aXNpdFByb3BlcnR5QWNjZXNzb3JzKG5vZGUsIC8qIHNldHRlciAqLyBmYWxzZSwgLyogZ2V0dGVyICovIHRydWUpO1xuICAgICAgfVxuXG4gICAgICAvLyBEbyBub3QgdmlzaXQgbm9kZXMgdGhhdCBkZWNsYXJlIGEgYmxvY2sgb2Ygc3RhdGVtZW50cyBidXQgYXJlIG5vdCBleGVjdXRlZFxuICAgICAgLy8gc3luY2hyb25vdXNseSAoZS5nLiBmdW5jdGlvbiBkZWNsYXJhdGlvbnMpLiBXZSBvbmx5IHdhbnQgdG8gY2hlY2sgVHlwZVNjcmlwdFxuICAgICAgLy8gbm9kZXMgd2hpY2ggYXJlIHN5bmNocm9ub3VzbHkgZXhlY3V0ZWQgaW4gdGhlIGNvbnRyb2wgZmxvdy5cbiAgICAgIGlmICghaXNGdW5jdGlvbkxpa2VEZWNsYXJhdGlvbihub2RlKSkge1xuICAgICAgICB0aGlzLm5vZGVRdWV1ZS5wdXNoKC4uLm5vZGUuZ2V0Q2hpbGRyZW4oKSk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgaWYgKHRoaXMuYW1iaWd1b3VzTm9kZVF1ZXVlLmxlbmd0aCkge1xuICAgICAgLy8gVXBkYXRlIHRoZSBub2RlIHF1ZXVlIHRvIGFsbCBzdG9yZWQgYW1iaWd1b3VzIG5vZGVzLiBUaGVzZSBub2RlcyBhcmUgbm90XG4gICAgICAvLyBndWFyYW50ZWVkIHRvIGJlIGV4ZWN1dGVkIGFuZCB0aGVyZWZvcmUgaW4gY2FzZSBvZiBhIHN5bmNocm9ub3VzIHVzYWdlXG4gICAgICAvLyB3aXRoaW4gb25lIG9mIHRob3NlIG5vZGVzLCB0aGUgcmVzb2x2ZWQgdXNhZ2UgaXMgYW1iaWd1b3VzLlxuICAgICAgdGhpcy5ub2RlUXVldWUgPSB0aGlzLmFtYmlndW91c05vZGVRdWV1ZTtcbiAgICAgIGNvbnN0IHVzYWdlID0gdGhpcy5pc1N5bmNocm9ub3VzbHlVc2VkSW5Ob2RlKHNlYXJjaE5vZGUpO1xuICAgICAgcmV0dXJuIHVzYWdlID09PSBSZXNvbHZlZFVzYWdlLlNZTkNIUk9OT1VTID8gUmVzb2x2ZWRVc2FnZS5BTUJJR1VPVVMgOiB1c2FnZTtcbiAgICB9XG4gICAgcmV0dXJuIFJlc29sdmVkVXNhZ2UuQVNZTkNIUk9OT1VTO1xuICB9XG5cbiAgLyoqXG4gICAqIFBlZWtzIGludG8gdGhlIGdpdmVuIGp1bXAgZXhwcmVzc2lvbiBieSBhZGRpbmcgYWxsIGZ1bmN0aW9uIGxpa2UgZGVjbGFyYXRpb25zXG4gICAqIHdoaWNoIGFyZSByZWZlcmVuY2VkIGluIHRoZSBqdW1wIGV4cHJlc3Npb24gYXJndW1lbnRzIHRvIHRoZSBhbWJpZ3VvdXMgbm9kZVxuICAgKiBxdWV1ZS4gVGhlc2UgYXJndW1lbnRzIGNvdWxkIHRlY2huaWNhbGx5IGFjY2VzcyB0aGUgZ2l2ZW4gZGVjbGFyYXRpb24gYnV0IGl0J3NcbiAgICogbm90IGd1YXJhbnRlZWQgdGhhdCB0aGUganVtcCBleHByZXNzaW9uIGlzIGV4ZWN1dGVkLiBJbiB0aGF0IGNhc2UgdGhlIHJlc29sdmVkXG4gICAqIHVzYWdlIGlzIGFtYmlndW91cy5cbiAgICovXG4gIHByaXZhdGUgcGVla0ludG9KdW1wRXhwcmVzc2lvbihqdW1wRXhwOiB0cy5DYWxsRXhwcmVzc2lvbnx0cy5OZXdFeHByZXNzaW9uKSB7XG4gICAgaWYgKCFqdW1wRXhwLmFyZ3VtZW50cykge1xuICAgICAgcmV0dXJuO1xuICAgIH1cblxuICAgIC8vIEZvciBzb21lIGNhbGwgZXhwcmVzc2lvbnMgd2UgZG9uJ3Qgd2FudCB0byBhZGQgdGhlIGFyZ3VtZW50cyB0byB0aGVcbiAgICAvLyBhbWJpZ3VvdXMgbm9kZSBxdWV1ZS4gZS5nLiBcInNldFRpbWVvdXRcIiBpcyBub3QgYW5hbHl6YWJsZSBidXQgaXNcbiAgICAvLyBndWFyYW50ZWVkIHRvIGV4ZWN1dGUgaXRzIGFyZ3VtZW50IGFzeW5jaHJvbm91c2x5LiBXZSBoYW5kbGUgYSBzdWJzZXRcbiAgICAvLyBvZiB0aGVzZSBjYWxsIGV4cHJlc3Npb25zIGJ5IGhhdmluZyBhIGhhcmRjb2RlZCBsaXN0IG9mIHNvbWUuXG4gICAgaWYgKHRzLmlzQ2FsbEV4cHJlc3Npb24oanVtcEV4cCkpIHtcbiAgICAgIGNvbnN0IHN5bWJvbCA9IHRoaXMuX2dldERlY2xhcmF0aW9uU3ltYm9sT2ZOb2RlKGp1bXBFeHAuZXhwcmVzc2lvbik7XG4gICAgICBpZiAoc3ltYm9sICYmIHN5bWJvbC52YWx1ZURlY2xhcmF0aW9uKSB7XG4gICAgICAgIGNvbnN0IHBhcmVudE5vZGUgPSBzeW1ib2wudmFsdWVEZWNsYXJhdGlvbi5wYXJlbnQ7XG4gICAgICAgIGlmIChwYXJlbnROb2RlICYmICh0cy5pc0ludGVyZmFjZURlY2xhcmF0aW9uKHBhcmVudE5vZGUpIHx8IHRzLmlzU291cmNlRmlsZShwYXJlbnROb2RlKSkgJiZcbiAgICAgICAgICAgICh0cy5pc01ldGhvZFNpZ25hdHVyZShzeW1ib2wudmFsdWVEZWNsYXJhdGlvbikgfHxcbiAgICAgICAgICAgICB0cy5pc0Z1bmN0aW9uRGVjbGFyYXRpb24oc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pKSAmJlxuICAgICAgICAgICAgc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24ubmFtZSkge1xuICAgICAgICAgIGNvbnN0IHBhcmVudE5hbWUgPSB0cy5pc0ludGVyZmFjZURlY2xhcmF0aW9uKHBhcmVudE5vZGUpID8gcGFyZW50Tm9kZS5uYW1lLnRleHQgOiBudWxsO1xuICAgICAgICAgIGNvbnN0IGNhbGxOYW1lID0gZ2V0UHJvcGVydHlOYW1lVGV4dChzeW1ib2wudmFsdWVEZWNsYXJhdGlvbi5uYW1lKTtcbiAgICAgICAgICBpZiAoQVNZTkNfRVhURVJOQUxfQ0FMTFMuc29tZShcbiAgICAgICAgICAgICAgICAgIGMgPT5cbiAgICAgICAgICAgICAgICAgICAgICAoYy5uYW1lID09PSBjYWxsTmFtZSAmJlxuICAgICAgICAgICAgICAgICAgICAgICAoYy5wYXJlbnQuaW5kZXhPZihwYXJlbnROYW1lKSAhPT0gLTEgfHwgYy5wYXJlbnQuaW5kZXhPZignKicpICE9PSAtMSkpKSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cblxuICAgIGp1bXBFeHAuYXJndW1lbnRzIS5mb3JFYWNoKChub2RlOiB0cy5Ob2RlKSA9PiB7XG4gICAgICBub2RlID0gdGhpcy5fcmVzb2x2ZURlY2xhcmF0aW9uT2ZOb2RlKG5vZGUpO1xuXG4gICAgICBpZiAodHMuaXNWYXJpYWJsZURlY2xhcmF0aW9uKG5vZGUpICYmIG5vZGUuaW5pdGlhbGl6ZXIpIHtcbiAgICAgICAgbm9kZSA9IG5vZGUuaW5pdGlhbGl6ZXI7XG4gICAgICB9XG5cbiAgICAgIGlmIChpc0Z1bmN0aW9uTGlrZURlY2xhcmF0aW9uKG5vZGUpICYmICEhbm9kZS5ib2R5KSB7XG4gICAgICAgIHRoaXMuYW1iaWd1b3VzTm9kZVF1ZXVlLnB1c2gobm9kZS5ib2R5KTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBSZXNvbHZlcyBhIGdpdmVuIG5vZGUgZnJvbSB0aGUgY29udGV4dC4gSW4gY2FzZSB0aGUgbm9kZSBpcyBub3QgbWFwcGVkIGluXG4gICAqIHRoZSBjb250ZXh0LCB0aGUgb3JpZ2luYWwgbm9kZSBpcyByZXR1cm5lZC5cbiAgICovXG4gIHByaXZhdGUgX3Jlc29sdmVOb2RlRnJvbUNvbnRleHQobm9kZTogdHMuTm9kZSk6IHRzLk5vZGUge1xuICAgIGlmICh0aGlzLmNvbnRleHQuaGFzKG5vZGUpKSB7XG4gICAgICByZXR1cm4gdGhpcy5jb250ZXh0LmdldChub2RlKSE7XG4gICAgfVxuICAgIHJldHVybiBub2RlO1xuICB9XG5cbiAgLyoqXG4gICAqIFVwZGF0ZXMgdGhlIGNvbnRleHQgdG8gcmVmbGVjdCB0aGUgbmV3bHkgc2V0IHBhcmFtZXRlciB2YWx1ZXMuIFRoaXMgYWxsb3dzIGZ1dHVyZVxuICAgKiByZWZlcmVuY2VzIHRvIGZ1bmN0aW9uIHBhcmFtZXRlcnMgdG8gYmUgcmVzb2x2ZWQgdG8gdGhlIGFjdHVhbCBub2RlIHRocm91Z2ggdGhlIGNvbnRleHQuXG4gICAqL1xuICBwcml2YXRlIF91cGRhdGVDb250ZXh0KFxuICAgICAgY2FsbEFyZ3M6IHRzLk5vZGVBcnJheTx0cy5FeHByZXNzaW9uPiwgcGFyYW1ldGVyczogdHMuTm9kZUFycmF5PHRzLlBhcmFtZXRlckRlY2xhcmF0aW9uPikge1xuICAgIHBhcmFtZXRlcnMuZm9yRWFjaCgocGFyYW1ldGVyLCBpbmRleCkgPT4ge1xuICAgICAgbGV0IGFyZ3VtZW50Tm9kZTogdHMuTm9kZSA9IGNhbGxBcmdzW2luZGV4XTtcblxuICAgICAgaWYgKCFhcmd1bWVudE5vZGUpIHtcbiAgICAgICAgaWYgKCFwYXJhbWV0ZXIuaW5pdGlhbGl6ZXIpIHtcbiAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBBcmd1bWVudCBjYW4gYmUgdW5kZWZpbmVkIGluIGNhc2UgdGhlIGZ1bmN0aW9uIHBhcmFtZXRlciBoYXMgYSBkZWZhdWx0XG4gICAgICAgIC8vIHZhbHVlLiBJbiB0aGF0IGNhc2Ugd2Ugd2FudCB0byBzdG9yZSB0aGUgcGFyYW1ldGVyIGRlZmF1bHQgdmFsdWUgaW4gdGhlIGNvbnRleHQuXG4gICAgICAgIGFyZ3VtZW50Tm9kZSA9IHBhcmFtZXRlci5pbml0aWFsaXplcjtcbiAgICAgIH1cblxuICAgICAgaWYgKHRzLmlzSWRlbnRpZmllcihhcmd1bWVudE5vZGUpKSB7XG4gICAgICAgIHRoaXMuY29udGV4dC5zZXQocGFyYW1ldGVyLCB0aGlzLl9yZXNvbHZlRGVjbGFyYXRpb25PZk5vZGUoYXJndW1lbnROb2RlKSk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICB0aGlzLmNvbnRleHQuc2V0KHBhcmFtZXRlciwgYXJndW1lbnROb2RlKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxuXG4gIC8qKlxuICAgKiBSZXNvbHZlcyB0aGUgZGVjbGFyYXRpb24gb2YgYSBnaXZlbiBUeXBlU2NyaXB0IG5vZGUuIEZvciBleGFtcGxlIGFuIGlkZW50aWZpZXIgY2FuXG4gICAqIHJlZmVyIHRvIGEgZnVuY3Rpb24gcGFyYW1ldGVyLiBUaGlzIHBhcmFtZXRlciBjYW4gdGhlbiBiZSByZXNvbHZlZCB0aHJvdWdoIHRoZVxuICAgKiBmdW5jdGlvbiBjb250ZXh0LlxuICAgKi9cbiAgcHJpdmF0ZSBfcmVzb2x2ZURlY2xhcmF0aW9uT2ZOb2RlKG5vZGU6IHRzLk5vZGUpOiB0cy5Ob2RlIHtcbiAgICBjb25zdCBzeW1ib2wgPSB0aGlzLl9nZXREZWNsYXJhdGlvblN5bWJvbE9mTm9kZShub2RlKTtcblxuICAgIGlmICghc3ltYm9sIHx8ICFzeW1ib2wudmFsdWVEZWNsYXJhdGlvbikge1xuICAgICAgcmV0dXJuIG5vZGU7XG4gICAgfVxuXG4gICAgcmV0dXJuIHRoaXMuX3Jlc29sdmVOb2RlRnJvbUNvbnRleHQoc3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pO1xuICB9XG5cbiAgLyoqXG4gICAqIEdldHMgdGhlIGRlY2xhcmF0aW9uIHN5bWJvbCBvZiBhIGdpdmVuIFR5cGVTY3JpcHQgbm9kZS4gUmVzb2x2ZXMgYWxpYXNlZFxuICAgKiBzeW1ib2xzIHRvIHRoZSBzeW1ib2wgY29udGFpbmluZyB0aGUgdmFsdWUgZGVjbGFyYXRpb24uXG4gICAqL1xuICBwcml2YXRlIF9nZXREZWNsYXJhdGlvblN5bWJvbE9mTm9kZShub2RlOiB0cy5Ob2RlKTogdHMuU3ltYm9sfG51bGwge1xuICAgIGxldCBzeW1ib2wgPSB0aGlzLnR5cGVDaGVja2VyLmdldFN5bWJvbEF0TG9jYXRpb24obm9kZSk7XG5cbiAgICBpZiAoIXN5bWJvbCkge1xuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuXG4gICAgLy8gUmVzb2x2ZSB0aGUgc3ltYm9sIHRvIGl0J3Mgb3JpZ2luYWwgZGVjbGFyYXRpb24gc3ltYm9sLlxuICAgIHdoaWxlIChzeW1ib2wuZmxhZ3MgJiB0cy5TeW1ib2xGbGFncy5BbGlhcykge1xuICAgICAgc3ltYm9sID0gdGhpcy50eXBlQ2hlY2tlci5nZXRBbGlhc2VkU3ltYm9sKHN5bWJvbCk7XG4gICAgfVxuXG4gICAgcmV0dXJuIHN5bWJvbDtcbiAgfVxuXG4gIC8qKiBHZXRzIHRoZSBzeW1ib2wgb2YgdGhlIGdpdmVuIHByb3BlcnR5IGFjY2VzcyBleHByZXNzaW9uLiAqL1xuICBwcml2YXRlIF9nZXRQcm9wZXJ0eUFjY2Vzc1N5bWJvbChub2RlOiB0cy5Qcm9wZXJ0eUFjY2Vzc0V4cHJlc3Npb24pOiB0cy5TeW1ib2x8bnVsbCB7XG4gICAgbGV0IHByb3BlcnR5U3ltYm9sID0gdGhpcy5fZ2V0RGVjbGFyYXRpb25TeW1ib2xPZk5vZGUobm9kZS5uYW1lKTtcblxuICAgIGlmICghcHJvcGVydHlTeW1ib2wgfHwgIXByb3BlcnR5U3ltYm9sLnZhbHVlRGVjbGFyYXRpb24pIHtcbiAgICAgIHJldHVybiBudWxsO1xuICAgIH1cblxuICAgIGlmICghdGhpcy5jb250ZXh0Lmhhcyhwcm9wZXJ0eVN5bWJvbC52YWx1ZURlY2xhcmF0aW9uKSkge1xuICAgICAgcmV0dXJuIHByb3BlcnR5U3ltYm9sO1xuICAgIH1cblxuICAgIC8vIEluIGNhc2UgdGhlIGNvbnRleHQgaGFzIHRoZSB2YWx1ZSBkZWNsYXJhdGlvbiBvZiB0aGUgZ2l2ZW4gcHJvcGVydHkgYWNjZXNzXG4gICAgLy8gbmFtZSBpZGVudGlmaWVyLCB3ZSBuZWVkIHRvIHJlcGxhY2UgdGhlIFwicHJvcGVydHlTeW1ib2xcIiB3aXRoIHRoZSBzeW1ib2xcbiAgICAvLyByZWZlcnJpbmcgdG8gdGhlIHJlc29sdmVkIHN5bWJvbCBiYXNlZCBvbiB0aGUgY29udGV4dC4gZS5nLiBhYnN0cmFjdCBwcm9wZXJ0aWVzXG4gICAgLy8gY2FuIHVsdGltYXRlbHkgcmVzb2x2ZSBpbnRvIGFuIGFjY2Vzc29yIGRlY2xhcmF0aW9uIGJhc2VkIG9uIHRoZSBpbXBsZW1lbnRhdGlvbi5cbiAgICBjb25zdCBjb250ZXh0Tm9kZSA9IHRoaXMuX3Jlc29sdmVOb2RlRnJvbUNvbnRleHQocHJvcGVydHlTeW1ib2wudmFsdWVEZWNsYXJhdGlvbik7XG5cbiAgICBpZiAoIXRzLmlzQWNjZXNzb3IoY29udGV4dE5vZGUpKSB7XG4gICAgICByZXR1cm4gbnVsbDtcbiAgICB9XG5cbiAgICAvLyBSZXNvbHZlIHRoZSBzeW1ib2wgcmVmZXJyaW5nIHRvIHRoZSBcImFjY2Vzc29yXCIgdXNpbmcgdGhlIG5hbWUgaWRlbnRpZmllclxuICAgIC8vIG9mIHRoZSBhY2Nlc3NvciBkZWNsYXJhdGlvbi5cbiAgICByZXR1cm4gdGhpcy5fZ2V0RGVjbGFyYXRpb25TeW1ib2xPZk5vZGUoY29udGV4dE5vZGUubmFtZSk7XG4gIH1cbn1cbiJdfQ==