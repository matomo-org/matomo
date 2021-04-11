/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/missing-injectable/providers_evaluator" />
import { ResolvedValue } from '@angular/compiler-cli/src/ngtsc/partial_evaluator';
import { StaticInterpreter } from '@angular/compiler-cli/src/ngtsc/partial_evaluator/src/interpreter';
import * as ts from 'typescript';
export interface ProviderLiteral {
    node: ts.ObjectLiteralExpression;
    resolvedValue: ResolvedValue;
}
/**
 * Providers evaluator that extends the ngtsc static interpreter. This is necessary because
 * the static interpreter by default only exposes the resolved value, but we are also interested
 * in the TypeScript nodes that declare providers. It would be possible to manually traverse the
 * AST to collect these nodes, but that would mean that we need to re-implement the static
 * interpreter in order to handle all possible scenarios. (e.g. spread operator, function calls,
 * callee scope). This can be avoided by simply extending the static interpreter and intercepting
 * the "visitObjectLiteralExpression" method.
 */
export declare class ProvidersEvaluator extends StaticInterpreter {
    private _providerLiterals;
    visitObjectLiteralExpression(node: ts.ObjectLiteralExpression, context: any): ResolvedValue;
    /**
     * Evaluates the given expression and returns its statically resolved value
     * and a list of object literals which define Angular providers.
     */
    evaluate(expr: ts.Expression): {
        resolvedValue: ResolvedValue;
        literals: ProviderLiteral[];
    };
}
