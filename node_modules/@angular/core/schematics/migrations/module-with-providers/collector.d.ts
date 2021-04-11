/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/module-with-providers/collector" />
import * as ts from 'typescript';
import { NgDecorator } from '../../utils/ng_decorators';
export interface ResolvedNgModule {
    name: string;
    node: ts.ClassDeclaration;
    decorator: NgDecorator;
    /**
     * List of found static method declarations on the module which do not
     * declare an explicit return type.
     */
    staticMethodsWithoutType: ts.MethodDeclaration[];
}
/**
 * Visitor that walks through specified TypeScript nodes and collects all
 * found NgModule static methods without types and all ModuleWithProviders
 * usages without generic types attached.
 */
export declare class Collector {
    typeChecker: ts.TypeChecker;
    resolvedModules: ResolvedNgModule[];
    resolvedNonGenerics: ts.TypeReferenceNode[];
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private visitClassDeclaration;
    private _visitNgModuleClass;
}
