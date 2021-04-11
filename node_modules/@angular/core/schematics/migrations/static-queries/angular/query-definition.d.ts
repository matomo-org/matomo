/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/angular/query-definition" />
import * as ts from 'typescript';
import { NgDecorator } from '../../../utils/ng_decorators';
/** Timing of a given query. Either static or dynamic. */
export declare enum QueryTiming {
    STATIC = 0,
    DYNAMIC = 1
}
/** Type of a given query. */
export declare enum QueryType {
    ViewChild = 0,
    ContentChild = 1
}
export interface NgQueryDefinition {
    /** Name of the query. Set to "null" in case the query name is not statically analyzable. */
    name: string | null;
    /** Type of the query definition. */
    type: QueryType;
    /** Node that declares this query. */
    node: ts.Node;
    /**
     * Property declaration that refers to the query value. For accessors there
     * is no property that is guaranteed to access the query value.
     */
    property: ts.PropertyDeclaration | null;
    /** Decorator that declares this as a query. */
    decorator: NgDecorator;
    /** Class declaration that holds this query. */
    container: ts.ClassDeclaration;
}
