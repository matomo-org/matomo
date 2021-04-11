/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { SourceMap } from '@angular/compiler';
export interface SourceLocation {
    line: number;
    column: number;
    source: string;
}
export declare function originalPositionFor(sourceMap: SourceMap, genPosition: {
    line: number | null;
    column: number | null;
}): SourceLocation;
export declare function extractSourceMap(source: string): SourceMap | null;
