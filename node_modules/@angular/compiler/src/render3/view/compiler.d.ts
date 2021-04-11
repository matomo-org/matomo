/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { ConstantPool } from '../../constant_pool';
import * as o from '../../output/output_ast';
import { ParseError, ParseSourceSpan } from '../../parse_util';
import { BindingParser } from '../../template_parser/binding_parser';
import { R3ComponentDef, R3ComponentMetadata, R3DirectiveDef, R3DirectiveMetadata } from './api';
/**
 * Compile a directive for the render3 runtime as defined by the `R3DirectiveMetadata`.
 */
export declare function compileDirectiveFromMetadata(meta: R3DirectiveMetadata, constantPool: ConstantPool, bindingParser: BindingParser): R3DirectiveDef;
/**
 * Compile a component for the render3 runtime as defined by the `R3ComponentMetadata`.
 */
export declare function compileComponentFromMetadata(meta: R3ComponentMetadata, constantPool: ConstantPool, bindingParser: BindingParser): R3ComponentDef;
/**
 * Creates the type specification from the component meta. This type is inserted into .d.ts files
 * to be consumed by upstream compilations.
 */
export declare function createComponentType(meta: R3ComponentMetadata): o.Type;
/**
 * A set of flags to be used with Queries.
 *
 * NOTE: Ensure changes here are in sync with `packages/core/src/render3/interfaces/query.ts`
 */
export declare const enum QueryFlags {
    /**
     * No flags
     */
    none = 0,
    /**
     * Whether or not the query should descend into children.
     */
    descendants = 1,
    /**
     * The query can be computed statically and hence can be assigned eagerly.
     *
     * NOTE: Backwards compatibility with ViewEngine.
     */
    isStatic = 2,
    /**
     * If the `QueryList` should fire change event only if actual change to query was computed (vs old
     * behavior where the change was fired whenever the query was recomputed, even if the recomputed
     * query resulted in the same list.)
     */
    emitDistinctChangesOnly = 4
}
export declare function createDirectiveTypeParams(meta: R3DirectiveMetadata): o.Type[];
/**
 * Creates the type specification from the directive meta. This type is inserted into .d.ts files
 * to be consumed by upstream compilations.
 */
export declare function createDirectiveType(meta: R3DirectiveMetadata): o.Type;
export interface ParsedHostBindings {
    attributes: {
        [key: string]: o.Expression;
    };
    listeners: {
        [key: string]: string;
    };
    properties: {
        [key: string]: string;
    };
    specialAttributes: {
        styleAttr?: string;
        classAttr?: string;
    };
}
export declare function parseHostBindings(host: {
    [key: string]: string | o.Expression;
}): ParsedHostBindings;
/**
 * Verifies host bindings and returns the list of errors (if any). Empty array indicates that a
 * given set of host bindings has no errors.
 *
 * @param bindings set of host bindings to verify.
 * @param sourceSpan source span where host bindings were defined.
 * @returns array of errors associated with a given set of host bindings.
 */
export declare function verifyHostBindings(bindings: ParsedHostBindings, sourceSpan: ParseSourceSpan): ParseError[];
