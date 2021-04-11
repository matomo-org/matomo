/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/transform" />
import * as ts from 'typescript';
import { NgQueryDefinition, QueryTiming } from './angular/query-definition';
export declare type TransformedQueryResult = null | {
    /** Transformed call expression. */
    node: ts.CallExpression;
    /** Failure message which is set when the query could not be transformed successfully. */
    failureMessage: string | null;
};
/**
 * Transforms the given query decorator by explicitly specifying the timing based on the
 * determined timing. The updated decorator call expression node will be returned.
 */
export declare function getTransformedQueryCallExpr(query: NgQueryDefinition, timing: QueryTiming | null, createTodo: boolean): TransformedQueryResult;
