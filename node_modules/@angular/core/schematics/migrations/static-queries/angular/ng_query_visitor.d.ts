/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/angular/ng_query_visitor" />
import * as ts from 'typescript';
import { ResolvedTemplate } from '../../../utils/ng_component_template';
import { NgQueryDefinition } from './query-definition';
/** Resolved metadata of a given class. */
export interface ClassMetadata {
    /** List of class declarations that derive from the given class. */
    derivedClasses: ts.ClassDeclaration[];
    /** Super class of the given class. */
    superClass: ts.ClassDeclaration | null;
    /** List of property names that declare an Angular input within the given class. */
    ngInputNames: string[];
    /** Component template that belongs to that class if present. */
    template?: ResolvedTemplate;
}
/** Type that describes a map which can be used to get a class declaration's metadata. */
export declare type ClassMetadataMap = Map<ts.ClassDeclaration, ClassMetadata>;
/**
 * Visitor that can be used to determine Angular queries within given TypeScript nodes.
 * Besides resolving queries, the visitor also records class relations and searches for
 * Angular input setters which can be used to analyze the timing usage of a given query.
 */
export declare class NgQueryResolveVisitor {
    typeChecker: ts.TypeChecker;
    /** Resolved Angular query definitions. */
    resolvedQueries: Map<ts.SourceFile, NgQueryDefinition[]>;
    /** Maps a class declaration to its class metadata. */
    classMetadata: ClassMetadataMap;
    constructor(typeChecker: ts.TypeChecker);
    visitNode(node: ts.Node): void;
    private visitPropertyDeclaration;
    private visitAccessorDeclaration;
    private visitClassDeclaration;
    private _recordQueryDeclaration;
    private _recordClassInputSetters;
    private _recordClassInheritances;
    private _getClassMetadata;
}
