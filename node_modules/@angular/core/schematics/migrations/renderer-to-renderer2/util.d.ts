/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/renderer-to-renderer2/util" />
import * as ts from 'typescript';
/**
 * Finds typed nodes (e.g. function parameters or class properties) that are referencing the old
 * `Renderer`, as well as calls to the `Renderer` methods.
 */
export declare function findRendererReferences(sourceFile: ts.SourceFile, typeChecker: ts.TypeChecker, rendererImportSpecifier: ts.ImportSpecifier): {
    typedNodes: Set<ts.ParameterDeclaration | ts.AsExpression | ts.PropertyDeclaration>;
    methodCalls: Set<ts.CallExpression>;
    forwardRefs: Set<ts.Identifier>;
};
