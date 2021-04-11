/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/typescript/find_base_classes" />
import * as ts from 'typescript';
/** Gets all base class declarations of the specified class declaration. */
export declare function findBaseClassDeclarations(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker): {
    identifier: ts.Identifier;
    node: ts.ClassDeclaration;
}[];
