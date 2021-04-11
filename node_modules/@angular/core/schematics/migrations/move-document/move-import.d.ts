/// <amd-module name="@angular/core/schematics/migrations/move-document/move-import" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import * as ts from 'typescript';
export declare function removeFromImport(importNode: ts.NamedImports, sourceFile: ts.SourceFile, importName: string): string;
export declare function addToImport(importNode: ts.NamedImports, sourceFile: ts.SourceFile, name: ts.Identifier, propertyName?: ts.Identifier): string;
export declare function createImport(importSource: string, sourceFile: ts.SourceFile, name: ts.Identifier, propertyName?: ts.Identifier): string;
