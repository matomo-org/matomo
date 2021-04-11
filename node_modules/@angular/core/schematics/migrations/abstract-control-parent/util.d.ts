/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/abstract-control-parent/util" />
import * as ts from 'typescript';
/**
 * Finds the `PropertyAccessExpression`-s that are accessing the `parent` property in
 * such a way that may result in a compilation error after the v11 type changes.
 */
export declare function findParentAccesses(typeChecker: ts.TypeChecker, sourceFile: ts.SourceFile): ts.PropertyAccessExpression[];
