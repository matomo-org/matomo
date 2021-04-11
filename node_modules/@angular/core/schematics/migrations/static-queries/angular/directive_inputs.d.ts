/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/angular/directive_inputs" />
import * as ts from 'typescript';
/** Analyzes the given class and resolves the name of all inputs which are declared. */
export declare function getInputNamesOfClass(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker): string[];
