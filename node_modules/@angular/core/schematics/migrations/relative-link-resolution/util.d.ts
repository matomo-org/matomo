/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/relative-link-resolution/util" />
import * as ts from 'typescript';
/** Determine whether a node is a ModuleWithProviders type reference node without a generic type */
export declare function isRouterModuleForRoot(typeChecker: ts.TypeChecker, node: ts.Node): node is ts.CallExpression;
export declare function isExtraOptions(typeChecker: ts.TypeChecker, node: ts.Node): node is ts.TypeReferenceNode;
