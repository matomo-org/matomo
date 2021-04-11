/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/usage_strategy/usage_strategy" />
import * as ts from 'typescript';
import { ClassMetadataMap } from '../../angular/ng_query_visitor';
import { NgQueryDefinition } from '../../angular/query-definition';
import { TimingResult, TimingStrategy } from '../timing-strategy';
/**
 * Query timing strategy that determines the timing of a given query by inspecting how
 * the query is accessed within the project's TypeScript source files. Read more about
 * this strategy here: https://hackmd.io/s/Hymvc2OKE
 */
export declare class QueryUsageStrategy implements TimingStrategy {
    private classMetadata;
    private typeChecker;
    constructor(classMetadata: ClassMetadataMap, typeChecker: ts.TypeChecker);
    setup(): void;
    /**
     * Analyzes the usage of the given query and determines the query timing based
     * on the current usage of the query.
     */
    detectTiming(query: NgQueryDefinition): TimingResult;
    /**
     * Checks whether a given query is used statically within the given class, its super
     * class or derived classes.
     */
    private analyzeQueryUsage;
}
