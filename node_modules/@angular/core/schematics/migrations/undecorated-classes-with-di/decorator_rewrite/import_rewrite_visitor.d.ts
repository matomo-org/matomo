/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/import_rewrite_visitor" />
import { AotCompilerHost } from '@angular/compiler';
import * as ts from 'typescript';
import { ImportManager } from '../../../utils/import_manager';
/**
 * Factory that creates a TypeScript transformer which ensures that
 * referenced identifiers are available at the target file location.
 *
 * Imports cannot be just added as sometimes identifiers collide in the
 * target source file and the identifier needs to be aliased.
 */
export declare class ImportRewriteTransformerFactory {
    private importManager;
    private typeChecker;
    private compilerHost;
    private sourceFileExports;
    constructor(importManager: ImportManager, typeChecker: ts.TypeChecker, compilerHost: AotCompilerHost);
    create<T extends ts.Node>(ctx: ts.TransformationContext, newSourceFile: ts.SourceFile): ts.Transformer<T>;
    private _recordIdentifierReference;
    /**
     * Gets the resolved exports of a given source file. Exports are cached
     * for subsequent calls.
     */
    private _getSourceFileExports;
    /** Rewrites a module import to be relative to the target file location. */
    private _rewriteModuleImport;
}
/** Error that will be thrown if a given identifier cannot be resolved. */
export declare class UnresolvedIdentifierError extends Error {
}
