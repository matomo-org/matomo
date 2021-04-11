/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/dynamic-queries/util" />
import * as ts from 'typescript';
/**
 * Identifies the nodes that should be migrated by the dynamic
 * queries schematic. Splits the nodes into the following categories:
 * - `removeProperty` - queries from which we should only remove the `static` property of the
 *  `options` parameter (e.g. `@ViewChild('child', {static: false, read: ElementRef})`).
 * - `removeParameter` - queries from which we should drop the entire `options` parameter.
 *  (e.g. `@ViewChild('child', {static: false})`).
 */
export declare function identifyDynamicQueryNodes(typeChecker: ts.TypeChecker, sourceFile: ts.SourceFile): {
    removeProperty: ts.ObjectLiteralExpression[];
    removeParameter: ts.CallExpression[];
};
/** Removes the `options` parameter from the call expression of a query decorator. */
export declare function removeOptionsParameter(node: ts.CallExpression): ts.CallExpression;
/** Removes the `static` property from an object literal expression. */
export declare function removeStaticFlag(node: ts.ObjectLiteralExpression): ts.ObjectLiteralExpression;
