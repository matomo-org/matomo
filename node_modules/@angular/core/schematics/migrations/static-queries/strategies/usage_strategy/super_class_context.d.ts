/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/super_class_context" />
import * as ts from 'typescript';
import { ClassMetadataMap } from '../../angular/ng_query_visitor';
import { FunctionContext } from './declaration_usage_visitor';
/**
 * Updates the specified function context to map abstract super-class class members
 * to their implementation TypeScript nodes. This allows us to run the declaration visitor
 * for the super class with the context of the "baseClass" (e.g. with implemented abstract
 * class members)
 */
export declare function updateSuperClassAbstractMembersContext(baseClass: ts.ClassDeclaration, context: FunctionContext, classMetadataMap: ClassMetadataMap): void;
