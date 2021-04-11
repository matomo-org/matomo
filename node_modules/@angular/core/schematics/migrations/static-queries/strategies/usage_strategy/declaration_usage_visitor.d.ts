/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/declaration_usage_visitor" />
import * as ts from 'typescript';
export declare type FunctionContext = Map<ts.Node, ts.Node>;
export declare enum ResolvedUsage {
    SYNCHRONOUS = 0,
    ASYNCHRONOUS = 1,
    AMBIGUOUS = 2
}
/**
 * Class that can be used to determine if a given TypeScript node is used within
 * other given TypeScript nodes. This is achieved by walking through all children
 * of the given node and checking for usages of the given declaration. The visitor
 * also handles potential control flow changes caused by call/new expressions.
 */
export declare class DeclarationUsageVisitor {
    private declaration;
    private typeChecker;
    private baseContext;
    /** Set of visited symbols that caused a jump in control flow. */
    private visitedJumpExprNodes;
    /**
     * Queue of nodes that need to be checked for declaration usage and
     * are guaranteed to be executed synchronously.
     */
    private nodeQueue;
    /**
     * Nodes which need to be checked for declaration usage but aren't
     * guaranteed to execute synchronously.
     */
    private ambiguousNodeQueue;
    /**
     * Function context that holds the TypeScript node values for all parameters
     * of the currently analyzed function block.
     */
    private context;
    constructor(declaration: ts.Node, typeChecker: ts.TypeChecker, baseContext?: FunctionContext);
    private isReferringToSymbol;
    private addJumpExpressionToQueue;
    private addNewExpressionToQueue;
    private visitPropertyAccessors;
    private visitBinaryExpression;
    getResolvedNodeUsage(searchNode: ts.Node): ResolvedUsage;
    private isSynchronouslyUsedInNode;
    /**
     * Peeks into the given jump expression by adding all function like declarations
     * which are referenced in the jump expression arguments to the ambiguous node
     * queue. These arguments could technically access the given declaration but it's
     * not guaranteed that the jump expression is executed. In that case the resolved
     * usage is ambiguous.
     */
    private peekIntoJumpExpression;
    /**
     * Resolves a given node from the context. In case the node is not mapped in
     * the context, the original node is returned.
     */
    private _resolveNodeFromContext;
    /**
     * Updates the context to reflect the newly set parameter values. This allows future
     * references to function parameters to be resolved to the actual node through the context.
     */
    private _updateContext;
    /**
     * Resolves the declaration of a given TypeScript node. For example an identifier can
     * refer to a function parameter. This parameter can then be resolved through the
     * function context.
     */
    private _resolveDeclarationOfNode;
    /**
     * Gets the declaration symbol of a given TypeScript node. Resolves aliased
     * symbols to the symbol containing the value declaration.
     */
    private _getDeclarationSymbolOfNode;
    /** Gets the symbol of the given property access expression. */
    private _getPropertyAccessSymbol;
}
