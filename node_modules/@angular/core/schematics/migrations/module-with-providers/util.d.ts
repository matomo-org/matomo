/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/module-with-providers/util" />
import * as ts from 'typescript';
/** Add a generic type to a type reference. */
export declare function createModuleWithProvidersType(type: string, node?: ts.TypeReferenceNode): ts.TypeReferenceNode;
/** Determine whether a node is a ModuleWithProviders type reference node without a generic type */
export declare function isModuleWithProvidersNotGeneric(typeChecker: ts.TypeChecker, node: ts.Node): node is ts.TypeReferenceNode;
