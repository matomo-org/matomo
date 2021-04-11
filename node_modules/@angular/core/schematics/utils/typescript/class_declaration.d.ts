/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/typescript/class_declaration" />
import * as ts from 'typescript';
/** Determines the base type identifiers of a specified class declaration. */
export declare function getBaseTypeIdentifiers(node: ts.ClassDeclaration): ts.Identifier[] | null;
/** Gets the first found parent class declaration of a given node. */
export declare function findParentClassDeclaration(node: ts.Node): ts.ClassDeclaration | null;
/** Checks whether the given class declaration has an explicit constructor or not. */
export declare function hasExplicitConstructor(node: ts.ClassDeclaration): boolean;
