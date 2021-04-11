/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/// <amd-module name="@angular/core/schematics/utils/line_mappings" />
/** Gets the line and character for the given position from the line starts map. */
export declare function getLineAndCharacterFromPosition(lineStartsMap: number[], position: number): {
    character: number;
    line: number;
};
/**
 * Computes the line start map of the given text. This can be used in order to
 * retrieve the line and character of a given text position index.
 */
export declare function computeLineStartsMap(text: string): number[];
