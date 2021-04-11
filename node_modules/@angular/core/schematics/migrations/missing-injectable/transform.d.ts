/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/missing-injectable/transform" />
import * as ts from 'typescript';
import { ResolvedDirective, ResolvedNgModule } from './definition_collector';
import { UpdateRecorder } from './update_recorder';
export interface AnalysisFailure {
    node: ts.Node;
    message: string;
}
export declare class MissingInjectableTransform {
    private typeChecker;
    private getUpdateRecorder;
    private printer;
    private importManager;
    private providersEvaluator;
    /** Set of provider class declarations which were already checked or migrated. */
    private visitedProviderClasses;
    /** Set of provider object literals which were already checked or migrated. */
    private visitedProviderLiterals;
    constructor(typeChecker: ts.TypeChecker, getUpdateRecorder: (sf: ts.SourceFile) => UpdateRecorder);
    recordChanges(): void;
    /**
     * Migrates all specified NgModule's by walking through referenced providers
     * and decorating them with "@Injectable" if needed.
     */
    migrateModules(modules: ResolvedNgModule[]): AnalysisFailure[];
    /**
     * Migrates all specified directives by walking through referenced providers
     * and decorating them with "@Injectable" if needed.
     */
    migrateDirectives(directives: ResolvedDirective[]): AnalysisFailure[];
    /** Migrates a given NgModule by walking through the referenced providers. */
    migrateModule(module: ResolvedNgModule): AnalysisFailure[];
    /**
     * Migrates a given directive by walking through defined providers. This method
     * also handles components with "viewProviders" defined.
     */
    migrateDirective(directive: ResolvedDirective): AnalysisFailure[];
    /**
     * Migrates a given provider class if it is not decorated with
     * any Angular decorator.
     */
    migrateProviderClass(node: ts.ClassDeclaration, context: ResolvedNgModule | ResolvedDirective): void;
    /**
     * Migrates object literal providers which do not use "useValue", "useClass",
     * "useExisting" or "useFactory". These providers behave differently in Ivy. e.g.
     *
     * ```ts
     *   {provide: X} -> {provide: X, useValue: undefined} // this is how it behaves in VE
     *   {provide: X} -> {provide: X, useClass: X} // this is how it behaves in Ivy
     * ```
     *
     * To ensure forward compatibility, we migrate these empty object literal providers
     * to explicitly use `useValue: undefined`.
     */
    private _migrateLiteralProviders;
    /**
     * Visits the given resolved value of a provider. Providers can be nested in
     * arrays and we need to recursively walk through the providers to be able to
     * migrate all referenced provider classes. e.g. "providers: [[A, [B]]]".
     */
    private _visitProviderResolvedValue;
}
