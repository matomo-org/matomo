/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/module-with-providers/transform" />
import { UpdateRecorder } from '@angular-devkit/schematics';
import { ResolvedValueMap } from '@angular/compiler-cli/src/ngtsc/partial_evaluator';
import * as ts from 'typescript';
import { ResolvedNgModule } from './collector';
export interface AnalysisFailure {
    node: ts.Node;
    message: string;
}
export declare class ModuleWithProvidersTransform {
    private typeChecker;
    private getUpdateRecorder;
    private printer;
    private partialEvaluator;
    constructor(typeChecker: ts.TypeChecker, getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    /** Migrates a given NgModule by walking through the referenced providers and static methods. */
    migrateModule(module: ResolvedNgModule): AnalysisFailure[];
    /** Migrates a ModuleWithProviders type definition that has no explicit generic type */
    migrateType(type: ts.TypeReferenceNode): AnalysisFailure[];
    /** Add a given generic to a type reference node */
    private _addGenericToTypeReference;
    /**
     * Migrates a given static method if its ModuleWithProviders does not provide
     * a generic type.
     */
    private _updateStaticMethodType;
    /** Whether the resolved value map represents a ModuleWithProviders object */
    isModuleWithProvidersType(value: ResolvedValueMap): boolean;
    /**
     * Determine the generic type of a suspected ModuleWithProviders return type and add it
     * explicitly
     */
    private _migrateStaticNgModuleMethod;
    /** Evaluate and return the ngModule type from an expression */
    private _getNgModuleTypeOfExpression;
    /**
     * Visits a given object literal expression to determine the ngModule type. If the expression
     * cannot be resolved, add a TODO to alert the user.
     */
    private _getTypeOfResolvedValue;
    private _updateNode;
}
