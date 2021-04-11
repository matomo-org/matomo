/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/move-document/document_import_visitor" />
import * as ts from 'typescript';
export declare const COMMON_IMPORT = "@angular/common";
export declare const PLATFORM_BROWSER_IMPORT = "@angular/platform-browser";
export declare const DOCUMENT_TOKEN_NAME = "DOCUMENT";
/** This contains the metadata necessary to move items from one import to another */
export interface ResolvedDocumentImport {
    platformBrowserImport: ts.NamedImports | null;
    commonImport: ts.NamedImports | null;
    documentElement: ts.ImportSpecifier | null;
}
/** Visitor that can be used to find a set of imports in a TypeScript file. */
export declare class DocumentImportVisitor {
    typeChecker: ts.TypeChecker;
    importsMap: Map<ts.SourceFile, ResolvedDocumentImport>;
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private visitNamedImport;
    private getDocumentElement;
}
