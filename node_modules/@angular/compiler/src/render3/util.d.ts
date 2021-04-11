/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as o from '../output/output_ast';
import { OutputContext } from '../util';
/**
 * Convert an object map with `Expression` values into a `LiteralMapExpr`.
 */
export declare function mapToMapExpression(map: {
    [key: string]: o.Expression | undefined;
}): o.LiteralMapExpr;
/**
 * Convert metadata into an `Expression` in the given `OutputContext`.
 *
 * This operation will handle arrays, references to symbols, or literal `null` or `undefined`.
 */
export declare function convertMetaToOutput(meta: any, ctx: OutputContext): o.Expression;
export declare function typeWithParameters(type: o.Expression, numParams: number): o.ExpressionType;
export interface R3Reference {
    value: o.Expression;
    type: o.Expression;
}
export declare function prepareSyntheticPropertyName(name: string): string;
export declare function prepareSyntheticListenerName(name: string, phase: string): string;
export declare function isSyntheticPropertyOrListener(name: string): boolean;
export declare function getSyntheticPropertyName(name: string): string;
export declare function getSafePropertyAccessString(accessor: string, name: string): string;
export declare function prepareSyntheticListenerFunctionName(name: string, phase: string): string;
export declare function jitOnlyGuardedExpression(expr: o.Expression): o.Expression;
export declare function devOnlyGuardedExpression(expr: o.Expression): o.Expression;
export declare function guardedExpression(guard: string, expr: o.Expression): o.Expression;
export declare function wrapReference(value: any): R3Reference;
