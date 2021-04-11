/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/wait-for-async/util" />
import * as ts from 'typescript';
/** Finds calls to the `async` function. */
export declare function findAsyncReferences(sourceFile: ts.SourceFile, typeChecker: ts.TypeChecker, asyncImportSpecifier: ts.ImportSpecifier): Set<ts.Identifier>;
