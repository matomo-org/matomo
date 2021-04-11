/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/typescript/imports" />
import * as ts from 'typescript';
export declare type Import = {
    name: string;
    importModule: string;
    node: ts.ImportDeclaration;
};
/** Gets import information about the specified identifier by using the Type checker. */
export declare function getImportOfIdentifier(typeChecker: ts.TypeChecker, node: ts.Identifier): Import | null;
/**
 * Gets a top-level import specifier with a specific name that is imported from a particular module.
 * E.g. given a file that looks like:
 *
 * ```
 * import { Component, Directive } from '@angular/core';
 * import { Foo } from './foo';
 * ```
 *
 * Calling `getImportSpecifier(sourceFile, '@angular/core', 'Directive')` will yield the node
 * referring to `Directive` in the top import.
 *
 * @param sourceFile File in which to look for imports.
 * @param moduleName Name of the import's module.
 * @param specifierName Original name of the specifier to look for. Aliases will be resolved to
 *    their original name.
 */
export declare function getImportSpecifier(sourceFile: ts.SourceFile, moduleName: string, specifierName: string): ts.ImportSpecifier | null;
/**
 * Replaces an import inside a named imports node with a different one.
 * @param node Node that contains the imports.
 * @param existingImport Import that should be replaced.
 * @param newImportName Import that should be inserted.
 */
export declare function replaceImport(node: ts.NamedImports, existingImport: string, newImportName: string): ts.NamedImports;
