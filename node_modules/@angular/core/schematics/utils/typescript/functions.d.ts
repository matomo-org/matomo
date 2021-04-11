/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/typescript/functions" />
import * as ts from 'typescript';
/** Checks whether a given node is a function like declaration. */
export declare function isFunctionLikeDeclaration(node: ts.Node): node is ts.FunctionLikeDeclaration;
/**
 * Unwraps a given expression TypeScript node. Expressions can be wrapped within multiple
 * parentheses or as expression. e.g. "(((({exp}))))()". The function should return the
 * TypeScript node referring to the inner expression. e.g "exp".
 */
export declare function unwrapExpression(node: ts.Expression | ts.ParenthesizedExpression): ts.Expression;
