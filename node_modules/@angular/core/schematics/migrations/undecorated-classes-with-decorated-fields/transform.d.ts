/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-decorated-fields/transform" />
import * as ts from 'typescript';
import { UpdateRecorder } from './update_recorder';
interface AnalysisFailure {
    node: ts.Node;
    message: string;
}
export declare class UndecoratedClassesWithDecoratedFieldsTransform {
    private typeChecker;
    private getUpdateRecorder;
    private printer;
    private importManager;
    private reflectionHost;
    private partialEvaluator;
    constructor(typeChecker: ts.TypeChecker, getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    /**
     * Migrates the specified source files. The transform adds the abstract `@Directive`
     * decorator to undecorated classes that use Angular features. Class members which
     * are decorated with any Angular decorator, or class members for lifecycle hooks are
     * indicating that a given class uses Angular features. https://hackmd.io/vuQfavzfRG6KUCtU7oK_EA
     */
    migrate(sourceFiles: ts.SourceFile[]): AnalysisFailure[];
    /** Records all changes that were made in the import manager. */
    recordChanges(): void;
    /**
     * Finds undecorated abstract directives in the specified source files. Also returns
     * a set of undecorated classes which could not be detected as guaranteed abstract
     * directives. Those are ambiguous and could be either Directive, Pipe or service.
     */
    private _findUndecoratedAbstractDirectives;
    /**
     * Analyzes the given class declaration by determining whether the class
     * is a directive, is an abstract directive, or uses Angular features.
     */
    private _analyzeClassDeclaration;
    /**
     * Checks whether the given decorator resolves to an abstract directive. An directive is
     * considered "abstract" if there is no selector specified.
     */
    private _isAbstractDirective;
    /**
     * Determines the kind of a given class in terms of Angular. The method checks
     * whether the given class has members that indicate the use of Angular features.
     * e.g. lifecycle hooks or decorated members like `@Input` or `@Output` are
     * considered Angular features..
     */
    private _determineClassKind;
    /**
     * Checks whether a given class has been reported as ambiguous in previous
     * migration run. e.g. when build targets are migrated first, and then test
     * targets that have an overlap with build source files, the same class
     * could be detected as ambiguous.
     */
    private _hasBeenReportedAsAmbiguous;
}
export {};
