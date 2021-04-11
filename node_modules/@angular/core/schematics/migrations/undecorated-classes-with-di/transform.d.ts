/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/transform" />
import { AotCompiler } from '@angular/compiler';
import { PartialEvaluator } from '@angular/compiler-cli/src/ngtsc/partial_evaluator';
import * as ts from 'typescript';
import { UpdateRecorder } from './update_recorder';
export interface TransformFailure {
    node: ts.Node;
    message: string;
}
export declare class UndecoratedClassesTransform {
    private typeChecker;
    private compiler;
    private evaluator;
    private getUpdateRecorder;
    private printer;
    private importManager;
    private decoratorRewriter;
    private compilerHost;
    private symbolResolver;
    private metadataResolver;
    /** Set of class declarations which have been decorated with "@Directive". */
    private decoratedDirectives;
    /** Set of class declarations which have been decorated with "@Injectable" */
    private decoratedProviders;
    /**
     * Set of class declarations which have been analyzed and need to specify
     * an explicit constructor.
     */
    private missingExplicitConstructorClasses;
    constructor(typeChecker: ts.TypeChecker, compiler: AotCompiler, evaluator: PartialEvaluator, getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    /**
     * Migrates decorated directives which can potentially inherit a constructor
     * from an undecorated base class. All base classes until the first one
     * with an explicit constructor will be decorated with the abstract "@Directive()"
     * decorator. See case 1 in the migration plan: https://hackmd.io/@alx/S1XKqMZeS
     */
    migrateDecoratedDirectives(directives: ts.ClassDeclaration[]): TransformFailure[];
    /**
     * Migrates decorated providers which can potentially inherit a constructor
     * from an undecorated base class. All base classes until the first one
     * with an explicit constructor will be decorated with the "@Injectable()".
     */
    migrateDecoratedProviders(providers: ts.ClassDeclaration[]): TransformFailure[];
    private _migrateProviderBaseClass;
    private _migrateDirectiveBaseClass;
    private _migrateDecoratedClassWithInheritedCtor;
    /**
     * Adds the abstract "@Directive()" decorator to the given class in case there
     * is no existing directive decorator.
     */
    private _addAbstractDirectiveDecorator;
    /**
     * Adds the abstract "@Injectable()" decorator to the given class in case there
     * is no existing directive decorator.
     */
    private _addInjectableDecorator;
    /** Adds a comment for adding an explicit constructor to the given class declaration. */
    private _addMissingExplicitConstructorTodo;
    /**
     * Migrates undecorated directives which were referenced in NgModule declarations.
     * These directives inherit the metadata from a parent base class, but with Ivy
     * these classes need to explicitly have a decorator for locality. The migration
     * determines the inherited decorator and copies it to the undecorated declaration.
     *
     * Note that the migration serializes the metadata for external declarations
     * where the decorator is not part of the source file AST.
     *
     * See case 2 in the migration plan: https://hackmd.io/@alx/S1XKqMZeS
     */
    migrateUndecoratedDeclarations(directives: ts.ClassDeclaration[]): TransformFailure[];
    private _migrateDerivedDeclaration;
    /** Records all changes that were made in the import manager. */
    recordChanges(): void;
    /**
     * Constructs a TypeScript decorator node from the specified declaration metadata. Returns
     * null if the metadata could not be simplified/resolved.
     */
    private _constructDecoratorFromMetadata;
    /**
     * Resolves the declaration metadata of a given static symbol. The metadata
     * is determined by resolving metadata for the static symbol.
     */
    private _resolveDeclarationMetadata;
    private _getStaticSymbolOfIdentifier;
    /**
     * Disables that static symbols are resolved through summaries. Summaries
     * cannot be used for decorator analysis as decorators are omitted in summaries.
     */
    private _disableSummaryResolution;
}
