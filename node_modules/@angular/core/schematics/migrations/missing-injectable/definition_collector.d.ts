/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/missing-injectable/definition_collector" />
import * as ts from 'typescript';
import { NgDecorator } from '../../utils/ng_decorators';
export interface ResolvedNgModule {
    name: string;
    node: ts.ClassDeclaration;
    decorator: NgDecorator;
    providersExpr: ts.Expression | null;
}
export interface ResolvedDirective {
    name: string;
    node: ts.ClassDeclaration;
    decorator: NgDecorator;
    providersExpr: ts.Expression | null;
    viewProvidersExpr: ts.Expression | null;
}
/**
 * Visitor that walks through specified TypeScript nodes and collects all
 * found NgModule, Directive or Component definitions.
 */
export declare class NgDefinitionCollector {
    typeChecker: ts.TypeChecker;
    resolvedModules: ResolvedNgModule[];
    resolvedDirectives: ResolvedDirective[];
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private visitClassDeclaration;
    private _visitDirectiveClass;
    private _visitNgModuleClass;
}
