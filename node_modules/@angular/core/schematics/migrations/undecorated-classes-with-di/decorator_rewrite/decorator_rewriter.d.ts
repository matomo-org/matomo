/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/decorator_rewrite/decorator_rewriter" />
/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { AotCompiler } from '@angular/compiler';
import { PartialEvaluator } from '@angular/compiler-cli/src/ngtsc/partial_evaluator';
import * as ts from 'typescript';
import { ImportManager } from '../../../utils/import_manager';
import { NgDecorator } from '../../../utils/ng_decorators';
/**
 * Class that can be used to copy decorators to a new location. The rewriter ensures that
 * identifiers and imports are rewritten to work in the new file location. Fields in a
 * decorator that cannot be cleanly copied will be copied with a comment explaining that
 * imports and identifiers need to be adjusted manually.
 */
export declare class DecoratorRewriter {
    private importManager;
    private typeChecker;
    private evaluator;
    private compiler;
    previousSourceFile: ts.SourceFile | null;
    newSourceFile: ts.SourceFile | null;
    newProperties: ts.ObjectLiteralElementLike[];
    nonCopyableProperties: ts.ObjectLiteralElementLike[];
    private importRewriterFactory;
    constructor(importManager: ImportManager, typeChecker: ts.TypeChecker, evaluator: PartialEvaluator, compiler: AotCompiler);
    rewrite(ngDecorator: NgDecorator, newSourceFile: ts.SourceFile): ts.Decorator;
    /** Creates a new decorator with the given expression. */
    private _createDecorator;
    /**
     * Sanitizes a metadata property by ensuring that all contained identifiers
     * are imported in the target source file.
     */
    private _sanitizeMetadataProperty;
}
