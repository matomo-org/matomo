/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/source_file_exports" />
import * as ts from 'typescript';
export interface ResolvedExport {
    symbol: ts.Symbol;
    exportName: string;
    identifier: ts.Identifier;
}
/** Computes the resolved exports of a given source file. */
export declare function getExportSymbolsOfFile(sf: ts.SourceFile, typeChecker: ts.TypeChecker): ResolvedExport[];
