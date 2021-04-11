/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/undecorated-classes-with-di/ng_declaration_collector" />
import { PartialEvaluator } from '@angular/compiler-cli/src/ngtsc/partial_evaluator';
import * as ts from 'typescript';
import { NgDecorator } from '../../utils/ng_decorators';
/**
 * Visitor that walks through specified TypeScript nodes and collects all defined
 * directives and provider classes. Directives are separated by decorated and
 * undecorated directives.
 */
export declare class NgDeclarationCollector {
    typeChecker: ts.TypeChecker;
    private evaluator;
    /** List of resolved directives which are decorated. */
    decoratedDirectives: ts.ClassDeclaration[];
    /** List of resolved providers which are decorated. */
    decoratedProviders: ts.ClassDeclaration[];
    /** Set of resolved Angular declarations which are not decorated. */
    undecoratedDeclarations: Set<ts.ClassDeclaration>;
    constructor(typeChecker: ts.TypeChecker, evaluator: PartialEvaluator);
    visitNode(node: ts.Node): void;
    private _visitClassDeclaration;
    private _visitNgModuleDecorator;
}
/** Checks whether the given node has the "@Directive" or "@Component" decorator set. */
export declare function hasDirectiveDecorator(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker, ngDecorators?: NgDecorator[]): boolean;
/** Checks whether the given node has the "@Injectable" decorator set. */
export declare function hasInjectableDecorator(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker, ngDecorators?: NgDecorator[]): boolean;
/** Whether the given node has an explicit decorator that describes an Angular declaration. */
export declare function hasNgDeclarationDecorator(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker): boolean;
/** Gets all Angular decorators of a given class declaration. */
export declare function getNgClassDecorators(node: ts.ClassDeclaration, typeChecker: ts.TypeChecker): NgDecorator[];
