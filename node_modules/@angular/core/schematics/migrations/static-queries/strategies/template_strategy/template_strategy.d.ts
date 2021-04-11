/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/template_strategy/template_strategy" />
import * as ts from 'typescript';
import { ClassMetadataMap } from '../../angular/ng_query_visitor';
import { NgQueryDefinition } from '../../angular/query-definition';
import { TimingResult, TimingStrategy } from '../timing-strategy';
export declare class QueryTemplateStrategy implements TimingStrategy {
    private projectPath;
    private classMetadata;
    private host;
    private compiler;
    private metadataResolver;
    private analyzedQueries;
    constructor(projectPath: string, classMetadata: ClassMetadataMap, host: ts.CompilerHost);
    /**
     * Sets up the template strategy by creating the AngularCompilerProgram. Returns false if
     * the AOT compiler program could not be created due to failure diagnostics.
     */
    setup(): void;
    /** Analyzes a given directive by determining the timing of all matched view queries. */
    private _analyzeDirective;
    /** Detects the timing of the query definition. */
    detectTiming(query: NgQueryDefinition): TimingResult;
    /**
     * Gets the timing that has been resolved for a given query when it's used within the
     * specified class declaration. e.g. queries from an inherited class can be used.
     */
    private _getQueryTimingFromClass;
    private _parseTemplate;
    private _createDiagnosticsError;
    private _getViewQueryUniqueKey;
}
