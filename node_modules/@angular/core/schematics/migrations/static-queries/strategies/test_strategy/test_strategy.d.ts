/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/migrations/static-queries/strategies/test_strategy/test_strategy" />
import { NgQueryDefinition } from '../../angular/query-definition';
import { TimingResult, TimingStrategy } from '../timing-strategy';
/**
 * Query timing strategy that is used for queries used within test files. The query
 * timing is not analyzed for test files as the template strategy cannot work within
 * spec files (due to missing component modules) and the usage strategy is not capable
 * of detecting the timing of queries based on how they are used in tests.
 */
export declare class QueryTestStrategy implements TimingStrategy {
    setup(): void;
    /**
     * Detects the timing for a given query. For queries within tests, we always
     * add a TODO and print a message saying that the timing can't be detected for tests.
     */
    detectTiming(query: NgQueryDefinition): TimingResult;
}
