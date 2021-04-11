/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/angular/super_class" />
import * as ts from 'typescript';
import { ClassMetadataMap } from './ng_query_visitor';
/**
 * Gets all chained super-class TypeScript declarations for the given class
 * by using the specified class metadata map.
 */
export declare function getSuperClassDeclarations(classDecl: ts.ClassDeclaration, classMetadataMap: ClassMetadataMap): ts.ClassDeclaration[];
